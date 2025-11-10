<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Дашборд</h1>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card text-bg-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Товаров на складе</h5>
                <p class="display-6 mb-0"><?= (int)$stats['stock_items']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Заказов за неделю</h5>
                <p class="display-6 mb-0"><?= (int)$stats['weekly_orders']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-info shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Заказов за месяц</h5>
                <p class="display-6 mb-0"><?= (int)$stats['monthly_orders']; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Пополнение остатков</h5>
        </div>
        <?php if (!empty($replenishments)): ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Наименование</th>
                            <th class="text-end" style="width: 160px;">Рекомендуется подвезти</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($replenishments as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars(isset($item['name']) ? (string)$item['name'] : ''); ?></td>
                                <td class="text-end"><?= (int)$item['to_deliver']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Нет рекомендаций по пополнению.</p>
        <?php endif; ?>
    </div>
</div>
