<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Товары</h1>
    <a href="<?= admin_url('catalog/product', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить товар</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Модель</th>
                    <th>Серия</th>
                    <th>Производитель</th>
                    <th>Категория</th>
                    <th>Цена закупки</th>
                    <th>Валюта</th>
                    <th>Сортировка</th>
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
