<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Склад</h1>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Товар</th>
                    <th>Модель</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= (int)$item['product_id']; ?></td>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['model']); ?></td>
                        <td><?= (int)$item['quantity']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Склад пуст</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
