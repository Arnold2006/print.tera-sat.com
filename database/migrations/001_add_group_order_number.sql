-- Migration 001: Add group_order_number to orders table
-- All items belonging to the same customer session share the same group_order_number.
ALTER TABLE `orders`
    ADD COLUMN `group_order_number` VARCHAR(20) DEFAULT NULL AFTER `order_number`,
    ADD INDEX `idx_group_order_number` (`group_order_number`);
