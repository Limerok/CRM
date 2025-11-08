<?php
session_start();

$config = require __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/../';
    $class = str_replace('\\', '/', $class);

    $paths = array(
        'system/engine/' . $class . '.php',
        'system/library/' . $class . '.php',
        'system/helper/' . $class . '.php',
        'admin/controller/' . strtolower($class) . '.php',
        'admin/model/' . strtolower($class) . '.php',
    );

    foreach ($paths as $path) {
        $file = __DIR__ . '/../' . $path;
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/library/Database.php';
require_once __DIR__ . '/engine/Controller.php';
require_once __DIR__ . '/engine/Model.php';
require_once __DIR__ . '/library/Migrator.php';

$db = new Database($config['db']);

$migrator = new Migrator($db);
$migrator->migrate();

function view_path($template) {
    return __DIR__ . '/../admin/view/template/' . $template . '.php';
}

function admin_url($route = '', $params = array()) {
    global $config;
    $url = rtrim($config['app']['admin_url'], '/');
    if ($route) {
        $params['route'] = $route;
    }
    if (!empty($params)) {
        $url .= '/index.php?' . http_build_query($params);
    }
    return $url;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function is_logged() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged()) {
        redirect(admin_url('common/login'));
    }
}

function get_setting($key, $default = null) {
    global $db;
    $row = $db->fetch('SELECT value FROM settings WHERE `key` = :key', array('key' => $key));
    return $row ? $row['value'] : $default;
}

function set_setting($key, $value) {
    global $db;
    $exists = $db->fetch('SELECT `key` FROM settings WHERE `key` = :key', array('key' => $key));
    if ($exists) {
        $db->query('UPDATE settings SET value = :value WHERE `key` = :key', array(
            'value' => (string)$value,
            'key' => $key,
        ));
    } else {
        $db->query('INSERT INTO settings (`key`, `value`) VALUES (:key, :value)', array(
            'key' => $key,
            'value' => (string)$value,
        ));
    }
}

