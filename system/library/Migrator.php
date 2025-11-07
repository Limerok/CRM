<?php
class Migrator
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function migrate()
    {
        $this->createUsers();
        $this->createCategories();
        $this->createManufacturers();
        $this->createProducts();
        $this->createSupplies();
        $this->createStock();
        $this->createSales();
    }

    private function createUsers()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createCategories()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            parent_id INT DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createManufacturers()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS manufacturers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createProducts()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            model VARCHAR(64) NOT NULL,
            series VARCHAR(64) DEFAULT NULL,
            manufacturer_id INT DEFAULT NULL,
            purchase_price DECIMAL(15,4) DEFAULT 0,
            purchase_currency ENUM('RUB','USD','EUR') DEFAULT 'RUB',
            weight DECIMAL(15,4) DEFAULT 0,
            weight_unit ENUM('kg','g') DEFAULT 'kg',
            weight_package DECIMAL(15,4) DEFAULT 0,
            length DECIMAL(15,4) DEFAULT 0,
            width DECIMAL(15,4) DEFAULT 0,
            height DECIMAL(15,4) DEFAULT 0,
            length_package DECIMAL(15,4) DEFAULT 0,
            width_package DECIMAL(15,4) DEFAULT 0,
            height_package DECIMAL(15,4) DEFAULT 0,
            length_unit ENUM('mm','cm','m') DEFAULT 'mm',
            category_id INT DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE SET NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createSupplies()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS supply_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supply_date DATE NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS supply_order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supply_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (supply_id) REFERENCES supply_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createStock()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS stock_items (
            product_id INT PRIMARY KEY,
            quantity INT NOT NULL DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createSales()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_date DATE NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS sale_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
