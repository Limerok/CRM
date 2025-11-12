<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Продажа №<?= (int)$sale['id']; ?></h1>
    <div class="btn-group">
        <a href="<?= admin_url('stock/sale'); ?>" class="btn btn-secondary">К продажам</a>
        <a href="<?= admin_url('stock/sale', array('action' => 'edit', 'id' => $sale['id'])); ?>" class="btn btn-primary">Редактировать</a>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <?php
                $saleDateValue = isset($sale['sale_date']) ? (string)$sale['sale_date'] : '';
                $saleDateDisplay = ($saleDateValue !== '' && $saleDateValue !== '0000-00-00') ? $saleDateValue : '—';
            ?>
            <div class="col-md-4">
                <div class="fw-semibold text-muted">Дата продажи</div>
                <div><?= htmlspecialchars($saleDateDisplay); ?></div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-muted">Дата создания</div>
                <div><?= htmlspecialchars($sale['created_at']); ?></div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-muted">Всего позиций</div>
                <div><?= (int)$total_items; ?></div>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Товар</th>
                    <th>Модель</th>
                    <th>Дата заказа</th>
                    <th>Источник</th>
                    <th>Статус заказа</th>
                    <th>№ задания</th>
                    <th>№ заказа</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['model']); ?></td>
                        <td><?= htmlspecialchars($item['order_date']); ?></td>
                        <td><?= htmlspecialchars(isset($item['source_name']) ? (string)$item['source_name'] : ''); ?></td>
                        <td><?= htmlspecialchars(isset($item['order_status']) ? (string)$item['order_status'] : ''); ?></td>
                        <td><?= htmlspecialchars(isset($item['task_number']) ? (string)$item['task_number'] : ''); ?></td>
                        <td><?= htmlspecialchars(isset($item['order_number']) ? (string)$item['order_number'] : ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Товары не найдены</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
