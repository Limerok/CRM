<?php
class ControllerSystemLength extends Controller
{
    public function index()
    {
        require_login();

        $lengthClasses = $this->db->fetchAll('SELECT id, name, code, value, sort_order, date_modified FROM length_classes ORDER BY sort_order ASC, name ASC');
        $defaultLengthId = (int)get_setting('config_length_class_id', 0);

        $this->render('system/length_list', array(
            'length_classes' => $lengthClasses,
            'default_length_class_id' => $defaultLengthId,
        ));
    }

    public function form()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $lengthClass = null;
        if ($id) {
            $lengthClass = $this->db->fetch('SELECT * FROM length_classes WHERE id = :id', array('id' => $id));
            if (!$lengthClass) {
                redirect(admin_url('system/length'));
            }
        }

        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $code = isset($_POST['code']) ? trim($_POST['code']) : '';
            $value = isset($_POST['value']) && $_POST['value'] !== '' ? (float)$_POST['value'] : 1;
            $sortOrder = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : 0;

            if ($name === '') {
                $errors[] = 'Введите название единицы длины.';
            }

            if ($code === '') {
                $errors[] = 'Введите обозначение единицы длины.';
            }

            if ($value <= 0) {
                $errors[] = 'Значение должно быть больше нуля.';
            }

            if ($code !== '') {
                $params = array('code' => $code);
                if ($id) {
                    $params['id'] = $id;
                    $existing = $this->db->fetch('SELECT id FROM length_classes WHERE code = :code AND id != :id', $params);
                } else {
                    $existing = $this->db->fetch('SELECT id FROM length_classes WHERE code = :code', $params);
                }
                if ($existing) {
                    $errors[] = 'Единица длины с таким обозначением уже существует.';
                }
            }

            if (empty($errors)) {
                if ($id) {
                    $this->db->query('UPDATE length_classes SET name = :name, code = :code, value = :value, sort_order = :sort_order WHERE id = :id', array(
                        'name' => $name,
                        'code' => $code,
                        'value' => $value,
                        'sort_order' => $sortOrder,
                        'id' => $id,
                    ));
                } else {
                    $this->db->query('INSERT INTO length_classes (name, code, value, sort_order) VALUES (:name, :code, :value, :sort_order)', array(
                        'name' => $name,
                        'code' => $code,
                        'value' => $value,
                        'sort_order' => $sortOrder,
                    ));
                }

                redirect(admin_url('system/length'));
            }

            $lengthClass = array(
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'value' => $value,
                'sort_order' => $sortOrder,
            );
        }

        $this->render('system/length_form', array(
            'length_class' => $lengthClass,
            'errors' => $errors,
        ));
    }

    public function delete()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $defaultLengthId = (int)get_setting('config_length_class_id', 0);
            if ($id !== $defaultLengthId) {
                $this->db->query('DELETE FROM length_classes WHERE id = :id', array('id' => $id));
            }
        }

        redirect(admin_url('system/length'));
    }
}
