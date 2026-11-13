-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS nnaco_sa;
USE nnaco_sa;

-- Buat tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin', 'operator', 'owner') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hapus data dummy jika sudah ada (untuk keperluan reset testing)
TRUNCATE TABLE users;

-- Masukkan data dummy (Password semua adalah: password123)
-- Hash password123 = $2y$10$w.93Nl8aH0sR/9a41W/8VODH7k2NqK8jQZ10DkS2gXw/68Z4uTz7i
INSERT INTO users (name, email, password, role) VALUES
('Super Admin', 'admin@nnaco.com', '$2y$10$.p7JYZnhi9LAzpyYEAA.vu1Na6tlZS5LfCITH6g/QN3XmeEFkvNX2', 'admin'),
('Operator Satu', 'operator@nnaco.com', '$2y$10$.p7JYZnhi9LAzpyYEAA.vu1Na6tlZS5LfCITH6g/QN3XmeEFkvNX2', 'operator'),
('Owner Bos', 'owner@nnaco.com', '$2y$10$.p7JYZnhi9LAzpyYEAA.vu1Na6tlZS5LfCITH6g/QN3XmeEFkvNX2', 'owner'),
('Budi Customer', 'customer@nnaco.com', '$2y$10$.p7JYZnhi9LAzpyYEAA.vu1Na6tlZS5LfCITH6g/QN3XmeEFkvNX2', 'customer');
