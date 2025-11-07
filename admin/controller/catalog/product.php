<?php
class ControllerCatalogProduct extends Controller
{
    public function index()
    {
        require_login();
        $products = $this->db->fetchAll('SELECT p.*, m.name as manufacturer_name, c.name as category_name FROM products p
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.sort_order ASC, p.name ASC');

        $this->render('catalog/product_list', array(
            'products' => $products,
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'name' => trim(isset($_POST['name']) ? $_POST['name'] : ''),
                'model' => strtoupper(trim(isset($_POST['model']) ? $_POST['model'] : '')),
                'series' => strtoupper(trim(isset($_POST['series']) ? $_POST['series'] : '')),
                'manufacturer_id' => !empty($_POST['manufacturer_id']) ? $_POST['manufacturer_id'] : null,
                'purchase_price' => isset($_POST['purchase_price']) && $_POST['purchase_price'] !== '' ? $_POST['purchase_price'] : 0,
                'purchase_currency' => isset($_POST['purchase_currency']) ? $_POST['purchase_currency'] : 'RUB',
                'weight' => isset($_POST['weight']) && $_POST['weight'] !== '' ? $_POST['weight'] : 0,
                'weight_unit' => isset($_POST['weight_unit']) ? $_POST['weight_unit'] : 'kg',
                'weight_package' => isset($_POST['weight_package']) && $_POST['weight_package'] !== '' ? $_POST['weight_package'] : 0,
                'length' => isset($_POST['length']) && $_POST['length'] !== '' ? $_POST['length'] : 0,
                'width' => isset($_POST['width']) && $_POST['width'] !== '' ? $_POST['width'] : 0,
                'height' => isset($_POST['height']) && $_POST['height'] !== '' ? $_POST['height'] : 0,
                'length_package' => isset($_POST['length_package']) && $_POST['length_package'] !== '' ? $_POST['length_package'] : 0,
                'width_package' => isset($_POST['width_package']) && $_POST['width_package'] !== '' ? $_POST['width_package'] : 0,
                'height_package' => isset($_POST['height_package']) && $_POST['height_package'] !== '' ? $_POST['height_package'] : 0,
                'length_unit' => isset($_POST['length_unit']) ? $_POST['length_unit'] : 'mm',
                'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
                'sort_order' => (isset($_POST['sort_order']) && $_POST['sort_order'] !== '') ? (int)$_POST['sort_order'] : null,
            );

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

        $manufacturers = $this->db->fetchAll('SELECT id, name FROM manufacturers ORDER BY sort_order ASC, name ASC');
        $categories = $this->db->fetchAll('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC');

        $this->render('catalog/product_form', array(
            'product' => $product,
            'manufacturers' => $manufacturers,
            'categories' => $categories,
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
