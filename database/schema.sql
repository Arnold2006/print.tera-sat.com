CREATE DATABASE IF NOT EXISTS `print_service` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `print_service`;

CREATE TABLE IF NOT EXISTS `orders` (
    `id`                  INT AUTO_INCREMENT PRIMARY KEY,
    `order_number`        VARCHAR(20)  UNIQUE NOT NULL,
    `group_order_number`  VARCHAR(20)  NULL DEFAULT NULL,
    `paypal_transaction_id` VARCHAR(50) NULL DEFAULT NULL,
    `filename`            VARCHAR(255) NOT NULL,
    `original_filename`   VARCHAR(255) NOT NULL,
    `size`                VARCHAR(20)  NOT NULL,
    `quantity`            INT          NOT NULL DEFAULT 1,
    `price`               DECIMAL(10,2) NOT NULL,
    `customer_name`       VARCHAR(100) NOT NULL,
    `customer_email`      VARCHAR(100) NOT NULL,
    `customer_address`    TEXT         NOT NULL,
    `status`              ENUM('pending','processing','completed') DEFAULT 'pending',
    `completed_at`        TIMESTAMP    NULL DEFAULT NULL,
    `purged_at`           TIMESTAMP    NULL DEFAULT NULL,
    `created_at`          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(50)  UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
