<?php
class ControllerCatalogManufacturer extends Controller
{
    public function index()
    {
        require_login();
        $manufacturers = $this->db->fetchAll('SELECT * FROM manufacturers ORDER BY sort_order ASC, name ASC');

        $this->render('catalog/manufacturer_list', array(
            'manufacturers' => $manufacturers,
        ));
    }

    public function form()
    {
        require_login();
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $manufacturer = null;
        if ($id) {
            $manufacturer = $this->db->fetch('SELECT * FROM manufacturers WHERE id = :id', array('id' => $id));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = strtoupper(trim(isset($_POST['name']) ? $_POST['name'] : ''));
            $sort_order = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : null;

            if ($sort_order === null) {
                $maxSort = $this->db->fetch('SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM manufacturers');
                $currentMax = isset($maxSort['max_sort']) ? $maxSort['max_sort'] : 0;
                $sort_order = ((int)$currentMax) + 1;
            }

            $params = array(
                'name' => $name,
                'sort_order' => $sort_order,
            );

            if ($id) {
                $params['id'] = $id;
                $this->db->query('UPDATE manufacturers SET name = :name, sort_order = :sort_order WHERE id = :id',
                    $params
                );
            } else {
                $this->db->query('INSERT INTO manufacturers (name, sort_order) VALUES (:name, :sort_order)', $params);
            }

            redirect(admin_url('catalog/manufacturer'));
        }

        $this->render('catalog/manufacturer_form', array(
            'manufacturer' => $manufacturer,
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
                    'DELETE FROM manufacturers WHERE id IN (' . implode(', ', $placeholders) . ')',
                    $params
                );
            }
        } else {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id) {
                $this->db->query('DELETE FROM manufacturers WHERE id = :id', array('id' => $id));
            }
        }

        redirect(admin_url('catalog/manufacturer'));
    }
}
