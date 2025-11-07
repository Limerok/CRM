<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Дашборд</h1>
</div>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card text-bg-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Товары</h5>
                <p class="display-6 mb-0"><?= (int)$stats['products']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Категории</h5>
                <p class="display-6 mb-0"><?= (int)$stats['categories']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Производители</h5>
                <p class="display-6 mb-0"><?= (int)$stats['manufacturers']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Товаров на складе</h5>
                <p class="display-6 mb-0"><?= (int)$stats['stock_items']; ?></p>
            </div>
        </div>
    </div>
</div>
