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
        $this->createCurrencies();
        $this->createLengthClasses();
        $this->createWeightClasses();
        $this->createSettings();
        $this->createSupplies();
        $this->createStock();
        $this->createRecommendedStock();
        $this->createOrderSources();
        $this->createOrderStatuses();
        $this->createSales();
        $this->createCategorySourceCommissions();
        $this->createProductPricing();
        $this->createProductPricingDefaults();
    }

    private function createUsers()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            is_super_admin TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $hasSuperAdminColumn = $this->db->fetch("SHOW COLUMNS FROM users LIKE 'is_super_admin'");
        if (!$hasSuperAdminColumn) {
            $this->db->query('ALTER TABLE users ADD COLUMN is_super_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER password');
        }
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
            purchase_currency VARCHAR(3) NOT NULL DEFAULT 'RUB',
            weight DECIMAL(15,4) DEFAULT 0,
            weight_unit VARCHAR(32) NOT NULL DEFAULT 'kg',
            weight_package DECIMAL(15,4) DEFAULT 0,
            length DECIMAL(15,4) DEFAULT 0,
            width DECIMAL(15,4) DEFAULT 0,
            height DECIMAL(15,4) DEFAULT 0,
            length_package DECIMAL(15,4) DEFAULT 0,
            width_package DECIMAL(15,4) DEFAULT 0,
            height_package DECIMAL(15,4) DEFAULT 0,
            length_unit VARCHAR(32) NOT NULL DEFAULT 'mm',
            category_id INT DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE SET NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $purchaseCurrencyColumn = $this->db->fetch("SHOW COLUMNS FROM products LIKE 'purchase_currency'");
        if ($purchaseCurrencyColumn && stripos($purchaseCurrencyColumn['Type'], 'enum') !== false) {
            $this->db->query("ALTER TABLE products MODIFY purchase_currency VARCHAR(3) NOT NULL DEFAULT 'RUB'");
        }

        $weightUnitColumn = $this->db->fetch("SHOW COLUMNS FROM products LIKE 'weight_unit'");
        if ($weightUnitColumn && stripos($weightUnitColumn['Type'], 'enum') !== false) {
            $this->db->query("ALTER TABLE products MODIFY weight_unit VARCHAR(32) NOT NULL DEFAULT 'kg'");
        }

        $lengthUnitColumn = $this->db->fetch("SHOW COLUMNS FROM products LIKE 'length_unit'");
        if ($lengthUnitColumn && stripos($lengthUnitColumn['Type'], 'enum') !== false) {
            $this->db->query("ALTER TABLE products MODIFY length_unit VARCHAR(32) NOT NULL DEFAULT 'mm'");
        }
    }

    private function createCurrencies()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS currencies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(3) NOT NULL UNIQUE,
            value DECIMAL(15,8) NOT NULL DEFAULT 1.00000000,
            date_modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $count = $this->db->fetch('SELECT COUNT(*) AS total FROM currencies');
        if (!$count || (int)$count['total'] === 0) {
            $this->db->query("INSERT INTO currencies (name, code, value) VALUES
                ('Российский рубль', 'RUB', 1.00000000),
                ('Доллар США', 'USD', 82.00000000),
                ('Евро', 'EUR', 89.00000000)");
        }
    }

    private function createLengthClasses()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS length_classes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(32) NOT NULL UNIQUE,
            value DECIMAL(15,8) NOT NULL DEFAULT 1.00000000,
            sort_order INT NOT NULL DEFAULT 0,
            date_modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $count = $this->db->fetch('SELECT COUNT(*) AS total FROM length_classes');
        if (!$count || (int)$count['total'] === 0) {
            $this->db->query("INSERT INTO length_classes (name, code, value, sort_order) VALUES
                ('Миллиметр', 'mm', 1.00000000, 1),
                ('Сантиметр', 'cm', 10.00000000, 2),
                ('Метр', 'm', 1000.00000000, 3)");
        }
    }

    private function createWeightClasses()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS weight_classes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(32) NOT NULL UNIQUE,
            value DECIMAL(15,8) NOT NULL DEFAULT 1.00000000,
            sort_order INT NOT NULL DEFAULT 0,
            date_modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $count = $this->db->fetch('SELECT COUNT(*) AS total FROM weight_classes');
        if (!$count || (int)$count['total'] === 0) {
            $this->db->query("INSERT INTO weight_classes (name, code, value, sort_order) VALUES
                ('Грамм', 'g', 1.00000000, 1),
                ('Килограмм', 'kg', 1000.00000000, 2)");
        }
    }

    private function createSettings()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(64) NOT NULL PRIMARY KEY,
            `value` TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $currency = $this->db->fetch("SELECT id FROM currencies WHERE code = 'RUB' LIMIT 1");
        if ($currency) {
            $this->ensureSetting('config_currency_id', $currency['id']);
        }

        $length = $this->db->fetch("SELECT id FROM length_classes WHERE code = 'mm' LIMIT 1");
        if ($length) {
            $this->ensureSetting('config_length_class_id', $length['id']);
        }

        $weight = $this->db->fetch("SELECT id FROM weight_classes WHERE code = 'kg' LIMIT 1");
        if ($weight) {
            $this->ensureSetting('config_weight_class_id', $weight['id']);
        }

        $this->ensureSetting('config_default_order_status_id', 0);
        $this->ensureSetting('config_allow_negative_stock', 0);
    }

    private function ensureSetting($key, $value)
    {
        $exists = $this->db->fetch('SELECT `key` FROM settings WHERE `key` = :key', array('key' => $key));
        if (!$exists) {
            $this->db->query('INSERT INTO settings (`key`, `value`) VALUES (:key, :value)', array(
                'key' => $key,
                'value' => (string)$value,
            ));
        }
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

    private function createRecommendedStock()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_recommended_stock (
            product_id INT PRIMARY KEY,
            recommended_quantity INT DEFAULT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createOrderSources()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS order_sources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createOrderStatuses()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS order_statuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
            order_date DATE DEFAULT NULL,
            source_id INT DEFAULT NULL,
            order_status VARCHAR(128) DEFAULT NULL,
            task_number VARCHAR(64) DEFAULT NULL,
            order_number VARCHAR(64) DEFAULT NULL,
            seller_price DECIMAL(15,4) DEFAULT NULL,
            source_sale_price DECIMAL(15,4) DEFAULT NULL,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (source_id) REFERENCES order_sources(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $orderDateColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'order_date'");
        if (!$orderDateColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN order_date DATE DEFAULT NULL AFTER product_id');
        }

        $sourceColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'source_id'");
        if (!$sourceColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN source_id INT DEFAULT NULL AFTER order_date');
        }

        $orderStatusColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'order_status'");
        if (!$orderStatusColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN order_status VARCHAR(128) DEFAULT NULL AFTER source_id');
        }

        $taskNumberColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'task_number'");
        if (!$taskNumberColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN task_number VARCHAR(64) DEFAULT NULL AFTER order_status');
        }

        $orderNumberColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'order_number'");
        if (!$orderNumberColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN order_number VARCHAR(64) DEFAULT NULL AFTER task_number');
        }

        $sellerPriceColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'seller_price'");
        if (!$sellerPriceColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN seller_price DECIMAL(15,4) DEFAULT NULL AFTER order_number');
        }

        $sourceSalePriceColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'source_sale_price'");
        if (!$sourceSalePriceColumn) {
            $this->db->query('ALTER TABLE sale_items ADD COLUMN source_sale_price DECIMAL(15,4) DEFAULT NULL AFTER seller_price');
        }

        $quantityColumn = $this->db->fetch("SHOW COLUMNS FROM sale_items LIKE 'quantity'");
        if ($quantityColumn) {
            $this->db->query('ALTER TABLE sale_items DROP COLUMN quantity');
        }

        $taskNumberIndex = $this->db->fetch("SHOW INDEX FROM sale_items WHERE Key_name = 'task_number_unique'");
        if (!$taskNumberIndex) {
            $this->db->query('ALTER TABLE sale_items ADD UNIQUE KEY task_number_unique (task_number)');
        }

        $orderNumberIndex = $this->db->fetch("SHOW INDEX FROM sale_items WHERE Key_name = 'order_number_unique'");
        if (!$orderNumberIndex) {
            $this->db->query('ALTER TABLE sale_items ADD UNIQUE KEY order_number_unique (order_number)');
        }

        $sourceIndex = $this->db->fetch("SHOW INDEX FROM sale_items WHERE Key_name = 'source_id'");
        if (!$sourceIndex) {
            $this->db->query('ALTER TABLE sale_items ADD KEY source_id (source_id)');
        }

        $existingSourceFk = $this->db->fetch("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sale_items' AND COLUMN_NAME = 'source_id' AND REFERENCED_TABLE_NAME = 'order_sources'");
        if (!$existingSourceFk && $sourceColumn) {
            $this->db->query('ALTER TABLE sale_items ADD CONSTRAINT sale_items_source_fk FOREIGN KEY (source_id) REFERENCES order_sources(id) ON DELETE SET NULL');
        }
    }

    private function createCategorySourceCommissions()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS category_source_commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            source_id INT NOT NULL,
            commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_category_source (category_id, source_id),
            CONSTRAINT fk_category_source_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            CONSTRAINT fk_category_source_source FOREIGN KEY (source_id) REFERENCES order_sources(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function createProductPricing()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_pricing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            source_id INT NOT NULL,
            sale_price DECIMAL(15,4) NOT NULL DEFAULT 0,
            profit_percent DECIMAL(9,4) DEFAULT NULL,
            payment_type VARCHAR(16) DEFAULT NULL,
            payment_value DECIMAL(15,4) DEFAULT NULL,
            logistics_type VARCHAR(16) DEFAULT NULL,
            logistics_value DECIMAL(15,4) DEFAULT NULL,
            reviews_type VARCHAR(16) DEFAULT NULL,
            reviews_value DECIMAL(15,4) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_product_source (product_id, source_id),
            CONSTRAINT fk_product_pricing_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT fk_product_pricing_source FOREIGN KEY (source_id) REFERENCES order_sources(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $profitPercentColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'profit_percent'");
        if (!$profitPercentColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN profit_percent DECIMAL(9,4) DEFAULT NULL AFTER sale_price");
        } elseif (isset($profitPercentColumn['Null']) && strtoupper($profitPercentColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY profit_percent DECIMAL(9,4) DEFAULT NULL");
        }

        $paymentTypeColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'payment_type'");
        if (!$paymentTypeColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN payment_type VARCHAR(16) DEFAULT NULL AFTER sale_price");
        } elseif (isset($paymentTypeColumn['Null']) && strtoupper($paymentTypeColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY payment_type VARCHAR(16) DEFAULT NULL");
        }

        $paymentValueColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'payment_value'");
        if (!$paymentValueColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN payment_value DECIMAL(15,4) DEFAULT NULL AFTER payment_type");
        } elseif (isset($paymentValueColumn['Null']) && strtoupper($paymentValueColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY payment_value DECIMAL(15,4) DEFAULT NULL");
        }

        $logisticsTypeColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'logistics_type'");
        if (!$logisticsTypeColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN logistics_type VARCHAR(16) DEFAULT NULL AFTER payment_value");
        } elseif (isset($logisticsTypeColumn['Null']) && strtoupper($logisticsTypeColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY logistics_type VARCHAR(16) DEFAULT NULL");
        }

        $logisticsValueColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'logistics_value'");
        if (!$logisticsValueColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN logistics_value DECIMAL(15,4) DEFAULT NULL AFTER logistics_type");
        } elseif (isset($logisticsValueColumn['Null']) && strtoupper($logisticsValueColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY logistics_value DECIMAL(15,4) DEFAULT NULL");
        }

        $reviewsTypeColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'reviews_type'");
        if (!$reviewsTypeColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN reviews_type VARCHAR(16) DEFAULT NULL AFTER logistics_value");
        } elseif (isset($reviewsTypeColumn['Null']) && strtoupper($reviewsTypeColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY reviews_type VARCHAR(16) DEFAULT NULL");
        }

        $reviewsValueColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing LIKE 'reviews_value'");
        if (!$reviewsValueColumn) {
            $this->db->query("ALTER TABLE product_pricing ADD COLUMN reviews_value DECIMAL(15,4) DEFAULT NULL AFTER reviews_type");
        } elseif (isset($reviewsValueColumn['Null']) && strtoupper($reviewsValueColumn['Null']) === 'NO') {
            $this->db->query("ALTER TABLE product_pricing MODIFY reviews_value DECIMAL(15,4) DEFAULT NULL");
        }

        $uniqueIndex = $this->db->fetch("SHOW INDEX FROM product_pricing WHERE Key_name = 'uq_product_source'");
        if (!$uniqueIndex) {
            $this->db->query('ALTER TABLE product_pricing ADD UNIQUE KEY uq_product_source (product_id, source_id)');
        }
    }

    private function createProductPricingDefaults()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_pricing_defaults (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_id INT NOT NULL UNIQUE,
            tax_percent DECIMAL(9,4) NOT NULL DEFAULT 0,
            profit_percent DECIMAL(9,4) DEFAULT NULL,
            payment_type VARCHAR(16) NOT NULL DEFAULT 'percent',
            payment_value DECIMAL(15,4) DEFAULT NULL,
            logistics_type VARCHAR(16) NOT NULL DEFAULT 'percent',
            logistics_value DECIMAL(15,4) DEFAULT NULL,
            reviews_type VARCHAR(16) NOT NULL DEFAULT 'percent',
            reviews_value DECIMAL(15,4) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_pricing_defaults_source FOREIGN KEY (source_id) REFERENCES order_sources(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $taxPercentColumn = $this->db->fetch("SHOW COLUMNS FROM product_pricing_defaults LIKE 'tax_percent'");
        if (!$taxPercentColumn) {
            $this->db->query("ALTER TABLE product_pricing_defaults ADD COLUMN tax_percent DECIMAL(9,4) NOT NULL DEFAULT 0 AFTER source_id");
        }
    }
}
