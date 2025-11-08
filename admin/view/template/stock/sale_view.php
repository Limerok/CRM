<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Продажа №<?= (int)$sale['id']; ?></h1>
    <a href="<?= admin_url('stock/sale'); ?>" class="btn btn-secondary">Назад к продажам</a>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="fw-semibold text-muted">Дата продажи</div>
                <div><?= htmlspecialchars($sale['sale_date']); ?></div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-muted">Дата создания</div>
                <div><?= htmlspecialchars($sale['created_at']); ?></div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-muted">Всего товаров</div>
                <div><?= (int)$total_quantity; ?></div>
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
                    <th class="text-end">Количество</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($items): ?>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['model']); ?></td>
                        <td class="text-end"><?= (int)$item['quantity']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Товары не найдены</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
