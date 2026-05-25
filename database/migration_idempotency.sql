-- Migration to add Idempotency Key for duplicate order prevention
ALTER TABLE `orders`
ADD COLUMN `idempotency_key` VARCHAR(255) NULL AFTER `order_number`,
ADD UNIQUE INDEX `idx_idempotency_key` (`idempotency_key`);
