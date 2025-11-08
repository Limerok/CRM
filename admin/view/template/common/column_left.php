<?php
$route = isset($_GET['route']) ? $_GET['route'] : 'common/dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$catalogRoutes = array('catalog/product', 'catalog/category', 'catalog/manufacturer');
$stockRoutes = array('stock/supply', 'stock/warehouse', 'stock/sale');
$systemRoutes = array('system/setting', 'system/currency', 'system/length', 'system/weight');
$systemLocalizationRoutes = array('system/currency', 'system/length', 'system/weight');

$catalogActive = in_array($route, $catalogRoutes, true);
$stockActive = in_array($route, $stockRoutes, true);
$systemActive = in_array($route, $systemRoutes, true);
$systemLocalizationActive = in_array($route, $systemLocalizationRoutes, true);
?>
        <aside class="col-md-3 col-lg-2 bg-light sidebar py-4">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link<?= $route === 'common/dashboard' ? ' active' : ''; ?>" href="<?= admin_url('common/dashboard'); ?>"><i class="bi bi-speedometer"></i> Дашборд</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $catalogActive ? ' active' : ''; ?>" href="#catalogMenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= $catalogActive ? 'true' : 'false'; ?>" aria-controls="catalogMenu"><i class="bi bi-box"></i> Каталог</a>
                    <div class="collapse<?= $catalogActive ? ' show' : ''; ?>" id="catalogMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link<?= ($route === 'catalog/product') ? ' active' : ''; ?>" href="<?= admin_url('catalog/product'); ?>">Товары</a></li>
                            <li class="nav-item"><a class="nav-link<?= ($route === 'catalog/category') ? ' active' : ''; ?>" href="<?= admin_url('catalog/category'); ?>">Категории</a></li>
                            <li class="nav-item"><a class="nav-link<?= ($route === 'catalog/manufacturer') ? ' active' : ''; ?>" href="<?= admin_url('catalog/manufacturer'); ?>">Производители</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $stockActive ? ' active' : ''; ?>" href="#stockMenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= $stockActive ? 'true' : 'false'; ?>" aria-controls="stockMenu"><i class="bi bi-archive"></i> Склад</a>
                    <div class="collapse<?= $stockActive ? ' show' : ''; ?>" id="stockMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link<?= ($route === 'stock/supply' && $action === 'create') ? ' active' : ''; ?>" href="<?= admin_url('stock/supply', array('action' => 'create')); ?>">Создать поставку</a></li>
                            <li class="nav-item"><a class="nav-link<?= ($route === 'stock/supply' && $action === 'history') ? ' active' : ''; ?>" href="<?= admin_url('stock/supply', array('action' => 'history')); ?>">История поставок</a></li>
                            <li class="nav-item"><a class="nav-link<?= ($route === 'stock/warehouse') ? ' active' : ''; ?>" href="<?= admin_url('stock/warehouse'); ?>">Склад</a></li>
                            <li class="nav-item"><a class="nav-link<?= ($route === 'stock/sale') ? ' active' : ''; ?>" href="<?= admin_url('stock/sale'); ?>">Продажа</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $systemActive ? ' active' : ''; ?>" href="#systemMenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= $systemActive ? 'true' : 'false'; ?>" aria-controls="systemMenu"><i class="bi bi-gear"></i> Система</a>
                    <div class="collapse<?= $systemActive ? ' show' : ''; ?>" id="systemMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link<?= ($route === 'system/setting') ? ' active' : ''; ?>" href="<?= admin_url('system/setting'); ?>">Настройки</a></li>
                            <li class="nav-item">
                                <a class="nav-link<?= $systemLocalizationActive ? ' active' : ''; ?>" href="#systemLocalizationMenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= $systemLocalizationActive ? 'true' : 'false'; ?>" aria-controls="systemLocalizationMenu">Локализация</a>
                                <div class="collapse<?= $systemLocalizationActive ? ' show' : ''; ?>" id="systemLocalizationMenu">
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item"><a class="nav-link<?= ($route === 'system/currency') ? ' active' : ''; ?>" href="<?= admin_url('system/currency'); ?>">Валюты</a></li>
                                        <li class="nav-item"><a class="nav-link<?= ($route === 'system/length') ? ' active' : ''; ?>" href="<?= admin_url('system/length'); ?>">Единицы длины</a></li>
                                        <li class="nav-item"><a class="nav-link<?= ($route === 'system/weight') ? ' active' : ''; ?>" href="<?= admin_url('system/weight'); ?>">Единицы веса</a></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </aside>
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
