<?php
class ControllerStockSupply extends Controller
{
    private function aggregateItemsFromPost($productIds, $quantities)
    {
        $aggregated = array();

        foreach ((array)$productIds as $index => $productId) {
            $id = (int)$productId;
            $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 0;

            if ($id <= 0 || $quantity <= 0) {
                continue;
            }

            if (!isset($aggregated[$id])) {
                $aggregated[$id] = 0;
            }

            $aggregated[$id] += $quantity;
        }

        return $aggregated;
    }

    private function aggregateSupplyItems($supplyId)
    {
        $rows = $this->db->fetchAll('SELECT product_id, quantity FROM supply_order_items WHERE supply_id = :supply_id', array(
            'supply_id' => $supplyId,
        ));

        $aggregated = array();
        foreach ($rows as $row) {
            $productId = (int)$row['product_id'];
            $quantity = (int)$row['quantity'];
            if (!isset($aggregated[$productId])) {
                $aggregated[$productId] = 0;
            }
            $aggregated[$productId] += $quantity;
        }

        return $aggregated;
    }

    private function buildFormItems(array $aggregatedItems)
    {
        if (!$aggregatedItems) {
            return array();
        }

        $placeholders = array();
        $params = array();
        $index = 0;
        foreach (array_keys($aggregatedItems) as $productId) {
            $placeholder = ':pid' . $index;
            $placeholders[] = $placeholder;
            $params['pid' . $index] = $productId;
            $index++;
        }

        $products = array();
        if ($placeholders) {
            $productRows = $this->db->fetchAll('SELECT id, name, model FROM products WHERE id IN (' . implode(', ', $placeholders) . ')', $params);
            foreach ($productRows as $productRow) {
                $products[(int)$productRow['id']] = $productRow;
            }
        }

        $items = array();
        foreach ($aggregatedItems as $productId => $quantity) {
            $product = isset($products[$productId]) ? $products[$productId] : array('name' => 'Товар', 'model' => '');
            $items[] = array(
                'product_id' => $productId,
                'name' => $product['name'],
                'model' => $product['model'],
                'quantity' => $quantity,
            );
        }

        return $items;
    }

    public function create()
    {
        require_login();
        $formItems = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplyDate = isset($_POST['supply_date']) ? $_POST['supply_date'] : date('Y-m-d');
            $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();
            $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : array();

            $aggregatedItems = $this->aggregateItemsFromPost($productIds, $quantities);

            if (!$aggregatedItems) {
                $error = 'Добавьте хотя бы один товар к поставке.';
            } else {
                $this->db->query('INSERT INTO supply_orders (supply_date) VALUES (:supply_date)', array(
                    'supply_date' => $supplyDate,
                ));
                $supplyId = $this->db->lastInsertId();

                foreach ($aggregatedItems as $productId => $quantity) {
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

                redirect(admin_url('stock/supply', array('action' => 'history', 'success' => 'Поставка успешно добавлена.')));
            }

            $formItems = $this->buildFormItems(isset($aggregatedItems) ? $aggregatedItems : array());
            $supply = array('supply_date' => $supplyDate);
        }

        $this->render('stock/supply_form', array(
            'error' => isset($error) ? $error : null,
            'supply' => isset($supply) ? $supply : null,
            'form_items' => $formItems,
            'page_title' => 'Создание поставки',
            'submit_label' => 'Сохранить поставку',
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
            'error' => isset($_GET['error']) ? $_GET['error'] : null,
            'success' => isset($_GET['success']) ? $_GET['success'] : null,
        ));
    }

    public function view()
    {
        require_login();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            redirect(admin_url('stock/supply', array('action' => 'history')));
        }

        $supply = $this->db->fetch('SELECT * FROM supply_orders WHERE id = :id', array('id' => $id));
        if (!$supply) {
            redirect(admin_url('stock/supply', array('action' => 'history')));
        }

        $items = $this->db->fetchAll('SELECT soi.product_id, soi.quantity, p.name, p.model FROM supply_order_items soi
            LEFT JOIN products p ON soi.product_id = p.id
            WHERE soi.supply_id = :supply_id
            ORDER BY p.name ASC', array('supply_id' => $id));

        $totalQuantity = 0;
        foreach ($items as $item) {
            $totalQuantity += (int)$item['quantity'];
        }

        $this->render('stock/supply_view', array(
            'supply' => $supply,
            'items' => $items,
            'total_quantity' => $totalQuantity,
        ));
    }

    public function edit()
    {
        require_login();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            redirect(admin_url('stock/supply', array('action' => 'history')));
        }

        $supply = $this->db->fetch('SELECT * FROM supply_orders WHERE id = :id', array('id' => $id));
        if (!$supply) {
            redirect(admin_url('stock/supply', array('action' => 'history')));
        }

        $existingAggregated = $this->aggregateSupplyItems($id);
        $formItems = $this->buildFormItems($existingAggregated);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplyDate = isset($_POST['supply_date']) ? $_POST['supply_date'] : $supply['supply_date'];
            $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();
            $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : array();

            $aggregatedItems = $this->aggregateItemsFromPost($productIds, $quantities);

            if (!$aggregatedItems) {
                $error = 'Добавьте хотя бы один товар к поставке.';
            } else {
                $diffs = array();
                $insufficient = array();
                $allProductIds = array_unique(array_merge(array_keys($existingAggregated), array_keys($aggregatedItems)));

                foreach ($allProductIds as $productId) {
                    $oldQty = isset($existingAggregated[$productId]) ? $existingAggregated[$productId] : 0;
                    $newQty = isset($aggregatedItems[$productId]) ? $aggregatedItems[$productId] : 0;
                    $diff = $newQty - $oldQty;

                    if ($diff < 0) {
                        $stock = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array(
                            'product_id' => $productId,
                        ));
                        $currentQuantity = $stock ? (int)$stock['quantity'] : 0;
                        if ($currentQuantity + $diff < 0) {
                            $product = $this->db->fetch('SELECT name, model FROM products WHERE id = :id', array('id' => $productId));
                            $productName = $product ? $product['name'] : 'Товар';
                            $productModel = $product ? $product['model'] : '';
                            $insufficient[] = $productName . ' (' . $productModel . ')';
                        }
                    }

                    $diffs[$productId] = $diff;
                }

                if (!$insufficient) {
                    $this->db->query('UPDATE supply_orders SET supply_date = :supply_date WHERE id = :id', array(
                        'supply_date' => $supplyDate,
                        'id' => $id,
                    ));

                    $this->db->query('DELETE FROM supply_order_items WHERE supply_id = :supply_id', array('supply_id' => $id));

                    foreach ($aggregatedItems as $productId => $quantity) {
                        $this->db->query('INSERT INTO supply_order_items (supply_id, product_id, quantity) VALUES (:supply_id, :product_id, :quantity)', array(
                            'supply_id' => $id,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                        ));
                    }

                    foreach ($diffs as $productId => $diff) {
                        if ($diff === 0) {
                            continue;
                        }

                        $existingStock = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array(
                            'product_id' => $productId,
                        ));

                        if ($existingStock) {
                            $this->db->query('UPDATE stock_items SET quantity = quantity + :diff WHERE product_id = :product_id', array(
                                'diff' => $diff,
                                'product_id' => $productId,
                            ));
                        } elseif ($diff > 0) {
                            $this->db->query('INSERT INTO stock_items (product_id, quantity) VALUES (:product_id, :quantity)', array(
                                'product_id' => $productId,
                                'quantity' => $diff,
                            ));
                        }
                    }

                    redirect(admin_url('stock/supply', array('action' => 'history', 'success' => 'Поставка обновлена.')));
                } else {
                    $error = 'Недостаточно товаров на складе для пересчёта: ' . implode(', ', $insufficient);
                }
            }

            $supply['supply_date'] = $supplyDate;
            $existingAggregated = $aggregatedItems;
            $formItems = $this->buildFormItems($aggregatedItems);
        }

        $this->render('stock/supply_form', array(
            'error' => isset($error) ? $error : null,
            'supply' => $supply,
            'form_items' => $formItems,
            'page_title' => 'Редактирование поставки',
            'submit_label' => 'Обновить поставку',
        ));
    }

    public function delete()
    {
        require_login();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            redirect(admin_url('stock/supply', array('action' => 'history')));
        }

        $supply = $this->db->fetch('SELECT * FROM supply_orders WHERE id = :id', array('id' => $id));
        if (!$supply) {
            redirect(admin_url('stock/supply', array('action' => 'history')));
        }

        $aggregatedItems = $this->aggregateSupplyItems($id);
        $insufficient = array();

        foreach ($aggregatedItems as $productId => $quantity) {
            $stock = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array(
                'product_id' => $productId,
            ));
            $currentQuantity = $stock ? (int)$stock['quantity'] : 0;
            if ($currentQuantity < $quantity) {
                $product = $this->db->fetch('SELECT name, model FROM products WHERE id = :id', array('id' => $productId));
                $productName = $product ? $product['name'] : 'Товар';
                $productModel = $product ? $product['model'] : '';
                $insufficient[] = $productName . ' (' . $productModel . ')';
            }
        }

        if ($insufficient) {
            redirect(admin_url('stock/supply', array('action' => 'history', 'error' => 'Невозможно удалить поставку: недостаточно остатка по товарам: ' . implode(', ', $insufficient))));
        }

        foreach ($aggregatedItems as $productId => $quantity) {
            $this->db->query('UPDATE stock_items SET quantity = quantity - :quantity WHERE product_id = :product_id', array(
                'quantity' => $quantity,
                'product_id' => $productId,
            ));
        }

        $this->db->query('DELETE FROM supply_orders WHERE id = :id', array('id' => $id));

        redirect(admin_url('stock/supply', array('action' => 'history', 'success' => 'Поставка удалена.')));
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
