<?php
class ControllerSystemSetting extends Controller
{
    public function index()
    {
        require_login();

        $errors = array();
        $success = isset($_GET['success']) ? $_GET['success'] : null;
        $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'localization';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = isset($_POST['form']) ? $_POST['form'] : 'localization';

            if ($form === 'statuses') {
                $statusId = isset($_POST['default_order_status_id']) ? (int)$_POST['default_order_status_id'] : 0;

                if ($statusId > 0) {
                    $status = $this->db->fetch('SELECT id FROM order_statuses WHERE id = :id', array('id' => $statusId));
                    if (!$status) {
                        $errors[] = 'Выберите существующий статус.';
                    }
                }

                if (empty($errors)) {
                    set_setting('config_default_order_status_id', $statusId);
                    redirect(admin_url('system/setting', array('success' => 1, 'tab' => 'statuses')));
                }

                $activeTab = 'statuses';
            } else {
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

                    redirect(admin_url('system/setting', array('success' => 1, 'tab' => 'localization')));
                }

                $activeTab = 'localization';
            }
        }

        $currencies = $this->db->fetchAll('SELECT id, name, code FROM currencies ORDER BY name ASC');
        $lengthClasses = $this->db->fetchAll('SELECT id, name, code FROM length_classes ORDER BY sort_order ASC, name ASC');
        $weightClasses = $this->db->fetchAll('SELECT id, name, code FROM weight_classes ORDER BY sort_order ASC, name ASC');
        $orderStatuses = $this->db->fetchAll('SELECT id, name FROM order_statuses ORDER BY name ASC');

        if ($activeTab !== 'statuses') {
            $activeTab = 'localization';
        }

        $this->render('system/setting', array(
            'currencies' => $currencies,
            'length_classes' => $lengthClasses,
            'weight_classes' => $weightClasses,
            'order_statuses' => $orderStatuses,
            'config_currency_id' => (int)get_setting('config_currency_id', 0),
            'config_length_class_id' => (int)get_setting('config_length_class_id', 0),
            'config_weight_class_id' => (int)get_setting('config_weight_class_id', 0),
            'default_order_status_id' => (int)get_setting('config_default_order_status_id', 0),
            'errors' => $errors,
            'success' => $success,
            'active_tab' => $activeTab,
        ));
    }
}
