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
