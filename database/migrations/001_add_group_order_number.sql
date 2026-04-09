-- Migration: add group_order_number column so that multiple items
-- placed together share a single customer-facing order reference.
ALTER TABLE `orders`
    ADD COLUMN `group_order_number` VARCHAR(20) NULL DEFAULT NULL AFTER `order_number`;
