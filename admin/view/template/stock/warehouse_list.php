<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Склад</h1>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= admin_url('stock/warehouse'); ?>" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="stock/warehouse">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort); ?>">
            <input type="hidden" name="order" value="<?= htmlspecialchars($order); ?>">
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Наименование</label>
                <input type="text" name="filter_name" class="form-control" value="<?= htmlspecialchars($filters['filter_name']); ?>" list="warehouse-filter-name-options" autocomplete="off">
                <datalist id="warehouse-filter-name-options">
                    <?php foreach ($name_suggestions as $suggestion): ?>
                        <option value="<?= htmlspecialchars($suggestion['name']); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Модель</label>
                <input type="text" name="filter_model" class="form-control" value="<?= htmlspecialchars($filters['filter_model']); ?>" list="warehouse-filter-model-options" autocomplete="off">
                <datalist id="warehouse-filter-model-options">
                    <?php foreach ($model_suggestions as $suggestion): ?>
                        <option value="<?= htmlspecialchars($suggestion['model']); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label mb-1">Серия</label>
                <input type="text" name="filter_series" class="form-control" value="<?= htmlspecialchars($filters['filter_series']); ?>" list="warehouse-filter-series-options" autocomplete="off">
                <datalist id="warehouse-filter-series-options">
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
                <a href="<?= admin_url('stock/warehouse'); ?>" class="btn btn-outline-secondary">Сбросить</a>
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
                    <th><a class="text-decoration-none" href="<?= admin_url('stock/warehouse', array_merge($url_filters, array('sort' => 'name', 'order' => $toggleOrder('name')))); ?>">Наименование <?= $sortIcon('name'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('stock/warehouse', array_merge($url_filters, array('sort' => 'model', 'order' => $toggleOrder('model')))); ?>">Модель <?= $sortIcon('model'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('stock/warehouse', array_merge($url_filters, array('sort' => 'series', 'order' => $toggleOrder('series')))); ?>">Серия <?= $sortIcon('series'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('stock/warehouse', array_merge($url_filters, array('sort' => 'manufacturer', 'order' => $toggleOrder('manufacturer')))); ?>">Производитель <?= $sortIcon('manufacturer'); ?></a></th>
                    <th><a class="text-decoration-none" href="<?= admin_url('stock/warehouse', array_merge($url_filters, array('sort' => 'category', 'order' => $toggleOrder('category')))); ?>">Категория <?= $sortIcon('category'); ?></a></th>
                    <th class="text-end"><a class="text-decoration-none" href="<?= admin_url('stock/warehouse', array_merge($url_filters, array('sort' => 'quantity', 'order' => $toggleOrder('quantity')))); ?>">Количество <?= $sortIcon('quantity'); ?></a></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= (int)$item['product_id']; ?></td>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['model']); ?></td>
                        <td><?= htmlspecialchars($item['series']); ?></td>
                        <td><?= htmlspecialchars($item['manufacturer_name'] !== null ? $item['manufacturer_name'] : '—'); ?></td>
                        <td><?= htmlspecialchars($item['category_name'] !== null ? $item['category_name'] : '—'); ?></td>
                        <td class="text-end"><?= (int)$item['quantity']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Склад пуст</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
