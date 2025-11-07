<?php
class ControllerStockSupply extends Controller
{
    public function create()
    {
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplyDate = isset($_POST['supply_date']) ? $_POST['supply_date'] : date('Y-m-d');
            $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();
            $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : array();

            if (!$productIds) {
                $error = 'Добавьте хотя бы один товар к поставке.';
            } else {
                $this->db->query('INSERT INTO supply_orders (supply_date) VALUES (:supply_date)', array(
                    'supply_date' => $supplyDate,
                ));
                $supplyId = $this->db->lastInsertId();

                foreach ($productIds as $index => $productId) {
                    $postedQuantity = isset($quantities[$index]) ? $quantities[$index] : 0;
                    $quantity = max(0, (int)$postedQuantity);
                    if ($productId && $quantity > 0) {
                        $this->db->query('INSERT INTO supply_order_items (supply_id, product_id, quantity) VALUES (:supply_id, :product_id, :quantity)', array(
                            'supply_id' => $supplyId,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                        ));

                        $existing = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array(
                            'product_id' => $productId,
                        ));

                        if ($existing) {
                            $this->db->query('UPDATE stock_items SET quantity = quantity + :quantity WHERE product_id = :product_id', array(
                                'quantity' => $quantity,
                                'product_id' => $productId,
                            ));
                        } else {
                            $this->db->query('INSERT INTO stock_items (product_id, quantity) VALUES (:product_id, :quantity)', array(
                                'product_id' => $productId,
                                'quantity' => $quantity,
                            ));
                        }
                    }
                }

                redirect(admin_url('stock/supply', array('action' => 'history')));
            }
        }

        $this->render('stock/supply_form', array(
            'error' => isset($error) ? $error : null,
        ));
    }

    public function history()
    {
        require_login();
        $supplies = $this->db->fetchAll('SELECT s.*, COUNT(i.id) AS items_count, COALESCE(SUM(i.quantity), 0) AS total_quantity
            FROM supply_orders s
            LEFT JOIN supply_order_items i ON s.id = i.supply_id
            GROUP BY s.id
            ORDER BY s.supply_date DESC, s.id DESC');

        $this->render('stock/supply_history', array(
            'supplies' => $supplies,
        ));
    }

    public function search()
    {
        require_login();
        header('Content-Type: application/json; charset=utf-8');
        $termInput = isset($_GET['term']) ? $_GET['term'] : '';
        $term = strtoupper(trim($termInput));
        $results = array();
        if ($term !== '') {
            $results = $this->db->fetchAll('SELECT id, name, model FROM products WHERE UPPER(name) LIKE :term OR model LIKE :term ORDER BY name ASC LIMIT 10', array(
                'term' => '%' . $term . '%',
            ));
        }
        echo json_encode($results);
        exit;
    }
}
