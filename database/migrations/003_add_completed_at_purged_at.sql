-- Migration: add completed_at to record when an order is marked completed,
-- and purged_at to record when the 30-day post-completion data purge ran.
ALTER TABLE `orders`
    ADD COLUMN `completed_at` TIMESTAMP NULL DEFAULT NULL AFTER `status`,
    ADD COLUMN `purged_at`    TIMESTAMP NULL DEFAULT NULL AFTER `completed_at`;
