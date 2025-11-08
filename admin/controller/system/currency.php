<?php
class ControllerSystemCurrency extends Controller
{
    public function index()
    {
        require_login();

        $currencies = $this->db->fetchAll('SELECT id, name, code, value, date_modified FROM currencies ORDER BY name ASC');
        $defaultCurrencyId = (int)get_setting('config_currency_id', 0);

        $this->render('system/currency_list', array(
            'currencies' => $currencies,
            'default_currency_id' => $defaultCurrencyId,
        ));
    }

    public function form()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $currency = null;
        if ($id) {
            $currency = $this->db->fetch('SELECT * FROM currencies WHERE id = :id', array('id' => $id));
            if (!$currency) {
                redirect(admin_url('system/currency'));
            }
        }

        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
            $value = isset($_POST['value']) && $_POST['value'] !== '' ? (float)$_POST['value'] : 1;

            if ($name === '') {
                $errors[] = 'Введите название валюты.';
            }

            if ($code === '' || strlen($code) > 3) {
                $errors[] = 'Введите корректный код валюты (до 3 символов).';
            }

            if ($value <= 0) {
                $errors[] = 'Значение курса должно быть больше нуля.';
            }

            if ($code !== '') {
                $params = array('code' => $code);
                if ($id) {
                    $params['id'] = $id;
                    $existing = $this->db->fetch('SELECT id FROM currencies WHERE code = :code AND id != :id', $params);
                } else {
                    $existing = $this->db->fetch('SELECT id FROM currencies WHERE code = :code', $params);
                }
                if ($existing) {
                    $errors[] = 'Валюта с таким кодом уже существует.';
                }
            }

            if (empty($errors)) {
                if ($id) {
                    $this->db->query('UPDATE currencies SET name = :name, code = :code, value = :value WHERE id = :id', array(
                        'name' => $name,
                        'code' => $code,
                        'value' => $value,
                        'id' => $id,
                    ));
                    $currencyId = $id;
                } else {
                    $this->db->query('INSERT INTO currencies (name, code, value) VALUES (:name, :code, :value)', array(
                        'name' => $name,
                        'code' => $code,
                        'value' => $value,
                    ));
                    $currencyId = (int)$this->db->lastInsertId();
                }

                $defaultCurrencyId = (int)get_setting('config_currency_id', 0);
                if ($currencyId === $defaultCurrencyId) {
                    $this->db->query('UPDATE currencies SET value = 1 WHERE id = :id', array('id' => $currencyId));
                }

                redirect(admin_url('system/currency'));
            }

            $currency = array(
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'value' => $value,
            );
        }

        $this->render('system/currency_form', array(
            'currency' => $currency,
            'errors' => $errors,
        ));
    }

    public function delete()
    {
        require_login();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $defaultCurrencyId = (int)get_setting('config_currency_id', 0);
            if ($id !== $defaultCurrencyId) {
                $this->db->query('DELETE FROM currencies WHERE id = :id', array('id' => $id));
            }
        }

        redirect(admin_url('system/currency'));
    }
}
