<?php
declare(strict_types=1);

namespace src\Controllers;

class UploadController
{
    public function index(): void
    {
        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/upload/index.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function process(): void
    {
        // Buffer any output (PHP warnings, notices, errors) so they cannot
        // leak into the JSON response body and cause a parse failure on the client.
        ob_start();

        header('Content-Type: application/json');

        try {
            // CSRF check
            $token = $_POST['csrf_token'] ?? '';
            if (!csrfVerify($token)) {
                ob_end_clean();
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
                return;
            }

            if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errMsg = $this->uploadErrorMessage($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $errMsg]);
                return;
            }

            $file = $_FILES['image'];

            // Size check
            if ($file['size'] > UPLOAD_MAX_SIZE) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'File exceeds maximum size of 10 MB.']);
                return;
            }

            // MIME type check via finfo
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, WebP.']);
                return;
            }

            // Extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid file extension.']);
                return;
            }

            // Generate safe filename
            $newName = bin2hex(random_bytes(16)) . '.' . $ext;
            $dest    = UPLOADS_PATH . $newName;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                ob_end_clean();
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
                return;
            }

            // Store in session
            $_SESSION['upload_filename']          = $newName;
            $_SESSION['upload_original_filename'] = htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8');

            ob_end_clean();
            echo json_encode([
                'success'      => true,
                'filename'     => $newName,
                'preview_url'  => APP_URL . '/?page=image&file=' . urlencode($newName),
                'redirect_url' => APP_URL . '/?page=order',
            ]);
        } catch (\Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error. Please try again.']);
        }
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL   => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE   => 'No file was uploaded.',
            default              => 'Upload error (code ' . $code . ').',
        };
    }
}
