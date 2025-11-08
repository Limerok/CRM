<?php
class ControllerSystemWeight extends Controller
{
    public function index()
    {
        require_login();

        $weightClasses = $this->db->fetchAll('SELECT id, name, code, value, sort_order, date_modified FROM weight_classes ORDER BY sort_order ASC, name ASC');
        $defaultWeightId = (int)get_setting('config_weight_class_id', 0);

        $this->render('system/weight_list', array(
            'weight_classes' => $weightClasses,
            'default_weight_class_id' => $defaultWeightId,
        ));
    }

    public function form()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $weightClass = null;
        if ($id) {
            $weightClass = $this->db->fetch('SELECT * FROM weight_classes WHERE id = :id', array('id' => $id));
            if (!$weightClass) {
                redirect(admin_url('system/weight'));
            }
        }

        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $code = isset($_POST['code']) ? trim($_POST['code']) : '';
            $value = isset($_POST['value']) && $_POST['value'] !== '' ? (float)$_POST['value'] : 1;
            $sortOrder = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : 0;

            if ($name === '') {
                $errors[] = 'Введите название единицы веса.';
            }

            if ($code === '') {
                $errors[] = 'Введите обозначение единицы веса.';
            }

            if ($value <= 0) {
                $errors[] = 'Значение должно быть больше нуля.';
            }

            if ($code !== '') {
                $params = array('code' => $code);
                if ($id) {
                    $params['id'] = $id;
                    $existing = $this->db->fetch('SELECT id FROM weight_classes WHERE code = :code AND id != :id', $params);
                } else {
                    $existing = $this->db->fetch('SELECT id FROM weight_classes WHERE code = :code', $params);
                }
                if ($existing) {
                    $errors[] = 'Единица веса с таким обозначением уже существует.';
                }
            }

            if (empty($errors)) {
                if ($id) {
                    $this->db->query('UPDATE weight_classes SET name = :name, code = :code, value = :value, sort_order = :sort_order WHERE id = :id', array(
                        'name' => $name,
                        'code' => $code,
                        'value' => $value,
                        'sort_order' => $sortOrder,
                        'id' => $id,
                    ));
                } else {
                    $this->db->query('INSERT INTO weight_classes (name, code, value, sort_order) VALUES (:name, :code, :value, :sort_order)', array(
                        'name' => $name,
                        'code' => $code,
                        'value' => $value,
                        'sort_order' => $sortOrder,
                    ));
                }

                redirect(admin_url('system/weight'));
            }

            $weightClass = array(
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'value' => $value,
                'sort_order' => $sortOrder,
            );
        }

        $this->render('system/weight_form', array(
            'weight_class' => $weightClass,
            'errors' => $errors,
        ));
    }

    public function delete()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $defaultWeightId = (int)get_setting('config_weight_class_id', 0);
            if ($id !== $defaultWeightId) {
                $this->db->query('DELETE FROM weight_classes WHERE id = :id', array('id' => $id));
            }
        }

        redirect(admin_url('system/weight'));
    }
}
