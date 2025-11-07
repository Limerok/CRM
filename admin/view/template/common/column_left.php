        <aside class="col-md-3 col-lg-2 bg-light sidebar py-4">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?= admin_url('common/dashboard'); ?>"><i class="bi bi-speedometer"></i> Дашборд</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#catalogMenu" data-bs-toggle="collapse" role="button" aria-expanded="false"><i class="bi bi-box"></i> Каталог</a>
                    <div class="collapse" id="catalogMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('catalog/product'); ?>">Товары</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('catalog/category'); ?>">Категории</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('catalog/manufacturer'); ?>">Производители</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#stockMenu" data-bs-toggle="collapse" role="button" aria-expanded="false"><i class="bi bi-archive"></i> Склад</a>
                    <div class="collapse" id="stockMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('stock/supply', array('action' => 'create')); ?>">Создать поставку</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('stock/supply', array('action' => 'history')); ?>">История поставок</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('stock/warehouse'); ?>">Склад</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= admin_url('stock/sale'); ?>">Продажа</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </aside>
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
