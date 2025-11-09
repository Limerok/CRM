<?php
class ControllerSystemStatus extends Controller
{
    public function index()
    {
        require_login();

        $statuses = $this->db->fetchAll('SELECT id, name, created_at, updated_at FROM order_statuses ORDER BY name ASC');

        $this->render('system/status_list', array(
            'statuses' => $statuses,
            'success' => isset($_GET['success']) ? $_GET['success'] : null,
            'error' => isset($_GET['error']) ? $_GET['error'] : null,
        ));
    }

    public function form()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $status = null;

        if ($id) {
            $status = $this->db->fetch('SELECT * FROM order_statuses WHERE id = :id', array('id' => $id));
            if (!$status) {
                redirect(admin_url('system/status'));
            }
        }

        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';

            if ($name === '') {
                $errors[] = 'Введите название статуса.';
            } else {
                $params = array('name' => $name);
                if ($id) {
                    $params['id'] = $id;
                    $exists = $this->db->fetch('SELECT id FROM order_statuses WHERE name = :name AND id != :id', $params);
                } else {
                    $exists = $this->db->fetch('SELECT id FROM order_statuses WHERE name = :name', $params);
                }

                if ($exists) {
                    $errors[] = 'Статус с таким названием уже существует.';
                }
            }

            if (empty($errors)) {
                if ($id) {
                    $this->db->query('UPDATE order_statuses SET name = :name WHERE id = :id', array(
                        'name' => $name,
                        'id' => $id,
                    ));
                    $message = 'Статус обновлён.';
                } else {
                    $this->db->query('INSERT INTO order_statuses (name) VALUES (:name)', array('name' => $name));
                    $id = (int)$this->db->lastInsertId();
                    $message = 'Статус добавлен.';
                }

                redirect(admin_url('system/status', array('success' => $message)));
            }

            $status = array(
                'id' => $id,
                'name' => $name,
            );
        }

        $this->render('system/status_form', array(
            'status' => $status,
            'errors' => $errors,
        ));
    }

    public function delete()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->db->query('DELETE FROM order_statuses WHERE id = :id', array('id' => $id));

            $defaultStatusId = (int)get_setting('config_default_order_status_id', 0);
            if ($defaultStatusId === $id) {
                set_setting('config_default_order_status_id', 0);
            }

            redirect(admin_url('system/status', array('success' => 'Статус удалён.')));
        }

        redirect(admin_url('system/status'));
    }
}
