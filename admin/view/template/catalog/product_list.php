<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Товары</h1>
    <a href="<?= admin_url('catalog/product', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить товар</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= admin_url('catalog/product'); ?>" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="catalog/product">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort); ?>">
            <input type="hidden" name="order" value="<?= htmlspecialchars($order); ?>">
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Наименование</label>
                <input type="text" name="filter_name" class="form-control" value="<?= htmlspecialchars($filters['filter_name']); ?>" list="filter-name-options" autocomplete="off">
                <datalist id="filter-name-options">
                    <?php foreach ($name_suggestions as $suggestion): ?>
                        <option value="<?= htmlspecialchars($suggestion['name']); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Модель</label>
                <input type="text" name="filter_model" class="form-control" value="<?= htmlspecialchars($filters['filter_model']); ?>" list="filter-model-options" autocomplete="off">
                <datalist id="filter-model-options">
                    <?php foreach ($model_suggestions as $suggestion): ?>
                        <option value="<?= htmlspecialchars($suggestion['model']); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Серия</label>
                <input type="text" name="filter_series" class="form-control" value="<?= htmlspecialchars($filters['filter_series']); ?>" list="filter-series-options" autocomplete="off">
                <datalist id="filter-series-options">
                    <?php foreach ($series_suggestions as $suggestion): ?>
                        <option value="<?= htmlspecialchars($suggestion['series']); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Производитель</label>
                <select name="filter_manufacturer_id" class="form-select">
                    <option value="">— Все —</option>
                    <?php foreach ($manufacturers as $manufacturer): ?>
                        <option value="<?= (int)$manufacturer['id']; ?>" <?= ($filters['filter_manufacturer_id'] == $manufacturer['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($manufacturer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Категория</label>
                <select name="filter_category_id" class="form-select">
                    <option value="">— Все —</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id']; ?>" <?= ($filters['filter_category_id'] == $category['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6 ms-auto text-end">
                <button type="submit" class="btn btn-primary me-2">Применить</button>
                <a href="<?= admin_url('catalog/product'); ?>" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<?php
$toggleOrder = function ($column) use ($sort, $order) {
    if ($sort === $column) {
        return $order === 'ASC' ? 'DESC' : 'ASC';
    }
    return 'ASC';
};

$sortIcon = function ($column) use ($sort, $order) {
    if ($sort !== $column) {
        return '<i class="bi bi-arrow-down-up ms-1 text-muted"></i>';
    }
    return $order === 'ASC' ? '<i class="bi bi-caret-up-fill ms-1"></i>' : '<i class="bi bi-caret-down-fill ms-1"></i>';
};
?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th><a class="text-decoration-none" href="<?= admin_url('catalog/product', array_merge($url_filters, array('sort' => 'name', 'order' => $toggleOrder('name')))); ?>">Наименование <?= $sortIcon('name'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('catalog/product', array_merge($url_filters, array('sort' => 'model', 'order' => $toggleOrder('model')))); ?>">Модель <?= $sortIcon('model'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('catalog/product', array_merge($url_filters, array('sort' => 'series', 'order' => $toggleOrder('series')))); ?>">Серия <?= $sortIcon('series'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('catalog/product', array_merge($url_filters, array('sort' => 'manufacturer', 'order' => $toggleOrder('manufacturer')))); ?>">Производитель <?= $sortIcon('manufacturer'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('catalog/product', array_merge($url_filters, array('sort' => 'category', 'order' => $toggleOrder('category')))); ?>">Категория <?= $sortIcon('category'); ?></a></th>
                    <th>Цена закупки</th>
                    <th>Валюта</th>
                    <th><a class="text-decoration-none" href="<?= admin_url('catalog/product', array_merge($url_filters, array('sort' => 'sort_order', 'order' => $toggleOrder('sort_order')))); ?>">Сортировка <?= $sortIcon('sort_order'); ?></a></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= (int)$product['id']; ?></td>
                        <td><?= htmlspecialchars($product['name']); ?></td>
                        <td><?= htmlspecialchars($product['model']); ?></td>
                        <td><?= htmlspecialchars($product['series']); ?></td>
                        <td><?= htmlspecialchars($product['manufacturer_name']); ?></td>
                        <td><?= htmlspecialchars($product['category_name']); ?></td>
                        <td><?= number_format((float)$product['purchase_price'], 2, '.', ' '); ?></td>
                        <td><?= htmlspecialchars($product['purchase_currency']); ?></td>
                        <td><?= (int)$product['sort_order']; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('catalog/product', array('action' => 'form', 'id' => $product['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('catalog/product', array('action' => 'delete', 'id' => $product['id'])); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить товар?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center">Товары не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
