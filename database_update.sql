-- Tabel Kategori
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Produk
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Dummy Categories
INSERT IGNORE INTO categories (id, name, slug, description) VALUES
(1, 'Kalung', 'kalung', 'Berbagai macam kalung elegan'),
(2, 'Gelang', 'gelang', 'Gelang kekinian'),
(3, 'Cincin', 'cincin', 'Cincin eksklusif');

-- Dummy Products
INSERT IGNORE INTO products (category_id, name, slug, description, price, stock, status) VALUES
(1, 'Kalung Mutiara', 'kalung-mutiara', 'Kalung mutiara asli dari laut dalam', 250000.00, 10, 'active'),
(2, 'Gelang Emas Putih', 'gelang-emas-putih', 'Gelang cantik bahan emas putih 18k', 1500000.00, 5, 'active');
