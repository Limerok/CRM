<?php
class ControllerSystemSource extends Controller
{
    public function index()
    {
        require_login();

        $sources = $this->db->fetchAll('SELECT id, name, created_at, updated_at FROM order_sources ORDER BY name ASC');

        $this->render('system/source_list', array(
            'sources' => $sources,
            'success' => isset($_GET['success']) ? $_GET['success'] : null,
            'error' => isset($_GET['error']) ? $_GET['error'] : null,
        ));
    }

    public function form()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $source = null;

        if ($id) {
            $source = $this->db->fetch('SELECT * FROM order_sources WHERE id = :id', array('id' => $id));
            if (!$source) {
                redirect(admin_url('system/source'));
            }
        }

        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';

            if ($name === '') {
                $errors[] = 'Введите название источника.';
            } else {
                $params = array('name' => $name);
                if ($id) {
                    $params['id'] = $id;
                    $exists = $this->db->fetch('SELECT id FROM order_sources WHERE name = :name AND id != :id', $params);
                } else {
                    $exists = $this->db->fetch('SELECT id FROM order_sources WHERE name = :name', $params);
                }

                if ($exists) {
                    $errors[] = 'Источник с таким названием уже существует.';
                }
            }

            if (empty($errors)) {
                if ($id) {
                    $this->db->query('UPDATE order_sources SET name = :name WHERE id = :id', array(
                        'name' => $name,
                        'id' => $id,
                    ));
                    $message = 'Источник обновлён.';
                } else {
                    $this->db->query('INSERT INTO order_sources (name) VALUES (:name)', array('name' => $name));
                    $id = (int)$this->db->lastInsertId();
                    $message = 'Источник добавлен.';
                }

                redirect(admin_url('system/source', array('success' => $message)));
            }

            $source = array(
                'id' => $id,
                'name' => $name,
            );
        }

        $this->render('system/source_form', array(
            'source' => $source,
            'errors' => $errors,
        ));
    }

    public function delete()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->db->query('DELETE FROM order_sources WHERE id = :id', array('id' => $id));
            redirect(admin_url('system/source', array('success' => 'Источник удалён.')));
        }

        redirect(admin_url('system/source'));
    }
}
