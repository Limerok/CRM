<?php
class ControllerSalesCommission extends Controller
{
    public function index()
    {
        require_login();

        $sources = $this->db->fetchAll('SELECT id, name FROM order_sources ORDER BY name ASC');
        $categories = $this->db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');

        $success = isset($_GET['success']) ? $_GET['success'] : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sources && $categories) {
            $input = isset($_POST['commissions']) && is_array($_POST['commissions']) ? $_POST['commissions'] : array();

            foreach ($categories as $category) {
                $categoryId = (int)$category['id'];
                foreach ($sources as $source) {
                    $sourceId = (int)$source['id'];

                    $rawValue = '';
                    if (isset($input[$categoryId]) && is_array($input[$categoryId]) && array_key_exists($sourceId, $input[$categoryId])) {
                        $rawValue = $input[$categoryId][$sourceId];
                    }

                    $rawValue = is_string($rawValue) ? trim($rawValue) : '';

                    if ($rawValue === '') {
                        $this->db->query('DELETE FROM category_source_commissions WHERE category_id = :category_id AND source_id = :source_id', array(
                            'category_id' => $categoryId,
                            'source_id' => $sourceId,
                        ));
                        continue;
                    }

                    $value = (float)str_replace(',', '.', $rawValue);
                    if ($value < 0) {
                        $value = 0;
                    }

                    $existing = $this->db->fetch('SELECT id FROM category_source_commissions WHERE category_id = :category_id AND source_id = :source_id', array(
                        'category_id' => $categoryId,
                        'source_id' => $sourceId,
                    ));

                    if ($existing) {
                        $this->db->query('UPDATE category_source_commissions SET commission_percent = :percent WHERE id = :id', array(
                            'percent' => $value,
                            'id' => $existing['id'],
                        ));
                    } else {
                        $this->db->query('INSERT INTO category_source_commissions (category_id, source_id, commission_percent) VALUES (:category_id, :source_id, :percent)', array(
                            'category_id' => $categoryId,
                            'source_id' => $sourceId,
                            'percent' => $value,
                        ));
                    }
                }
            }

            redirect(admin_url('sales/commission', array('success' => 1)));
        }

        $commissionRows = $this->db->fetchAll('SELECT category_id, source_id, commission_percent FROM category_source_commissions');
        $commissionMap = array();
        foreach ($commissionRows as $row) {
            $categoryId = (int)$row['category_id'];
            $sourceId = (int)$row['source_id'];
            if (!isset($commissionMap[$categoryId])) {
                $commissionMap[$categoryId] = array();
            }
            $commissionMap[$categoryId][$sourceId] = (float)$row['commission_percent'];
        }

        $this->render('sales/commission', array(
            'sources' => $sources,
            'categories' => $categories,
            'commission_map' => $commissionMap,
            'success' => $success,
        ));
    }
}
