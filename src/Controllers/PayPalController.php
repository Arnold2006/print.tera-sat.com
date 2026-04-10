<?php
declare(strict_types=1);

namespace src\Controllers;

use src\Models\Order;

/**
 * Handles server-side PayPal Smart Buttons integration.
 *
 * Endpoints:
 *   POST ?page=paypal&action=create-order  – creates a PayPal order and returns { id }
 *   POST ?page=paypal&action=capture-order – captures payment and places the DB order
 *
 * The client secret is never sent to the browser; amounts are always read
 * from the server-side session, not from client-provided values.
 */
class PayPalController
{
    private const API_BASE_SANDBOX = 'https://api-m.sandbox.paypal.com';
    private const API_BASE_LIVE    = 'https://api-m.paypal.com';
    private const CURRENCY         = 'EUR';

    // -------------------------------------------------------------------------
    // Public endpoints
    // -------------------------------------------------------------------------

    /**
     * POST ?page=paypal&action=create-order
     *
     * Reads grand_total from session, creates a PayPal order, and returns JSON.
     */
    public function createOrder(): void
    {
        $this->jsonHeaders();

        if (empty($_SESSION['order_data'])) {
            $this->jsonError(400, 'No order data in session.');
        }

        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            $this->jsonError(403, 'Invalid CSRF token.');
        }

        // Use server-side amount – never trust a client-provided total
        $grandTotal = (float) ($_SESSION['order_data']['grand_total'] ?? 0);
        if ($grandTotal <= 0) {
            $this->jsonError(400, 'Invalid order total.');
        }

        try {
            $accessToken = $this->getAccessToken();
        } catch (\RuntimeException $e) {
            $this->jsonError(502, 'Payment service unavailable. Please try again later.');
        }

        $payload = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'amount'      => [
                    'currency_code' => self::CURRENCY,
                    'value'         => number_format($grandTotal, 2, '.', ''),
                ],
                'description' => 'Photo prints order',
            ]],
        ];

        try {
            $response = $this->callApi('POST', '/v2/checkout/orders', $accessToken, $payload);
        } catch (\RuntimeException $e) {
            $this->jsonError(502, 'Payment service unavailable. Please try again later.');
        }

        if ($response['httpCode'] !== 201 || empty($response['body']['id'])) {
            $this->jsonError(502, 'Failed to create PayPal order.');
        }

        echo json_encode(['id' => $response['body']['id']]);
        exit;
    }

    /**
     * POST ?page=paypal&action=capture-order
     *
     * Captures the PayPal payment, verifies the amount, and places the order in the DB.
     */
    public function captureOrder(): void
    {
        $this->jsonHeaders();

        if (empty($_SESSION['upload_files']) || empty($_SESSION['order_data'])) {
            $this->jsonError(400, 'Session expired or invalid. Please start your order again.');
        }

        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            $this->jsonError(403, 'Invalid CSRF token.');
        }

        $paypalOrderId = trim($_POST['paypal_order_id'] ?? '');
        if (!preg_match('/^[A-Za-z0-9]{6,50}$/', $paypalOrderId)) {
            $this->jsonError(400, 'Invalid PayPal order ID.');
        }

        try {
            $accessToken = $this->getAccessToken();
        } catch (\RuntimeException $e) {
            $this->jsonError(502, 'Payment service unavailable. Please try again later.');
        }

        // Capture the PayPal order
        try {
            $response = $this->callApi(
                'POST',
                '/v2/checkout/orders/' . $paypalOrderId . '/capture',
                $accessToken,
                []
            );
        } catch (\RuntimeException $e) {
            $this->jsonError(502, 'Payment service unavailable. Please try again later.');
        }

        if ($response['httpCode'] !== 201) {
            $this->jsonError(502, 'Failed to capture PayPal payment.');
        }

        $capture = $response['body'];
        if (($capture['status'] ?? '') !== 'COMPLETED') {
            $this->jsonError(402, 'Payment was not completed.');
        }

        // Verify that the captured amount matches the server-side total (compare as formatted strings)
        $capturedAmount = number_format(
            (float) ($capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0),
            2, '.', ''
        );
        $expectedTotal = number_format((float) ($_SESSION['order_data']['grand_total'] ?? 0), 2, '.', '');
        if ($capturedAmount !== $expectedTotal) {
            $this->jsonError(400, 'Payment amount mismatch. Please contact support.');
        }

        $paypalCaptureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? '';

        // Place the order (same logic as OrderController::place)
        $orderData = $_SESSION['order_data'];
        $items     = $orderData['items'] ?? [];

        if (empty($items)) {
            $this->jsonError(400, 'No items in order.');
        }

        foreach ($items as $item) {
            if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i', $item['filename'])) {
                $this->jsonError(400, 'Invalid image filename detected.');
            }
            if (!file_exists(UPLOADS_PATH . $item['filename'])) {
                $this->jsonError(400, 'One or more uploaded images were not found. Please upload again.');
            }
        }

        $model            = new Order();
        $groupOrderNumber = $model->generateGroupOrderNumber();

        foreach ($items as $item) {
            $src  = UPLOADS_PATH  . $item['filename'];
            $dest = PERMANENT_PATH . $item['filename'];

            if (!rename($src, $dest)) {
                $this->jsonError(500, 'Failed to process an image. Please contact support.');
            }

            $model->createOrder([
                'group_order_number' => $groupOrderNumber,
                'paypal_transaction_id' => $paypalCaptureId,
                'filename'           => $item['filename'],
                'original_filename'  => $item['original_filename'],
                'size'               => $item['size'],
                'quantity'           => $item['quantity'],
                'price'              => $item['total_price'],
                'customer_name'      => $orderData['customer_name'],
                'customer_email'     => $orderData['customer_email'],
                'customer_address'   => $orderData['customer_address'],
            ]);
        }

        unset($_SESSION['upload_files'], $_SESSION['order_data'], $_SESSION['order_error']);
        $_SESSION['success_order_number'] = $groupOrderNumber;
        $_SESSION['success_paypal_transaction_id'] = $paypalCaptureId;

        echo json_encode([
            'success'     => true,
            'redirectUrl' => APP_URL . '/?page=order&action=success',
        ]);
        exit;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Obtains a PayPal OAuth 2.0 access token using client credentials.
     *
     * @throws \RuntimeException if the token cannot be obtained.
     */
    private function getAccessToken(): string
    {
        $url = $this->apiBase() . '/v1/oauth2/token';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $body     = curl_exec($ch);
        $curlErr  = $body === false ? curl_error($ch) : '';
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException('cURL error obtaining PayPal access token: ' . $curlErr);
        }

        if ($httpCode !== 200 || $body === '') {
            throw new \RuntimeException('Unable to obtain PayPal access token (HTTP ' . $httpCode . ').');
        }

        $data = json_decode((string) $body, true);
        if (empty($data['access_token'])) {
            throw new \RuntimeException('PayPal access token missing in response.');
        }

        return $data['access_token'];
    }

    /**
     * Makes an authenticated request to the PayPal REST API.
     *
     * @param  string  $method      HTTP method ('GET', 'POST', etc.)
     * @param  string  $path        API path (e.g. '/v2/checkout/orders')
     * @param  string  $accessToken Bearer token
     * @param  array   $payload     Request body (encoded as JSON)
     * @return array{httpCode: int, body: array<mixed>}
     */
    private function callApi(string $method, string $path, string $accessToken, array $payload): array
    {
        $ch = curl_init($this->apiBase() . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => json_encode(empty($payload) ? new \stdClass() : $payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $body     = curl_exec($ch);
        $curlErr  = $body === false ? curl_error($ch) : '';
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException('cURL request to PayPal API failed: ' . $curlErr);
        }

        return [
            'httpCode' => $httpCode,
            'body'     => json_decode((string) $body, true) ?? [],
        ];
    }

    private function apiBase(): string
    {
        return PAYPAL_ENV === 'live' ? self::API_BASE_LIVE : self::API_BASE_SANDBOX;
    }

    private function jsonHeaders(): void
    {
        header('Content-Type: application/json');
    }

    /**
     * Emit a JSON error response and exit.
     *
     * @param  int    $code    HTTP status code
     * @param  string $message Human-readable message
     * @return never
     */
    private function jsonError(int $code, string $message): never
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}
