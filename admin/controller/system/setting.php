<?php
class ControllerSystemSetting extends Controller
{
    public function index()
    {
        require_login();

        $errors = array();
        $success = !empty($_GET['success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currencyId = isset($_POST['config_currency_id']) ? (int)$_POST['config_currency_id'] : 0;
            $lengthClassId = isset($_POST['config_length_class_id']) ? (int)$_POST['config_length_class_id'] : 0;
            $weightClassId = isset($_POST['config_weight_class_id']) ? (int)$_POST['config_weight_class_id'] : 0;

            $currency = $currencyId ? $this->db->fetch('SELECT id FROM currencies WHERE id = :id', array('id' => $currencyId)) : null;
            $lengthClass = $lengthClassId ? $this->db->fetch('SELECT id FROM length_classes WHERE id = :id', array('id' => $lengthClassId)) : null;
            $weightClass = $weightClassId ? $this->db->fetch('SELECT id FROM weight_classes WHERE id = :id', array('id' => $weightClassId)) : null;

            if (!$currency) {
                $errors[] = 'Выберите валюту магазина.';
            }

            if (!$lengthClass) {
                $errors[] = 'Выберите единицу длины магазина.';
            }

            if (!$weightClass) {
                $errors[] = 'Выберите единицу веса магазина.';
            }

            if (empty($errors)) {
                set_setting('config_currency_id', $currencyId);
                set_setting('config_length_class_id', $lengthClassId);
                set_setting('config_weight_class_id', $weightClassId);

                $this->db->query('UPDATE currencies SET value = 1 WHERE id = :id', array('id' => $currencyId));

                redirect(admin_url('system/setting', array('success' => 1)));
            }
        }

        $currencies = $this->db->fetchAll('SELECT id, name, code FROM currencies ORDER BY name ASC');
        $lengthClasses = $this->db->fetchAll('SELECT id, name, code FROM length_classes ORDER BY sort_order ASC, name ASC');
        $weightClasses = $this->db->fetchAll('SELECT id, name, code FROM weight_classes ORDER BY sort_order ASC, name ASC');

        $this->render('system/setting', array(
            'currencies' => $currencies,
            'length_classes' => $lengthClasses,
            'weight_classes' => $weightClasses,
            'config_currency_id' => (int)get_setting('config_currency_id', 0),
            'config_length_class_id' => (int)get_setting('config_length_class_id', 0),
            'config_weight_class_id' => (int)get_setting('config_weight_class_id', 0),
            'errors' => $errors,
            'success' => $success,
        ));
    }
}
