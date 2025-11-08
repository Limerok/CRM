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
                $aggregatedItems = array();
                foreach ($productIds as $index => $productId) {
                    $productId = (int)$productId;
                    $postedQuantity = isset($quantities[$index]) ? $quantities[$index] : 0;
                    $quantity = max(0, (int)$postedQuantity);
                    if ($productId <= 0 || $quantity <= 0) {
                        continue;
                    }

                    if (!isset($aggregatedItems[$productId])) {
                        $aggregatedItems[$productId] = 0;
                    }

                    $aggregatedItems[$productId] += $quantity;
                }

                if (!$aggregatedItems) {
                    $error = 'Добавьте хотя бы один товар для продажи.';
                } else {
                    $insufficient = array();

                    foreach ($aggregatedItems as $productId => $totalQuantity) {
                        $stock = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array(
                            'product_id' => $productId,
                        ));

                        if (!$stock || $stock['quantity'] < $totalQuantity) {
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

                        foreach ($aggregatedItems as $productId => $totalQuantity) {
                            $this->db->query('INSERT INTO sale_items (sale_id, product_id, quantity) VALUES (:sale_id, :product_id, :quantity)', array(
                                'sale_id' => $saleId,
                                'product_id' => $productId,
                                'quantity' => $totalQuantity,
                            ));
                            $this->db->query('UPDATE stock_items SET quantity = quantity - :quantity WHERE product_id = :product_id', array(
                                'quantity' => $totalQuantity,
                                'product_id' => $productId,
                            ));
                        }

                        $success = 'Продажа успешно сохранена.';
                    }
                }
            }
        }

        $recentSales = $this->db->fetchAll('SELECT s.*, COUNT(i.id) AS items_count, COALESCE(SUM(i.quantity), 0) AS total_quantity
            FROM sales s
            LEFT JOIN sale_items i ON s.id = i.sale_id
            GROUP BY s.id
            ORDER BY s.sale_date DESC, s.id DESC
            LIMIT 10');

        $this->render('stock/sale_form', array(
            'error' => isset($error) ? $error : null,
            'success' => isset($success) ? $success : null,
            'recent_sales' => $recentSales,
        ));
    }

    public function view()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            redirect(admin_url('stock/sale'));
        }

        $sale = $this->db->fetch('SELECT * FROM sales WHERE id = :id', array('id' => $id));
        if (!$sale) {
            redirect(admin_url('stock/sale'));
        }

        $items = $this->db->fetchAll('SELECT si.*, p.name, p.model FROM sale_items si
            LEFT JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = :sale_id
            ORDER BY p.name ASC', array('sale_id' => $id));

        $totalQuantity = 0;
        foreach ($items as $item) {
            $totalQuantity += (int)$item['quantity'];
        }

        $this->render('stock/sale_view', array(
            'sale' => $sale,
            'items' => $items,
            'total_quantity' => $totalQuantity,
        ));
    }
}
