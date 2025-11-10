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

            $paymentType = isset($row['payment_type']) ? $row['payment_type'] : null;
            $logisticsType = isset($row['logistics_type']) ? $row['logistics_type'] : null;
            $reviewsType = isset($row['reviews_type']) ? $row['reviews_type'] : null;

            $map[$productId] = array(
                'sale_price' => isset($row['sale_price']) ? (float)$row['sale_price'] : null,
                'profit_percent' => isset($row['profit_percent']) && $row['profit_percent'] !== null ? (float)$row['profit_percent'] : null,
                'payment_type' => in_array($paymentType, array('percent', 'fixed'), true) ? $paymentType : null,
                'payment_value' => isset($row['payment_value']) && $row['payment_value'] !== null ? (float)$row['payment_value'] : null,
                'logistics_type' => in_array($logisticsType, array('percent', 'fixed'), true) ? $logisticsType : null,
                'logistics_value' => isset($row['logistics_value']) && $row['logistics_value'] !== null ? (float)$row['logistics_value'] : null,
                'reviews_type' => in_array($reviewsType, array('percent', 'fixed'), true) ? $reviewsType : null,
                'reviews_value' => isset($row['reviews_value']) && $row['reviews_value'] !== null ? (float)$row['reviews_value'] : null,
            );
        }

        return $map;
    }

    private function getDefaultsForSource($sourceId)
    {
        if ($sourceId <= 0) {
            return array(
                'tax_percent' => 0.0,
                'profit_percent' => null,
                'payment_type' => 'percent',
                'payment_value' => null,
                'logistics_type' => 'percent',
                'logistics_value' => null,
                'reviews_type' => 'percent',
                'reviews_value' => null,
            );
        }

        $row = $this->db->fetch('SELECT tax_percent, profit_percent, payment_type, payment_value, logistics_type, logistics_value, reviews_type, reviews_value FROM product_pricing_defaults WHERE source_id = :source_id', array(
            'source_id' => $sourceId,
        ));

        if (!$row) {
            return array(
                'tax_percent' => 0.0,
                'profit_percent' => null,
                'payment_type' => 'percent',
                'payment_value' => null,
                'logistics_type' => 'percent',
                'logistics_value' => null,
                'reviews_type' => 'percent',
                'reviews_value' => null,
            );
        }

        return array(
            'tax_percent' => isset($row['tax_percent']) ? (float)$row['tax_percent'] : 0.0,
            'profit_percent' => isset($row['profit_percent']) && $row['profit_percent'] !== null ? (float)$row['profit_percent'] : null,
            'payment_type' => in_array(isset($row['payment_type']) ? $row['payment_type'] : '', array('percent', 'fixed'), true) ? $row['payment_type'] : 'percent',
            'payment_value' => isset($row['payment_value']) && $row['payment_value'] !== null ? (float)$row['payment_value'] : null,
            'logistics_type' => in_array(isset($row['logistics_type']) ? $row['logistics_type'] : '', array('percent', 'fixed'), true) ? $row['logistics_type'] : 'percent',
            'logistics_value' => isset($row['logistics_value']) && $row['logistics_value'] !== null ? (float)$row['logistics_value'] : null,
            'reviews_type' => in_array(isset($row['reviews_type']) ? $row['reviews_type'] : '', array('percent', 'fixed'), true) ? $row['reviews_type'] : 'percent',
            'reviews_value' => isset($row['reviews_value']) && $row['reviews_value'] !== null ? (float)$row['reviews_value'] : null,
        );
    }

    private function normalizeDefaults(array $defaults)
    {
        $normalized = array();

        $taxPercent = isset($defaults['tax_percent']) ? (float)$defaults['tax_percent'] : 0.0;
        if ($taxPercent < 0) {
            $taxPercent = 0.0;
        }
        $normalized['tax_percent'] = $taxPercent;

        $profitPercent = null;
        if (isset($defaults['profit_percent']) && $defaults['profit_percent'] !== '' && $defaults['profit_percent'] !== null) {
            $profitPercent = (float)$defaults['profit_percent'];
            if ($profitPercent < 0) {
                $profitPercent = 0.0;
            }
        }
        $normalized['profit_percent'] = $profitPercent;

        $normalized['payment_type'] = in_array(isset($defaults['payment_type']) ? $defaults['payment_type'] : '', array('percent', 'fixed'), true) ? $defaults['payment_type'] : 'percent';
        $normalized['logistics_type'] = in_array(isset($defaults['logistics_type']) ? $defaults['logistics_type'] : '', array('percent', 'fixed'), true) ? $defaults['logistics_type'] : 'percent';
        $normalized['reviews_type'] = in_array(isset($defaults['reviews_type']) ? $defaults['reviews_type'] : '', array('percent', 'fixed'), true) ? $defaults['reviews_type'] : 'percent';

        foreach (array('payment_value', 'logistics_value', 'reviews_value') as $key) {
            $value = null;
            if (isset($defaults[$key]) && $defaults[$key] !== '' && $defaults[$key] !== null) {
                $floatValue = (float)$defaults[$key];
                if ($floatValue < 0) {
                    $floatValue = 0.0;
                }
                $value = $floatValue;
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function saveDefaults($sourceId, array $defaults)
    {
        if ($sourceId <= 0) {
            return;
        }

        $existing = $this->db->fetch('SELECT id FROM product_pricing_defaults WHERE source_id = :source_id', array(
            'source_id' => $sourceId,
        ));

        $params = array(
            'source_id' => $sourceId,
            'tax_percent' => $defaults['tax_percent'],
            'profit_percent' => $defaults['profit_percent'],
            'payment_type' => $defaults['payment_type'],
            'payment_value' => $defaults['payment_value'],
            'logistics_type' => $defaults['logistics_type'],
            'logistics_value' => $defaults['logistics_value'],
            'reviews_type' => $defaults['reviews_type'],
            'reviews_value' => $defaults['reviews_value'],
        );

        if ($existing) {
            $paramsForUpdate = $params;
            $paramsForUpdate['id'] = (int)$existing['id'];
            unset($paramsForUpdate['source_id']);

            $this->db->query('UPDATE product_pricing_defaults SET tax_percent = :tax_percent, profit_percent = :profit_percent, payment_type = :payment_type, payment_value = :payment_value, logistics_type = :logistics_type, logistics_value = :logistics_value, reviews_type = :reviews_type, reviews_value = :reviews_value WHERE id = :id', $paramsForUpdate);
        } else {
            $this->db->query('INSERT INTO product_pricing_defaults (source_id, tax_percent, profit_percent, payment_type, payment_value, logistics_type, logistics_value, reviews_type, reviews_value) VALUES (:source_id, :tax_percent, :profit_percent, :payment_type, :payment_value, :logistics_type, :logistics_value, :reviews_type, :reviews_value)', $params);
        }
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
            ORDER BY p.sort_order ASC, p.name ASC");

        $pricingMap = $this->getProductPricingMap($selectedSourceId);
        $defaults = $this->getDefaultsForSource($selectedSourceId);

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
            $profitPercent = array_key_exists('profit_percent', $pricing) ? $pricing['profit_percent'] : null;
            $paymentType = isset($pricing['payment_type']) ? $pricing['payment_type'] : null;
            $paymentValue = array_key_exists('payment_value', $pricing) ? $pricing['payment_value'] : null;
            $logisticsType = isset($pricing['logistics_type']) ? $pricing['logistics_type'] : null;
            $logisticsValue = array_key_exists('logistics_value', $pricing) ? $pricing['logistics_value'] : null;
            $reviewsType = isset($pricing['reviews_type']) ? $pricing['reviews_type'] : null;
            $reviewsValue = array_key_exists('reviews_value', $pricing) ? $pricing['reviews_value'] : null;

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
                'payment_type' => in_array($paymentType, array('percent', 'fixed'), true) ? $paymentType : null,
                'payment_value' => $paymentValue,
                'logistics_type' => in_array($logisticsType, array('percent', 'fixed'), true) ? $logisticsType : null,
                'logistics_value' => $logisticsValue,
                'reviews_type' => in_array($reviewsType, array('percent', 'fixed'), true) ? $reviewsType : null,
                'reviews_value' => $reviewsValue,
            );
        }

        $this->render('sales/pricing', array(
            'sources' => $sources,
            'selected_source_id' => $selectedSourceId,
            'selected_source_name' => $selectedSourceName,
            'products' => $products,
            'default_currency_code' => $defaultCurrencyCode,
            'pricing_defaults' => $defaults,
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

        if (array_key_exists('defaults', $payload)) {
            $defaultsInput = is_array($payload['defaults']) ? $payload['defaults'] : array();
            $normalizedDefaults = $this->normalizeDefaults($defaultsInput);
            $this->saveDefaults($sourceId, $normalizedDefaults);
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

            $profitPercent = null;
            if (isset($item['profit_percent']) && $item['profit_percent'] !== null) {
                $profitPercent = (float)$item['profit_percent'];
                if ($profitPercent < 0) {
                    $profitPercent = 0.0;
                }
            }

            $paymentType = isset($item['payment_type']) ? $item['payment_type'] : null;
            $logisticsType = isset($item['logistics_type']) ? $item['logistics_type'] : null;
            $reviewsType = isset($item['reviews_type']) ? $item['reviews_type'] : null;

            $paymentValue = isset($item['payment_value']) && $item['payment_value'] !== null ? (float)$item['payment_value'] : null;
            if ($paymentValue !== null && $paymentValue < 0) {
                $paymentValue = 0.0;
            }

            $logisticsValue = isset($item['logistics_value']) && $item['logistics_value'] !== null ? (float)$item['logistics_value'] : null;
            if ($logisticsValue !== null && $logisticsValue < 0) {
                $logisticsValue = 0.0;
            }

            $reviewsValue = isset($item['reviews_value']) && $item['reviews_value'] !== null ? (float)$item['reviews_value'] : null;
            if ($reviewsValue !== null && $reviewsValue < 0) {
                $reviewsValue = 0.0;
            }

            $params = array(
                'product_id' => $productId,
                'source_id' => $sourceId,
                'sale_price' => round($salePrice, 4),
                'profit_percent' => $profitPercent !== null ? round($profitPercent, 4) : null,
                'payment_type' => in_array($paymentType, array('percent', 'fixed'), true) ? $paymentType : null,
                'payment_value' => $paymentValue !== null ? round($paymentValue, 4) : null,
                'logistics_type' => in_array($logisticsType, array('percent', 'fixed'), true) ? $logisticsType : null,
                'logistics_value' => $logisticsValue !== null ? round($logisticsValue, 4) : null,
                'reviews_type' => in_array($reviewsType, array('percent', 'fixed'), true) ? $reviewsType : null,
                'reviews_value' => $reviewsValue !== null ? round($reviewsValue, 4) : null,
            );

            $existing = $this->db->fetch('SELECT id FROM product_pricing WHERE product_id = :product_id AND source_id = :source_id', array(
                'product_id' => $productId,
                'source_id' => $sourceId,
            ));

            if ($existing) {
                $paramsForUpdate = $params;
                $paramsForUpdate['id'] = (int)$existing['id'];
                unset($paramsForUpdate['product_id'], $paramsForUpdate['source_id']);

                $this->db->query('UPDATE product_pricing SET sale_price = :sale_price, profit_percent = :profit_percent, payment_type = :payment_type, payment_value = :payment_value, logistics_type = :logistics_type, logistics_value = :logistics_value, reviews_type = :reviews_type, reviews_value = :reviews_value WHERE id = :id', $paramsForUpdate);
            } else {
                $this->db->query('INSERT INTO product_pricing (product_id, source_id, sale_price, profit_percent, payment_type, payment_value, logistics_type, logistics_value, reviews_type, reviews_value) VALUES (:product_id, :source_id, :sale_price, :profit_percent, :payment_type, :payment_value, :logistics_type, :logistics_value, :reviews_type, :reviews_value)', $params);
            }

            $saved++;
        }

        echo json_encode(array('success' => true, 'saved' => $saved));
    }

    public function save_defaults()
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

        $defaultsInput = isset($payload['defaults']) && is_array($payload['defaults']) ? $payload['defaults'] : array();
        $normalizedDefaults = $this->normalizeDefaults($defaultsInput);
        $this->saveDefaults($sourceId, $normalizedDefaults);

        echo json_encode(array('success' => true));
    }
}
