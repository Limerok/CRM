<?php
class ControllerCatalogCategory extends Controller
{
    public function index()
    {
        require_login();
        $categories = $this->db->fetchAll('SELECT c.*, parent.name AS parent_name FROM categories c
            LEFT JOIN categories parent ON c.parent_id = parent.id
            ORDER BY c.sort_order ASC, c.name ASC');

        $this->render('catalog/category_list', array(
            'categories' => $categories,
        ));
    }

    public function form()
    {
        require_login();
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $category = null;
        if ($id) {
            $category = $this->db->fetch('SELECT * FROM categories WHERE id = :id', array('id' => $id));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
            $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $sort_order = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : null;

            if ($sort_order === null) {
                $maxSort = $this->db->fetch('SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM categories');
                $currentMax = isset($maxSort['max_sort']) ? $maxSort['max_sort'] : 0;
                $sort_order = ((int)$currentMax) + 1;
            }

            $params = array(
                'name' => $name,
                'parent_id' => $parent_id,
                'sort_order' => $sort_order,
            );

            if ($id) {
                $params['id'] = $id;
                $this->db->query('UPDATE categories SET name = :name, parent_id = :parent_id, sort_order = :sort_order WHERE id = :id',
                    $params
                );
            } else {
                $this->db->query('INSERT INTO categories (name, parent_id, sort_order) VALUES (:name, :parent_id, :sort_order)', $params);
            }

            redirect(admin_url('catalog/category'));
        }

        $categories = $this->db->fetchAll('SELECT id, name FROM categories WHERE id != :id OR :id IS NULL ORDER BY name ASC', array(
            'id' => $id,
        ));

        $this->render('catalog/category_form', array(
            'category' => $category,
            'categories' => $categories,
        ));
    }

    public function delete()
    {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selected = isset($_POST['selected']) ? (array)$_POST['selected'] : array();
            $ids = array();

            foreach ($selected as $value) {
                $id = (int)$value;
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }

            if ($ids) {
                $placeholders = array();
                $params = array();
                $index = 0;

                foreach ($ids as $id) {
                    $placeholder = ':id' . $index;
                    $placeholders[] = $placeholder;
                    $params['id' . $index] = $id;
                    $index++;
                }

                $this->db->query(
                    'DELETE FROM categories WHERE id IN (' . implode(', ', $placeholders) . ')',
                    $params
                );
            }
        } else {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id) {
                $this->db->query('DELETE FROM categories WHERE id = :id', array('id' => $id));
            }
        }

        redirect(admin_url('catalog/category'));
    }
}
