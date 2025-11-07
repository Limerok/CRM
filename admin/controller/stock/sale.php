<?php
class ControllerStockSale extends Controller
{
    public function index()
    {
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $saleDate = isset($_POST['sale_date']) ? $_POST['sale_date'] : date('Y-m-d');
            $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();
            $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : array();

            if (!$productIds) {
                $error = 'Добавьте хотя бы один товар для продажи.';
            } else {
                $insufficient = array();
                foreach ($productIds as $index => $productId) {
                    $postedQuantity = isset($quantities[$index]) ? $quantities[$index] : 0;
                    $quantity = max(0, (int)$postedQuantity);
                    if (!$productId || $quantity <= 0) {
                        continue;
                    }
                    $stock = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array(
                        'product_id' => $productId,
                    ));
                    if (!$stock || $stock['quantity'] < $quantity) {
                        $product = $this->db->fetch('SELECT name, model FROM products WHERE id = :id', array('id' => $productId));
                        $productName = isset($product['name']) ? $product['name'] : 'Товар';
                        $productModel = isset($product['model']) ? $product['model'] : '';
                        $insufficient[] = $productName . ' (' . $productModel . ')';
                    }
                }

                if ($insufficient) {
                    $error = 'Недостаточно товара на складе: ' . implode(', ', $insufficient);
                } else {
                    $this->db->query('INSERT INTO sales (sale_date) VALUES (:sale_date)', array(
                        'sale_date' => $saleDate,
                    ));
                    $saleId = $this->db->lastInsertId();

                    foreach ($productIds as $index => $productId) {
                        $postedQuantity = isset($quantities[$index]) ? $quantities[$index] : 0;
                        $quantity = max(0, (int)$postedQuantity);
                        if ($productId && $quantity > 0) {
                            $this->db->query('INSERT INTO sale_items (sale_id, product_id, quantity) VALUES (:sale_id, :product_id, :quantity)', array(
                                'sale_id' => $saleId,
                                'product_id' => $productId,
                                'quantity' => $quantity,
                            ));
                            $this->db->query('UPDATE stock_items SET quantity = quantity - :quantity WHERE product_id = :product_id', array(
                                'quantity' => $quantity,
                                'product_id' => $productId,
                            ));
                        }
                    }

                    $success = 'Продажа успешно сохранена.';
                }
            }
        }

        $this->render('stock/sale_form', array(
            'error' => isset($error) ? $error : null,
            'success' => isset($success) ? $success : null,
        ));
    }
}
