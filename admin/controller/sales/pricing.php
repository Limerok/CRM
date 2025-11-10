<?php
class ControllerSalesPricing extends Controller
{
    private function getProductPricingMap($sourceId)
    {
        if ($sourceId <= 0) {
            return array();
        }

        $rows = $this->db->fetchAll('SELECT product_id, sale_price, profit_percent, payment_type, payment_value, logistics_type, logistics_value, reviews_type, reviews_value FROM product_pricing WHERE source_id = :source_id', array(
            'source_id' => $sourceId,
        ));

        $map = array();
        foreach ($rows as $row) {
            $productId = isset($row['product_id']) ? (int)$row['product_id'] : 0;
            if (!$productId) {
                continue;
            }

            $paymentType = isset($row['payment_type']) ? $row['payment_type'] : 'percent';
            $logisticsType = isset($row['logistics_type']) ? $row['logistics_type'] : 'percent';
            $reviewsType = isset($row['reviews_type']) ? $row['reviews_type'] : 'percent';

            $map[$productId] = array(
                'sale_price' => isset($row['sale_price']) ? (float)$row['sale_price'] : null,
                'profit_percent' => isset($row['profit_percent']) ? (float)$row['profit_percent'] : 0.0,
                'payment_type' => in_array($paymentType, array('percent', 'fixed'), true) ? $paymentType : 'percent',
                'payment_value' => isset($row['payment_value']) ? (float)$row['payment_value'] : 0.0,
                'logistics_type' => in_array($logisticsType, array('percent', 'fixed'), true) ? $logisticsType : 'percent',
                'logistics_value' => isset($row['logistics_value']) ? (float)$row['logistics_value'] : 0.0,
                'reviews_type' => in_array($reviewsType, array('percent', 'fixed'), true) ? $reviewsType : 'percent',
                'reviews_value' => isset($row['reviews_value']) ? (float)$row['reviews_value'] : 0.0,
            );
        }

        return $map;
    }

    public function index()
    {
        require_login();

        $selectedSourceId = isset($_GET['source_id']) ? (int)$_GET['source_id'] : 0;

        $sources = $this->db->fetchAll('SELECT id, name FROM order_sources ORDER BY name ASC');
        $selectedSource = null;
        foreach ($sources as $source) {
            $sourceId = isset($source['id']) ? (int)$source['id'] : 0;
            if ($selectedSourceId && $selectedSourceId === $sourceId) {
                $selectedSource = $source;
                break;
            }
        }

        if ($selectedSourceId && $selectedSource === null) {
            $selectedSourceId = 0;
        }

        if ($selectedSource === null && !empty($sources)) {
            $selectedSource = $sources[0];
            $selectedSourceId = isset($selectedSource['id']) ? (int)$selectedSource['id'] : 0;
        }

        $selectedSourceName = $selectedSource && isset($selectedSource['name']) ? $selectedSource['name'] : '';

        $currencies = $this->db->fetchAll('SELECT id, code, value FROM currencies');
        $currencyValuesByCode = array();
        $currencyValuesById = array();
        foreach ($currencies as $currency) {
            $code = isset($currency['code']) ? strtoupper($currency['code']) : '';
            $currencyValuesByCode[$code] = isset($currency['value']) ? (float)$currency['value'] : 1.0;
            $currencyValuesById[(int)$currency['id']] = isset($currency['value']) ? (float)$currency['value'] : 1.0;
        }

        $defaultCurrencyId = (int)get_setting('config_currency_id', 0);
        $defaultCurrencyCode = 'RUB';
        $defaultCurrencyValue = 1.0;
        if ($defaultCurrencyId && isset($currencyValuesById[$defaultCurrencyId])) {
            $defaultCurrencyValue = $currencyValuesById[$defaultCurrencyId] ?: 1.0;
            foreach ($currencies as $currency) {
                if ((int)$currency['id'] === $defaultCurrencyId) {
                    $defaultCurrencyCode = strtoupper($currency['code']);
                    break;
                }
            }
        }

        if ($defaultCurrencyValue == 0.0) {
            $defaultCurrencyValue = 1.0;
        }

        $productsRaw = $this->db->fetchAll("SELECT p.id, p.name, p.model, p.series, p.purchase_price, p.purchase_currency, p.category_id, m.name AS manufacturer_name
            FROM products p
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
            ORDER BY p.name ASC");

        $pricingMap = $this->getProductPricingMap($selectedSourceId);

        $commissionMap = array();
        if ($selectedSourceId) {
            $commissionRows = $this->db->fetchAll('SELECT category_id, commission_percent FROM category_source_commissions WHERE source_id = :source_id', array(
                'source_id' => $selectedSourceId,
            ));
            foreach ($commissionRows as $commissionRow) {
                $commissionMap[(int)$commissionRow['category_id']] = (float)$commissionRow['commission_percent'];
            }
        }

        $products = array();
        foreach ($productsRaw as $product) {
            $purchasePrice = isset($product['purchase_price']) ? (float)$product['purchase_price'] : 0.0;
            $purchaseCurrencyCode = isset($product['purchase_currency']) ? strtoupper($product['purchase_currency']) : $defaultCurrencyCode;
            $currencyValue = isset($currencyValuesByCode[$purchaseCurrencyCode]) ? $currencyValuesByCode[$purchaseCurrencyCode] : 1.0;
            $purchaseCost = $purchasePrice * ($currencyValue / $defaultCurrencyValue);

            $categoryId = isset($product['category_id']) ? (int)$product['category_id'] : 0;
            $commissionPercent = ($selectedSourceId && $categoryId && isset($commissionMap[$categoryId])) ? $commissionMap[$categoryId] : 0.0;

            $productId = isset($product['id']) ? (int)$product['id'] : 0;
            $pricing = isset($pricingMap[$productId]) ? $pricingMap[$productId] : array();
            $salePrice = array_key_exists('sale_price', $pricing) ? (float)$pricing['sale_price'] : null;
            $profitPercent = array_key_exists('profit_percent', $pricing) ? (float)$pricing['profit_percent'] : 0.0;
            $paymentType = isset($pricing['payment_type']) ? $pricing['payment_type'] : 'percent';
            $paymentValue = array_key_exists('payment_value', $pricing) ? (float)$pricing['payment_value'] : 0.0;
            $logisticsType = isset($pricing['logistics_type']) ? $pricing['logistics_type'] : 'percent';
            $logisticsValue = array_key_exists('logistics_value', $pricing) ? (float)$pricing['logistics_value'] : 0.0;
            $reviewsType = isset($pricing['reviews_type']) ? $pricing['reviews_type'] : 'percent';
            $reviewsValue = array_key_exists('reviews_value', $pricing) ? (float)$pricing['reviews_value'] : 0.0;

            $products[] = array(
                'id' => $productId,
                'name' => isset($product['name']) ? $product['name'] : '',
                'model' => isset($product['model']) ? $product['model'] : '',
                'series' => isset($product['series']) ? $product['series'] : '',
                'manufacturer' => isset($product['manufacturer_name']) ? $product['manufacturer_name'] : '',
                'purchase_cost' => $purchaseCost,
                'commission_percent' => $commissionPercent,
                'sale_price' => $salePrice,
                'profit_percent' => $profitPercent,
                'payment_type' => in_array($paymentType, array('percent', 'fixed'), true) ? $paymentType : 'percent',
                'payment_value' => $paymentValue,
                'logistics_type' => in_array($logisticsType, array('percent', 'fixed'), true) ? $logisticsType : 'percent',
                'logistics_value' => $logisticsValue,
                'reviews_type' => in_array($reviewsType, array('percent', 'fixed'), true) ? $reviewsType : 'percent',
                'reviews_value' => $reviewsValue,
            );
        }

        $this->render('sales/pricing', array(
            'sources' => $sources,
            'selected_source_id' => $selectedSourceId,
            'selected_source_name' => $selectedSourceName,
            'products' => $products,
            'default_currency_code' => $defaultCurrencyCode,
        ));
    }

    public function save()
    {
        require_login();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(array('success' => false, 'error' => 'Метод не поддерживается.'));
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Некорректный формат данных.'));
            return;
        }

        $sourceId = isset($payload['source_id']) ? (int)$payload['source_id'] : 0;
        if ($sourceId <= 0) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Источник не выбран.'));
            return;
        }

        $sourceExists = $this->db->fetch('SELECT id FROM order_sources WHERE id = :id', array('id' => $sourceId));
        if (!$sourceExists) {
            http_response_code(404);
            echo json_encode(array('success' => false, 'error' => 'Источник не найден.'));
            return;
        }

        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : array();
        if (!$items) {
            echo json_encode(array('success' => true, 'saved' => 0));
            return;
        }

        $saved = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            if ($productId <= 0) {
                continue;
            }

            $salePrice = isset($item['sale_price']) ? (float)$item['sale_price'] : 0.0;
            if ($salePrice < 0) {
                $salePrice = 0.0;
            }

            $profitPercent = isset($item['profit_percent']) ? (float)$item['profit_percent'] : 0.0;

            $paymentType = isset($item['payment_type']) && in_array($item['payment_type'], array('percent', 'fixed'), true) ? $item['payment_type'] : 'percent';
            $paymentValue = isset($item['payment_value']) ? (float)$item['payment_value'] : 0.0;
            if ($paymentValue < 0) {
                $paymentValue = 0.0;
            }

            $logisticsType = isset($item['logistics_type']) && in_array($item['logistics_type'], array('percent', 'fixed'), true) ? $item['logistics_type'] : 'percent';
            $logisticsValue = isset($item['logistics_value']) ? (float)$item['logistics_value'] : 0.0;
            if ($logisticsValue < 0) {
                $logisticsValue = 0.0;
            }

            $reviewsType = isset($item['reviews_type']) && in_array($item['reviews_type'], array('percent', 'fixed'), true) ? $item['reviews_type'] : 'percent';
            $reviewsValue = isset($item['reviews_value']) ? (float)$item['reviews_value'] : 0.0;
            if ($reviewsValue < 0) {
                $reviewsValue = 0.0;
            }

            $params = array(
                'product_id' => $productId,
                'source_id' => $sourceId,
                'sale_price' => round($salePrice, 4),
                'profit_percent' => round($profitPercent, 4),
                'payment_type' => $paymentType,
                'payment_value' => round($paymentValue, 4),
                'logistics_type' => $logisticsType,
                'logistics_value' => round($logisticsValue, 4),
                'reviews_type' => $reviewsType,
                'reviews_value' => round($reviewsValue, 4),
            );

            $existing = $this->db->fetch('SELECT id FROM product_pricing WHERE product_id = :product_id AND source_id = :source_id', array(
                'product_id' => $productId,
                'source_id' => $sourceId,
            ));

            if ($existing) {
                $params['id'] = (int)$existing['id'];
                $this->db->query('UPDATE product_pricing SET sale_price = :sale_price, profit_percent = :profit_percent, payment_type = :payment_type, payment_value = :payment_value, logistics_type = :logistics_type, logistics_value = :logistics_value, reviews_type = :reviews_type, reviews_value = :reviews_value WHERE id = :id', $params);
            } else {
                $this->db->query('INSERT INTO product_pricing (product_id, source_id, sale_price, profit_percent, payment_type, payment_value, logistics_type, logistics_value, reviews_type, reviews_value) VALUES (:product_id, :source_id, :sale_price, :profit_percent, :payment_type, :payment_value, :logistics_type, :logistics_value, :reviews_type, :reviews_value)', $params);
            }

            $saved++;
        }

        echo json_encode(array('success' => true, 'saved' => $saved));
    }
}
