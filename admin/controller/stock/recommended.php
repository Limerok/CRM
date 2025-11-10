<?php
class ControllerStockRecommended extends Controller
{
    public function index()
    {
        require_login();

        $errors = array();
        $success = isset($_GET['success']) ? (int)$_GET['success'] : 0;

        $submittedRecommendations = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recommendations = isset($_POST['recommended']) && is_array($_POST['recommended']) ? $_POST['recommended'] : array();
            $submittedRecommendations = $recommendations;
            $productsList = $this->db->fetchAll('SELECT id, name FROM products');
            $productsIndex = array();

            foreach ($productsList as $product) {
                $productId = isset($product['id']) ? (int)$product['id'] : 0;
                if ($productId > 0) {
                    $productsIndex[$productId] = $product['name'];
                }
            }

            foreach ($recommendations as $productId => $value) {
                $productId = (int)$productId;
                if ($productId <= 0 || !isset($productsIndex[$productId])) {
                    continue;
                }

                $rawValue = trim((string)$value);

                if ($rawValue === '') {
                    $this->db->query('DELETE FROM product_recommended_stock WHERE product_id = :product_id', array('product_id' => $productId));
                    continue;
                }

                if (filter_var($rawValue, FILTER_VALIDATE_INT) === false) {
                    $errors[] = sprintf('Введите целое число для товара "%s".', $productsIndex[$productId]);
                    continue;
                }

                $quantity = (int)$rawValue;
                if ($quantity < 0) {
                    $quantity = 0;
                }

                $this->db->query(
                    'INSERT INTO product_recommended_stock (product_id, recommended_quantity)
                     VALUES (:product_id, :quantity)
                     ON DUPLICATE KEY UPDATE recommended_quantity = VALUES(recommended_quantity), updated_at = CURRENT_TIMESTAMP',
                    array(
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    )
                );
            }

            if (empty($errors)) {
                redirect(admin_url('stock/recommended', array('success' => 1)));
            }

            $success = 0;
        }

        $products = $this->db->fetchAll(
            'SELECT p.id, p.name, p.model, p.series,
                    COALESCE(s.quantity, 0) AS quantity,
                    prs.recommended_quantity
             FROM products p
             LEFT JOIN stock_items s ON s.product_id = p.id
             LEFT JOIN product_recommended_stock prs ON prs.product_id = p.id
             ORDER BY p.name ASC, p.id ASC'
        );

        if (!empty($errors) && !empty($submittedRecommendations)) {
            foreach ($products as &$product) {
                $productId = isset($product['id']) ? (int)$product['id'] : 0;
                if ($productId <= 0 || !array_key_exists($productId, $submittedRecommendations)) {
                    continue;
                }

                $rawValue = trim((string)$submittedRecommendations[$productId]);
                if ($rawValue === '') {
                    $product['recommended_quantity'] = null;
                } else {
                    $product['recommended_quantity'] = $rawValue;
                }
            }
            unset($product);
        }

        $this->render('stock/recommended', array(
            'products' => $products,
            'errors' => $errors,
            'success' => $success,
        ));
    }
}
