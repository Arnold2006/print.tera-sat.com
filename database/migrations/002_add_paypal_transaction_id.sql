-- Migration: add paypal_transaction_id column to store the PayPal capture/transaction ID.
ALTER TABLE `orders`
    ADD COLUMN `paypal_transaction_id` VARCHAR(50) NULL DEFAULT NULL AFTER `group_order_number`;
