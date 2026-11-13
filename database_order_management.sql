-- Update tabel orders yang sudah ada
ALTER TABLE orders
ADD COLUMN shipping_courier VARCHAR(100) NULL AFTER shipping_address,
ADD COLUMN tracking_number VARCHAR(100) NULL AFTER shipping_courier,
ADD COLUMN shipped_at TIMESTAMP NULL AFTER updated_at,
ADD COLUMN completed_at TIMESTAMP NULL AFTER shipped_at;
