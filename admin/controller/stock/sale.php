<?php
class ControllerStockSale extends Controller
{
    public function index()
    {
        require_login();

        $sources = $this->fetchSources();
        $sourceMap = $this->mapSources($sources);

        $orderStatuses = $this->fetchOrderStatuses();
        $defaultStatusId = (int)get_setting('config_default_order_status_id', 0);
        $defaultStatusName = $this->findStatusNameById($orderStatuses, $defaultStatusId);
        $allowNegativeStock = $this->isNegativeStockAllowed();

        $formItems = array();
        $errors = array();
        $success = isset($_GET['success']) ? $_GET['success'] : null;
        $isMultiSale = false;
        $selectedSaleDate = date('Y-m-d');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isMultiSaleInput = isset($_POST['is_multi_sale']) ? $_POST['is_multi_sale'] : 0;
            if (is_array($isMultiSaleInput)) {
                $isMultiSaleInput = end($isMultiSaleInput);
            }
            $isMultiSale = (string)$isMultiSaleInput === '1';

            list($items, $formItems, $productCounts, $taskNumbers, $orderNumbers, $errors, $saleDates) = $this->collectSaleItemsFromRequest($sourceMap, $isMultiSale);

            if (!empty($saleDates)) {
                $selectedSaleDate = $saleDates[0];
            }

            if (!$errors && !$isMultiSale) {
                $taskConflicts = $this->findExistingValues('task_number', $taskNumbers);
                if ($taskConflicts) {
                    $errors[] = 'Номера заданий уже используются: ' . implode(', ', $taskConflicts) . '. Перепроверьте номер.';
                }

                $orderConflicts = $this->findExistingValues('order_number', $orderNumbers);
                if ($orderConflicts) {
                    $errors[] = 'Номера заказов уже используются: ' . implode(', ', $orderConflicts) . '. Перепроверьте номер.';
                }
            }

            if (!$errors && !$allowNegativeStock) {
                $insufficient = $this->checkStockAvailability($formItems, $productCounts);
                if ($insufficient) {
                    $errors[] = 'Недостаточно товара на складе: ' . implode(', ', $insufficient);
                }
            }

            if (!$errors) {
                $saleDate = !empty($saleDates) ? $saleDates[0] : date('Y-m-d');
                $this->db->query('INSERT INTO sales (sale_date, is_multi_sale) VALUES (:sale_date, :is_multi_sale)', array(
                    'sale_date' => $saleDate,
                    'is_multi_sale' => $isMultiSale ? 1 : 0,
                ));
                $saleId = (int)$this->db->lastInsertId();

                foreach ($items as $item) {
                    $this->db->query('INSERT INTO sale_items (sale_id, product_id, order_date, source_id, order_status, task_number, order_number, seller_price, source_sale_price) VALUES (:sale_id, :product_id, :order_date, :source_id, :order_status, :task_number, :order_number, :seller_price, :source_sale_price)', array(
                        'sale_id' => $saleId,
                        'product_id' => $item['product_id'],
                        'order_date' => $item['order_date'],
                        'source_id' => $item['source_id'],
                        'order_status' => $item['order_status'],
                        'task_number' => $item['task_number'],
                        'order_number' => $item['order_number'],
                        'seller_price' => isset($item['seller_price']) ? $item['seller_price'] : null,
                        'source_sale_price' => isset($item['source_sale_price']) ? $item['source_sale_price'] : null,
                    ));
                }

                foreach ($productCounts as $productId => $count) {
                    $this->updateStockQuantity($productId, -$count);
                }

                $success = 'Продажа успешно сохранена.';
                $formItems = array();
                $isMultiSale = false;
                $selectedSaleDate = date('Y-m-d');
            }
        }

        $recentSales = $this->db->fetchAll('SELECT s.*, COUNT(i.id) AS items_count
            FROM sales s
            LEFT JOIN sale_items i ON s.id = i.sale_id
            GROUP BY s.id
            ORDER BY s.sale_date DESC, s.id DESC
            LIMIT 10');

        $this->render('stock/sale_form', array(
            'error' => $errors ? implode(' ', $errors) : null,
            'errors_list' => $errors,
            'success' => $success,
            'recent_sales' => $recentSales,
            'form_items' => $formItems,
            'sources' => $sources,
            'statuses' => $orderStatuses,
            'default_status_id' => $defaultStatusId,
            'default_status_name' => $defaultStatusName,
            'allow_negative_stock' => $allowNegativeStock,
            'is_multi_sale' => $isMultiSale,
            'selected_sale_date' => $selectedSaleDate,
            'page_title' => 'Продажа со склада',
            'submit_label' => 'Сохранить продажу',
            'form_action' => admin_url('stock/sale'),
        ));
    }

    public function edit()
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

        $isMultiSale = isset($sale['is_multi_sale']) ? (int)$sale['is_multi_sale'] === 1 : false;
        $selectedSaleDate = isset($sale['sale_date']) ? $sale['sale_date'] : date('Y-m-d');

        $sources = $this->fetchSources();
        $sourceMap = $this->mapSources($sources);

        $existingRows = $this->db->fetchAll('SELECT si.*, p.name AS product_name, p.model AS product_model FROM sale_items si
            LEFT JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = :sale_id
            ORDER BY si.id ASC', array('sale_id' => $id));

        $orderStatuses = $this->fetchOrderStatuses();
        $defaultStatusId = (int)get_setting('config_default_order_status_id', 0);
        $defaultStatusName = $this->findStatusNameById($orderStatuses, $defaultStatusId);
        $allowNegativeStock = $this->isNegativeStockAllowed();

        $formItems = array();
        $existingCounts = array();
        foreach ($existingRows as $row) {
            $productId = (int)$row['product_id'];
            $existingCounts[$productId] = isset($existingCounts[$productId]) ? $existingCounts[$productId] + 1 : 1;
            $formItems[] = array(
                'product_id' => $productId,
                'product_name' => $row['product_name'] ? $row['product_name'] : 'Товар',
                'product_model' => $row['product_model'] ? $row['product_model'] : '',
                'order_date' => $row['order_date'],
                'sale_date' => $sale['sale_date'],
                'source_id' => $row['source_id'] !== null ? (int)$row['source_id'] : null,
                'order_status' => $row['order_status'] !== null ? $row['order_status'] : '',
                'task_number' => $row['task_number'] !== null ? $row['task_number'] : '',
                'order_number' => $row['order_number'] !== null ? $row['order_number'] : '',
                'seller_price' => $row['seller_price'] !== null ? (float)$row['seller_price'] : null,
                'source_sale_price' => $row['source_sale_price'] !== null ? (float)$row['source_sale_price'] : null,
            );
        }

        $errors = array();
        $success = isset($_GET['success']) ? $_GET['success'] : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isMultiSaleInput = isset($_POST['is_multi_sale']) ? $_POST['is_multi_sale'] : ($isMultiSale ? '1' : '0');
            if (is_array($isMultiSaleInput)) {
                $isMultiSaleInput = end($isMultiSaleInput);
            }
            $isMultiSale = (string)$isMultiSaleInput === '1';

            list($items, $formItems, $productCounts, $taskNumbers, $orderNumbers, $errors, $saleDates) = $this->collectSaleItemsFromRequest($sourceMap, $isMultiSale);

            if (!empty($saleDates)) {
                $selectedSaleDate = $saleDates[0];
            }

            if (!$errors && !$isMultiSale) {
                $taskConflicts = $this->findExistingValues('task_number', $taskNumbers, $id);
                if ($taskConflicts) {
                    $errors[] = 'Номера заданий уже используются: ' . implode(', ', $taskConflicts) . '. Перепроверьте номер.';
                }

                $orderConflicts = $this->findExistingValues('order_number', $orderNumbers, $id);
                if ($orderConflicts) {
                    $errors[] = 'Номера заказов уже используются: ' . implode(', ', $orderConflicts) . '. Перепроверьте номер.';
                }
            }

            $diffs = array();
            if (!$errors) {
                $allProductIds = array_unique(array_merge(array_keys($existingCounts), array_keys($productCounts)));
                foreach ($allProductIds as $productId) {
                    $oldCount = isset($existingCounts[$productId]) ? $existingCounts[$productId] : 0;
                    $newCount = isset($productCounts[$productId]) ? $productCounts[$productId] : 0;
                    $diffs[$productId] = $newCount - $oldCount;
                }

                if (!$allowNegativeStock) {
                    $insufficient = $this->checkStockAvailabilityForDiff($formItems, $diffs);
                    if ($insufficient) {
                        $errors[] = 'Недостаточно товара на складе: ' . implode(', ', $insufficient);
                    }
                }
            }

            if (!$errors) {
                $saleDate = !empty($saleDates) ? $saleDates[0] : $selectedSaleDate;
                $this->db->query('UPDATE sales SET sale_date = :sale_date, is_multi_sale = :is_multi_sale WHERE id = :id', array(
                    'sale_date' => $saleDate,
                    'is_multi_sale' => $isMultiSale ? 1 : 0,
                    'id' => $id,
                ));

                $this->db->query('DELETE FROM sale_items WHERE sale_id = :sale_id', array('sale_id' => $id));

                foreach ($items as $item) {
                    $this->db->query('INSERT INTO sale_items (sale_id, product_id, order_date, source_id, order_status, task_number, order_number, seller_price, source_sale_price) VALUES (:sale_id, :product_id, :order_date, :source_id, :order_status, :task_number, :order_number, :seller_price, :source_sale_price)', array(
                        'sale_id' => $id,
                        'product_id' => $item['product_id'],
                        'order_date' => $item['order_date'],
                        'source_id' => $item['source_id'],
                        'order_status' => $item['order_status'],
                        'task_number' => $item['task_number'],
                        'order_number' => $item['order_number'],
                        'seller_price' => isset($item['seller_price']) ? $item['seller_price'] : null,
                        'source_sale_price' => isset($item['source_sale_price']) ? $item['source_sale_price'] : null,
                    ));
                }

                foreach ($diffs as $productId => $diff) {
                    if ($diff !== 0) {
                        $this->updateStockQuantity($productId, -$diff);
                    }
                }

                redirect(admin_url('stock/sale', array('action' => 'edit', 'id' => $id, 'success' => 'Продажа обновлена.')));
            }
        }

        $this->render('stock/sale_form', array(
            'error' => $errors ? implode(' ', $errors) : null,
            'errors_list' => $errors,
            'success' => $success,
            'recent_sales' => null,
            'form_items' => $formItems,
            'sources' => $sources,
            'statuses' => $orderStatuses,
            'default_status_id' => $defaultStatusId,
            'default_status_name' => $defaultStatusName,
            'allow_negative_stock' => $allowNegativeStock,
            'is_multi_sale' => $isMultiSale,
            'selected_sale_date' => $selectedSaleDate,
            'page_title' => 'Редактирование продажи #' . $id,
            'submit_label' => 'Обновить продажу',
            'form_action' => admin_url('stock/sale', array('action' => 'edit', 'id' => $id)),
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

        $items = $this->db->fetchAll('SELECT si.*, p.name, p.model, os.name AS source_name FROM sale_items si
            LEFT JOIN products p ON si.product_id = p.id
            LEFT JOIN order_sources os ON si.source_id = os.id
            WHERE si.sale_id = :sale_id
            ORDER BY si.id ASC', array('sale_id' => $id));

        $totalItems = count($items);

        $this->render('stock/sale_view', array(
            'sale' => $sale,
            'items' => $items,
            'total_items' => $totalItems,
        ));
    }

    public function delete()
    {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(admin_url('stock/sale'));
        }

        $id = isset($_POST['sale_id']) ? (int)$_POST['sale_id'] : 0;
        if (!$id) {
            redirect(admin_url('stock/sale'));
        }

        $sale = $this->db->fetch('SELECT id FROM sales WHERE id = :id', array('id' => $id));
        if (!$sale) {
            redirect(admin_url('stock/sale'));
        }

        $items = $this->db->fetchAll('SELECT product_id, COUNT(*) AS items_count FROM sale_items WHERE sale_id = :sale_id GROUP BY product_id', array(
            'sale_id' => $id,
        ));

        foreach ($items as $item) {
            $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            $count = isset($item['items_count']) ? (int)$item['items_count'] : 0;
            if ($productId > 0 && $count > 0) {
                $this->updateStockQuantity($productId, $count);
            }
        }

        $this->db->query('DELETE FROM sales WHERE id = :id', array('id' => $id));

        redirect(admin_url('stock/sale', array('success' => 'Продажа удалена.')));
    }

    private function fetchSources()
    {
        return $this->db->fetchAll('SELECT id, name FROM order_sources ORDER BY name ASC');
    }

    private function mapSources(array $sources)
    {
        $map = array();
        foreach ($sources as $source) {
            $map[(int)$source['id']] = $source;
        }
        return $map;
    }

    private function updateStockQuantity($productId, $change)
    {
        $productId = (int)$productId;
        $change = (int)$change;

        if ($productId <= 0 || $change === 0) {
            return;
        }

        $existing = $this->db->fetch('SELECT quantity FROM stock_items WHERE product_id = :product_id', array('product_id' => $productId));

        if ($existing) {
            $this->db->query('UPDATE stock_items SET quantity = quantity + :change WHERE product_id = :product_id', array(
                'change' => $change,
                'product_id' => $productId,
            ));
        } else {
            $this->db->query('INSERT INTO stock_items (product_id, quantity) VALUES (:product_id, :quantity)', array(
                'product_id' => $productId,
                'quantity' => $change,
            ));
        }
    }

    private function fetchOrderStatuses()
    {
        return $this->db->fetchAll('SELECT id, name FROM order_statuses ORDER BY name ASC');
    }

    private function findStatusNameById(array $statuses, $id)
    {
        foreach ($statuses as $status) {
            if ((int)$status['id'] === (int)$id) {
                return $status['name'];
            }
        }

        return '';
    }

    private function collectSaleItemsFromRequest(array $sourceMap, $isMultiSale = false)
    {
        $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();
        $orderDates = isset($_POST['order_dates']) ? $_POST['order_dates'] : array();
        $saleDates = isset($_POST['sale_dates']) ? $_POST['sale_dates'] : array();
        $sourceIds = isset($_POST['source_ids']) ? $_POST['source_ids'] : array();
        $orderStatuses = isset($_POST['order_statuses']) ? $_POST['order_statuses'] : array();
        $taskNumbersInput = isset($_POST['task_numbers']) ? $_POST['task_numbers'] : array();
        $orderNumbersInput = isset($_POST['order_numbers']) ? $_POST['order_numbers'] : array();
        $sellerPricesInput = isset($_POST['seller_prices']) ? $_POST['seller_prices'] : array();
        $sourceSalePricesInput = isset($_POST['source_sale_prices']) ? $_POST['source_sale_prices'] : array();

        $rowCount = max(
            count($productIds),
            count($orderDates),
            count($saleDates),
            count($sourceIds),
            count($orderStatuses),
            count($taskNumbersInput),
            count($orderNumbersInput),
            count($sellerPricesInput),
            count($sourceSalePricesInput)
        );

        $rawRows = array();
        $preparedItems = array();
        $errors = array();

        for ($i = 0; $i < $rowCount; $i++) {
            $productId = isset($productIds[$i]) ? (int)$productIds[$i] : 0;
            $orderDate = isset($orderDates[$i]) ? trim($orderDates[$i]) : '';
            $saleDate = isset($saleDates[$i]) ? trim($saleDates[$i]) : '';
            $sourceValue = isset($sourceIds[$i]) ? trim($sourceIds[$i]) : '';
            $status = isset($orderStatuses[$i]) ? trim($orderStatuses[$i]) : '';
            $taskNumber = isset($taskNumbersInput[$i]) ? trim($taskNumbersInput[$i]) : '';
            $orderNumber = isset($orderNumbersInput[$i]) ? trim($orderNumbersInput[$i]) : '';
            $sellerPriceInput = isset($sellerPricesInput[$i]) ? trim($sellerPricesInput[$i]) : '';
            $sourceSalePriceInput = isset($sourceSalePricesInput[$i]) ? trim($sourceSalePricesInput[$i]) : '';

            if ($productId <= 0 && $orderDate === '' && $saleDate === '' && $sourceValue === '' && $status === '' && $taskNumber === '' && $orderNumber === '' && $sellerPriceInput === '' && $sourceSalePriceInput === '') {
                continue;
            }

            $sourceId = null;
            if ($sourceValue !== '') {
                $candidate = (int)$sourceValue;
                if (isset($sourceMap[$candidate])) {
                    $sourceId = $candidate;
                } else {
                    $errors[] = 'Выбран несуществующий источник.';
                }
            }

            $isValid = true;
            if ($productId <= 0) {
                $errors[] = 'Выберите товар для каждой позиции.';
                $isValid = false;
            }

            if ($orderDate === '') {
                $errors[] = 'Укажите дату заказа для каждой позиции.';
                $isValid = false;
            } else {
                $date = DateTime::createFromFormat('Y-m-d', $orderDate);
                if (!$date || $date->format('Y-m-d') !== $orderDate) {
                    $errors[] = 'Укажите корректную дату заказа.';
                    $isValid = false;
                }
            }

            if ($saleDate === '') {
                $errors[] = 'Укажите дату продажи для каждой позиции.';
                $isValid = false;
            } else {
                $saleDateObject = DateTime::createFromFormat('Y-m-d', $saleDate);
                if (!$saleDateObject || $saleDateObject->format('Y-m-d') !== $saleDate) {
                    $errors[] = 'Укажите корректную дату продажи.';
                    $isValid = false;
                }
            }

            $sellerPrice = null;
            if ($sellerPriceInput !== '') {
                if (!is_numeric(str_replace(',', '.', $sellerPriceInput))) {
                    $errors[] = 'Цена продавца должна быть числом.';
                    $isValid = false;
                } else {
                    $sellerPrice = (float)str_replace(',', '.', $sellerPriceInput);
                    if ($sellerPrice < 0) {
                        $errors[] = 'Цена продавца не может быть отрицательной.';
                        $isValid = false;
                    }
                }
            }

            $sourceSalePrice = null;
            if ($sourceSalePriceInput !== '') {
                if (!is_numeric(str_replace(',', '.', $sourceSalePriceInput))) {
                    $errors[] = 'Цена реализации источником должна быть числом.';
                    $isValid = false;
                } else {
                    $sourceSalePrice = (float)str_replace(',', '.', $sourceSalePriceInput);
                    if ($sourceSalePrice < 0) {
                        $errors[] = 'Цена реализации источником не может быть отрицательной.';
                        $isValid = false;
                    }
                }
            }

            $rawRows[] = array(
                'product_id' => $productId,
                'order_date' => $orderDate,
                'sale_date' => $saleDate,
                'source_id' => $sourceId,
                'order_status' => $status,
                'task_number' => $taskNumber,
                'order_number' => $orderNumber,
                'seller_price' => $sellerPriceInput,
                'source_sale_price' => $sourceSalePriceInput,
                'seller_price_value' => $sellerPrice,
                'source_sale_price_value' => $sourceSalePrice,
            );

            if ($isValid) {
                $preparedItems[] = array(
                    'product_id' => $productId,
                    'order_date' => $orderDate,
                    'sale_date' => $saleDate,
                    'source_id' => $sourceId,
                    'order_status' => $status !== '' ? $status : null,
                    'task_number' => $taskNumber !== '' ? $taskNumber : null,
                    'order_number' => $orderNumber !== '' ? $orderNumber : null,
                    'seller_price' => $sellerPrice,
                    'source_sale_price' => $sourceSalePrice,
                );
            }
        }

        if (!$preparedItems) {
            if (empty($rawRows)) {
                $errors[] = 'Добавьте хотя бы один товар в продажу.';
            }
        }

        $productIdsForFetch = array();
        foreach ($rawRows as $row) {
            if ($row['product_id'] > 0) {
                $productIdsForFetch[] = $row['product_id'];
            }
        }
        $productIdsForFetch = array_unique($productIdsForFetch);

        $productsById = array();
        if ($productIdsForFetch) {
            $placeholders = array();
            $params = array();
            foreach ($productIdsForFetch as $index => $productId) {
                $placeholder = ':pid' . $index;
                $placeholders[] = $placeholder;
                $params['pid' . $index] = $productId;
            }
            $productRows = $this->db->fetchAll('SELECT id, name, model FROM products WHERE id IN (' . implode(', ', $placeholders) . ')', $params);
            foreach ($productRows as $productRow) {
                $productsById[(int)$productRow['id']] = $productRow;
            }
        }

        $missingProducts = false;
        $formItems = array();
        foreach ($rawRows as $row) {
            $product = isset($productsById[$row['product_id']]) ? $productsById[$row['product_id']] : null;
            if ($row['product_id'] > 0 && !$product) {
                $missingProducts = true;
            }
            $formItems[] = array(
                'product_id' => $row['product_id'],
                'product_name' => $product ? $product['name'] : 'Товар',
                'product_model' => $product ? $product['model'] : '',
                'order_date' => $row['order_date'],
                'sale_date' => $row['sale_date'],
                'source_id' => $row['source_id'],
                'order_status' => $row['order_status'],
                'task_number' => $row['task_number'],
                'order_number' => $row['order_number'],
                'seller_price' => $row['seller_price'],
                'source_sale_price' => $row['source_sale_price'],
            );
        }

        if ($missingProducts) {
            $errors[] = 'Некоторые выбранные товары не найдены.';
        }

        $validItems = array();
        foreach ($preparedItems as $item) {
            if (isset($productsById[$item['product_id']])) {
                $validItems[] = $item;
            }
        }
        $preparedItems = $validItems;

        $productCounts = array();
        $taskNumbers = array();
        $orderNumbers = array();
        foreach ($preparedItems as $item) {
            $productId = $item['product_id'];
            $productCounts[$productId] = isset($productCounts[$productId]) ? $productCounts[$productId] + 1 : 1;
            if (!empty($item['task_number'])) {
                $taskNumbers[] = $item['task_number'];
            }
            if (!empty($item['order_number'])) {
                $orderNumbers[] = $item['order_number'];
            }
        }

        $saleDateValues = array();
        foreach ($preparedItems as $item) {
            if (!empty($item['sale_date'])) {
                $saleDateValues[] = $item['sale_date'];
            }
        }
        $saleDateValues = array_values(array_unique($saleDateValues));

        if (!$isMultiSale && count($preparedItems) > 1) {
            $errors[] = 'Для одиночной продажи можно добавить только один товар.';
        }

        if (count($saleDateValues) > 1) {
            $errors[] = 'Дата продажи должна быть одинаковой для всех товаров.';
        }

        if (!$isMultiSale) {
            $taskDuplicates = array_count_values($taskNumbers);
            foreach ($taskDuplicates as $value => $count) {
                if ($count > 1) {
                    $errors[] = 'Номера заданий должны быть уникальными.';
                    break;
                }
            }

            $orderDuplicates = array_count_values($orderNumbers);
            foreach ($orderDuplicates as $value => $count) {
                if ($count > 1) {
                    $errors[] = 'Номера заказов должны быть уникальными.';
                    break;
                }
            }
        }

        $saleDatesForResult = array();
        foreach ($rawRows as $row) {
            if (!empty($row['sale_date'])) {
                $saleDatesForResult[] = $row['sale_date'];
            }
        }
        $saleDatesForResult = array_values(array_unique($saleDatesForResult));

        return array($preparedItems, $formItems, $productCounts, array_unique($taskNumbers), array_unique($orderNumbers), $errors, $saleDatesForResult);
    }

    private function checkStockAvailability(array $formItems, array $productCounts)
    {
        if (!$productCounts) {
            return array();
        }

        $placeholders = array();
        $params = array();
        $index = 0;
        foreach ($productCounts as $productId => $count) {
            $placeholder = ':pid' . $index;
            $placeholders[] = $placeholder;
            $params['pid' . $index] = $productId;
            $index++;
        }

        $stockRows = $this->db->fetchAll('SELECT product_id, quantity FROM stock_items WHERE product_id IN (' . implode(', ', $placeholders) . ')', $params);
        $stockById = array();
        foreach ($stockRows as $stockRow) {
            $stockById[(int)$stockRow['product_id']] = (int)$stockRow['quantity'];
        }

        $productNames = array();
        foreach ($formItems as $item) {
            $productId = (int)$item['product_id'];
            if ($productId > 0 && !isset($productNames[$productId])) {
                $name = $item['product_name'];
                $model = $item['product_model'];
                $productNames[$productId] = trim($name . ($model ? ' (' . $model . ')' : ''));
            }
        }

        $insufficient = array();
        foreach ($productCounts as $productId => $count) {
            $available = isset($stockById[$productId]) ? $stockById[$productId] : 0;
            if ($available < $count) {
                $insufficient[] = isset($productNames[$productId]) ? $productNames[$productId] : ('Товар #' . $productId);
            }
        }

        return $insufficient;
    }

    private function checkStockAvailabilityForDiff(array $formItems, array $diffs)
    {
        $positiveDiffs = array();
        foreach ($diffs as $productId => $diff) {
            if ($diff > 0) {
                $positiveDiffs[$productId] = $diff;
            }
        }

        if (!$positiveDiffs) {
            return array();
        }

        return $this->checkStockAvailability($formItems, $positiveDiffs);
    }

    private function isNegativeStockAllowed()
    {
        return (int)get_setting('config_allow_negative_stock', 0) === 1;
    }

    private function findExistingValues($column, array $values, $excludeSaleId = null)
    {
        $values = array_filter($values, function ($value) {
            return $value !== null && $value !== '';
        });

        if (!$values) {
            return array();
        }

        $placeholders = array();
        $params = array();
        foreach (array_values($values) as $index => $value) {
            $placeholder = ':val' . $index;
            $placeholders[] = $placeholder;
            $params['val' . $index] = $value;
        }

        $query = 'SELECT ' . $column . ' FROM sale_items WHERE ' . $column . ' IN (' . implode(', ', $placeholders) . ')';
        if ($excludeSaleId !== null) {
            $query .= ' AND sale_id != :exclude_sale_id';
            $params['exclude_sale_id'] = $excludeSaleId;
        }

        $rows = $this->db->fetchAll($query, $params);
        $duplicates = array();
        foreach ($rows as $row) {
            if (isset($row[$column])) {
                $duplicates[] = $row[$column];
            }
        }

        return $duplicates;
    }
}
