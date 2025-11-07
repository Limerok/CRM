<?php
require_once __DIR__ . '/../system/startup.php';

$route = isset($_GET['route']) ? $_GET['route'] : 'common/login';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if (!is_logged() && $route !== 'common/login') {
    redirect(admin_url('common/login'));
}

$controller_file = __DIR__ . '/controller/' . $route . '.php';
if (!is_file($controller_file)) {
    http_response_code(404);
    echo 'Controller not found';
    exit;
}

require_once $controller_file;

$class = 'Controller' . str_replace(' ', '', ucwords(str_replace('/', ' ', $route)));

if (!class_exists($class)) {
    http_response_code(500);
    echo 'Controller class not found';
    exit;
}

$controller = new $class($db);

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'Action not found';
    exit;
}

$controller->{$action}();
