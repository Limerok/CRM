<?php
class ControllerCatalogProduct extends Controller
{
    public function index()
    {
        require_login();
        $filters = array(
            'filter_name' => isset($_GET['filter_name']) ? trim($_GET['filter_name']) : '',
            'filter_model' => isset($_GET['filter_model']) ? trim($_GET['filter_model']) : '',
            'filter_series' => isset($_GET['filter_series']) ? trim($_GET['filter_series']) : '',
            'filter_manufacturer_id' => isset($_GET['filter_manufacturer_id']) ? (int)$_GET['filter_manufacturer_id'] : 0,
            'filter_category_id' => isset($_GET['filter_category_id']) ? (int)$_GET['filter_category_id'] : 0,
        );

        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'sort_order';
        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';

        $sortMap = array(
            'name' => 'p.name',
            'model' => 'p.model',
            'series' => 'p.series',
            'manufacturer' => 'm.name',
            'category' => 'c.name',
            'sort_order' => 'p.sort_order',
        );

        if (!isset($sortMap[$sort])) {
            $sort = 'sort_order';
        }

        $orderBy = $sortMap[$sort] . ' ' . $order . ', p.name ASC';

        $sql = 'SELECT p.*, m.name AS manufacturer_name, c.name AS category_name FROM products p
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
            LEFT JOIN categories c ON p.category_id = c.id';
        $conditions = array();
        $params = array();

        if ($filters['filter_name'] !== '') {
            $conditions[] = 'p.name LIKE :filter_name';
            $params['filter_name'] = '%' . $filters['filter_name'] . '%';
        }

        if ($filters['filter_model'] !== '') {
            $conditions[] = 'p.model LIKE :filter_model';
            $params['filter_model'] = '%' . $filters['filter_model'] . '%';
        }

        if ($filters['filter_series'] !== '') {
            $conditions[] = 'p.series LIKE :filter_series';
            $params['filter_series'] = '%' . $filters['filter_series'] . '%';
        }

        if ($filters['filter_manufacturer_id']) {
            $conditions[] = 'p.manufacturer_id = :filter_manufacturer_id';
            $params['filter_manufacturer_id'] = $filters['filter_manufacturer_id'];
        }

        if ($filters['filter_category_id']) {
            $conditions[] = 'p.category_id = :filter_category_id';
            $params['filter_category_id'] = $filters['filter_category_id'];
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ' . $orderBy;

        $products = $this->db->fetchAll($sql, $params);

        $manufacturers = $this->db->fetchAll('SELECT id, name FROM manufacturers ORDER BY name ASC');
        $categories = $this->db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');

        $nameSuggestions = $this->db->fetchAll('SELECT DISTINCT name FROM products ORDER BY name ASC LIMIT 20');
        $modelSuggestions = $this->db->fetchAll('SELECT DISTINCT model FROM products ORDER BY model ASC LIMIT 20');
        $seriesSuggestions = $this->db->fetchAll("SELECT DISTINCT series FROM products WHERE series IS NOT NULL AND series != '' ORDER BY series ASC LIMIT 20");

        $urlFilters = array();
        foreach ($filters as $key => $value) {
            if (in_array($key, array('filter_manufacturer_id', 'filter_category_id'), true)) {
                if ($value) {
                    $urlFilters[$key] = $value;
                }
            } elseif ($value !== '') {
                $urlFilters[$key] = $value;
            }
        }

        $this->render('catalog/product_list', array(
            'products' => $products,
            'filters' => $filters,
            'manufacturers' => $manufacturers,
            'categories' => $categories,
            'name_suggestions' => $nameSuggestions,
            'model_suggestions' => $modelSuggestions,
            'series_suggestions' => $seriesSuggestions,
            'sort' => $sort,
            'order' => $order,
            'url_filters' => $urlFilters,
        ));
    }

    public function form()
    {
        require_login();
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $product = null;
        if ($id) {
            $product = $this->db->fetch('SELECT * FROM products WHERE id = :id', array('id' => $id));
        }

        $manufacturers = $this->db->fetchAll('SELECT id, name FROM manufacturers ORDER BY sort_order ASC, name ASC');
        $categories = $this->db->fetchAll('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC');
        $currencies = $this->db->fetchAll('SELECT id, name, code FROM currencies ORDER BY name ASC');
        $weightClasses = $this->db->fetchAll('SELECT id, name, code FROM weight_classes ORDER BY sort_order ASC, name ASC');
        $lengthClasses = $this->db->fetchAll('SELECT id, name, code FROM length_classes ORDER BY sort_order ASC, name ASC');

        $currencyCodes = array();
        foreach ($currencies as $currencyItem) {
            $currencyCodes[] = $currencyItem['code'];
        }

        $weightCodes = array();
        foreach ($weightClasses as $weightItem) {
            $weightCodes[] = $weightItem['code'];
        }

        $lengthCodes = array();
        foreach ($lengthClasses as $lengthItem) {
            $lengthCodes[] = $lengthItem['code'];
        }

        $defaultCurrencyRow = $this->db->fetch('SELECT code FROM currencies WHERE id = :id', array('id' => (int)get_setting('config_currency_id', 0)));
        $defaultWeightRow = $this->db->fetch('SELECT code FROM weight_classes WHERE id = :id', array('id' => (int)get_setting('config_weight_class_id', 0)));
        $defaultLengthRow = $this->db->fetch('SELECT code FROM length_classes WHERE id = :id', array('id' => (int)get_setting('config_length_class_id', 0)));

        $defaultCurrencyCode = $defaultCurrencyRow ? $defaultCurrencyRow['code'] : (!empty($currencyCodes) ? $currencyCodes[0] : 'RUB');
        $defaultWeightCode = $defaultWeightRow ? $defaultWeightRow['code'] : (!empty($weightCodes) ? $weightCodes[0] : 'kg');
        $defaultLengthCode = $defaultLengthRow ? $defaultLengthRow['code'] : (!empty($lengthCodes) ? $lengthCodes[0] : 'mm');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'name' => trim(isset($_POST['name']) ? $_POST['name'] : ''),
                'model' => strtoupper(trim(isset($_POST['model']) ? $_POST['model'] : '')),
                'series' => strtoupper(trim(isset($_POST['series']) ? $_POST['series'] : '')),
                'manufacturer_id' => !empty($_POST['manufacturer_id']) ? $_POST['manufacturer_id'] : null,
                'purchase_price' => isset($_POST['purchase_price']) && $_POST['purchase_price'] !== '' ? $_POST['purchase_price'] : 0,
                'purchase_currency' => isset($_POST['purchase_currency']) ? strtoupper($_POST['purchase_currency']) : $defaultCurrencyCode,
                'weight' => isset($_POST['weight']) && $_POST['weight'] !== '' ? $_POST['weight'] : 0,
                'weight_unit' => isset($_POST['weight_unit']) ? $_POST['weight_unit'] : $defaultWeightCode,
                'weight_package' => isset($_POST['weight_package']) && $_POST['weight_package'] !== '' ? $_POST['weight_package'] : 0,
                'length' => isset($_POST['length']) && $_POST['length'] !== '' ? $_POST['length'] : 0,
                'width' => isset($_POST['width']) && $_POST['width'] !== '' ? $_POST['width'] : 0,
                'height' => isset($_POST['height']) && $_POST['height'] !== '' ? $_POST['height'] : 0,
                'length_package' => isset($_POST['length_package']) && $_POST['length_package'] !== '' ? $_POST['length_package'] : 0,
                'width_package' => isset($_POST['width_package']) && $_POST['width_package'] !== '' ? $_POST['width_package'] : 0,
                'height_package' => isset($_POST['height_package']) && $_POST['height_package'] !== '' ? $_POST['height_package'] : 0,
                'length_unit' => isset($_POST['length_unit']) ? $_POST['length_unit'] : $defaultLengthCode,
                'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
                'sort_order' => (isset($_POST['sort_order']) && $_POST['sort_order'] !== '') ? (int)$_POST['sort_order'] : null,
            );

            if (!in_array($data['purchase_currency'], $currencyCodes, true)) {
                $data['purchase_currency'] = $defaultCurrencyCode;
            }

            if (!in_array($data['weight_unit'], $weightCodes, true)) {
                $data['weight_unit'] = $defaultWeightCode;
            }

            if (!in_array($data['length_unit'], $lengthCodes, true)) {
                $data['length_unit'] = $defaultLengthCode;
            }

            if ($data['sort_order'] === null) {
                $maxSort = $this->db->fetch('SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM products');
                $currentMax = isset($maxSort['max_sort']) ? $maxSort['max_sort'] : 0;
                $data['sort_order'] = ((int)$currentMax) + 1;
            }

            if ($id) {
                $updateData = array_merge($data, array('id' => $id));
                $this->db->query('UPDATE products SET name = :name, model = :model, series = :series, manufacturer_id = :manufacturer_id,
                    purchase_price = :purchase_price, purchase_currency = :purchase_currency, weight = :weight, weight_unit = :weight_unit,
                    weight_package = :weight_package, length = :length, width = :width, height = :height,
                    length_package = :length_package, width_package = :width_package, height_package = :height_package,
                    length_unit = :length_unit, category_id = :category_id, sort_order = :sort_order WHERE id = :id',
                    $updateData
                );
            } else {
                $this->db->query('INSERT INTO products (name, model, series, manufacturer_id, purchase_price, purchase_currency,
                    weight, weight_unit, weight_package, length, width, height, length_package, width_package, height_package,
                    length_unit, category_id, sort_order) VALUES
                    (:name, :model, :series, :manufacturer_id, :purchase_price, :purchase_currency, :weight, :weight_unit,
                    :weight_package, :length, :width, :height, :length_package, :width_package, :height_package, :length_unit,
                    :category_id, :sort_order)',
                    $data
                );
            }

            redirect(admin_url('catalog/product'));
        }

        $this->render('catalog/product_form', array(
            'product' => $product,
            'manufacturers' => $manufacturers,
            'categories' => $categories,
            'currencies' => $currencies,
            'weight_classes' => $weightClasses,
            'length_classes' => $lengthClasses,
            'default_currency_code' => $defaultCurrencyCode,
            'default_weight_code' => $defaultWeightCode,
            'default_length_code' => $defaultLengthCode,
        ));
    }

    public function delete()
    {
        require_login();
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $this->db->query('DELETE FROM products WHERE id = :id', array('id' => $id));
        }
        redirect(admin_url('catalog/product'));
    }
}
