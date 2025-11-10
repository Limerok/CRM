<?php
class ControllerSalesPricing extends Controller
{
    public function index()
    {
        require_login();

        $selectedSourceId = isset($_GET['source_id']) ? (int)$_GET['source_id'] : 0;

        $sources = $this->db->fetchAll('SELECT id, name FROM order_sources ORDER BY name ASC');
        $sourceIds = array();
        foreach ($sources as $source) {
            $sourceIds[] = (int)$source['id'];
        }

        if ($selectedSourceId && !in_array($selectedSourceId, $sourceIds, true)) {
            $selectedSourceId = 0;
        }

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

        $productsRaw = $this->db->fetchAll('SELECT p.id, p.name, p.model, p.series, p.purchase_price, p.purchase_currency, p.category_id, m.name AS manufacturer_name
            FROM products p
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
            ORDER BY p.name ASC');

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

            $products[] = array(
                'id' => (int)$product['id'],
                'name' => isset($product['name']) ? $product['name'] : '',
                'model' => isset($product['model']) ? $product['model'] : '',
                'series' => isset($product['series']) ? $product['series'] : '',
                'manufacturer' => isset($product['manufacturer_name']) ? $product['manufacturer_name'] : '',
                'purchase_cost' => $purchaseCost,
                'commission_percent' => $commissionPercent,
            );
        }

        $paymentType = isset($_GET['payment_type']) && in_array($_GET['payment_type'], array('percent', 'fixed'), true) ? $_GET['payment_type'] : 'percent';
        $paymentValue = isset($_GET['payment_value']) ? (float)$_GET['payment_value'] : 0.0;
        $logisticsType = isset($_GET['logistics_type']) && in_array($_GET['logistics_type'], array('percent', 'fixed'), true) ? $_GET['logistics_type'] : 'percent';
        $logisticsValue = isset($_GET['logistics_value']) ? (float)$_GET['logistics_value'] : 0.0;
        $reviewsType = isset($_GET['reviews_type']) && in_array($_GET['reviews_type'], array('percent', 'fixed'), true) ? $_GET['reviews_type'] : 'percent';
        $reviewsValue = isset($_GET['reviews_value']) ? (float)$_GET['reviews_value'] : 0.0;

        $this->render('sales/pricing', array(
            'sources' => $sources,
            'selected_source_id' => $selectedSourceId,
            'products' => $products,
            'default_currency_code' => $defaultCurrencyCode,
            'expenses' => array(
                'payment_type' => $paymentType,
                'payment_value' => $paymentValue,
                'logistics_type' => $logisticsType,
                'logistics_value' => $logisticsValue,
                'reviews_type' => $reviewsType,
                'reviews_value' => $reviewsValue,
            ),
        ));
    }
}
