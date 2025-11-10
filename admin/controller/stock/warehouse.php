<?php
class ControllerStockWarehouse extends Controller
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

        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';

        $sortMap = array(
            'name' => 'p.name',
            'model' => 'p.model',
            'series' => 'p.series',
            'manufacturer' => 'm.name',
            'category' => 'c.name',
            'quantity' => 's.quantity',
        );

        if (!isset($sortMap[$sort])) {
            $sort = 'name';
        }

        $sql = 'SELECT s.product_id, s.quantity, p.name, p.model, p.series, m.name AS manufacturer_name, c.name AS category_name,
                       prs.recommended_quantity
            FROM stock_items s
            LEFT JOIN products p ON s.product_id = p.id
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_recommended_stock prs ON prs.product_id = s.product_id';

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

        $sql .= ' ORDER BY ' . $sortMap[$sort] . ' ' . $order . ', p.name ASC';

        $items = $this->db->fetchAll($sql, $params);

        foreach ($items as &$item) {
            $recommendedQuantity = isset($item['recommended_quantity']) ? $item['recommended_quantity'] : null;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;

            if ($recommendedQuantity === null) {
                $item['recommended_to_deliver'] = null;
            } else {
                $recommendedQuantity = (int)$recommendedQuantity;
                $item['recommended_to_deliver'] = $recommendedQuantity > $quantity ? $recommendedQuantity - $quantity : 0;
            }
        }
        unset($item);

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

        $this->render('stock/warehouse_list', array(
            'items' => $items,
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
}
