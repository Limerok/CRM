<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">История поставок</h1>
    <a href="<?= admin_url('stock/supply', array('action' => 'create')); ?>" class="btn btn-primary">Новая поставка</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Дата</th>
                    <th>Позиций</th>
                    <th>Всего товаров</th>
                    <th>Создано</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($supplies): ?>
                <?php foreach ($supplies as $supply): ?>
                    <tr>
                        <td><?= (int)$supply['id']; ?></td>
                        <td><?= htmlspecialchars($supply['supply_date']); ?></td>
                        <td><?= (int)$supply['items_count']; ?></td>
                        <td><?= (int)$supply['total_quantity']; ?></td>
                        <td><?= htmlspecialchars($supply['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Поставки не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
