<?php
    $selectedSourceName = isset($selected_source_name) ? $selected_source_name : '';
    $hasSources = !empty($sources);
    $currentSourceId = isset($selected_source_id) ? (int)$selected_source_id : 0;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <?= $selectedSourceName !== '' ? 'Расчет цен ' . htmlspecialchars($selectedSourceName) : 'Расчет цен по источникам'; ?>
    </h1>
</div>

<?php if ($hasSources): ?>
    <ul class="nav nav-pills mb-3">
        <?php foreach ($sources as $source): ?>
            <?php $sourceId = (int)$source['id']; ?>
            <?php $isActiveSource = ($currentSourceId === $sourceId); ?>
            <li class="nav-item">
                <a class="nav-link<?= $isActiveSource ? ' active' : ''; ?>" href="<?= admin_url('sales/pricing', array('source_id' => $sourceId)); ?>">
                    <?= htmlspecialchars($source['name']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="alert alert-info">Добавьте источники заказов в разделе &laquo;Система &rarr; Источники заказов&raquo;, чтобы рассчитать цены.</div>
<?php endif; ?>

<?php if ($hasSources): ?>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="source_id" value="<?= $currentSourceId; ?>">
            <div class="col-12 col-lg-4">
                <label class="form-label">Прием платежа</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="payment_value" id="paymentValue" class="form-control pricing-expense-control" value="<?= isset($expenses['payment_value']) ? htmlspecialchars(number_format((float)$expenses['payment_value'], 2, '.', '')) : '0.00'; ?>">
                    <select name="payment_type" id="paymentType" class="form-select pricing-expense-control">
                        <option value="percent" <?= (isset($expenses['payment_type']) && $expenses['payment_type'] === 'percent') ? 'selected' : ''; ?>>%</option>
                        <option value="fixed" <?= (isset($expenses['payment_type']) && $expenses['payment_type'] === 'fixed') ? 'selected' : ''; ?>>Сумма</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Логистика</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="logistics_value" id="logisticsValue" class="form-control pricing-expense-control" value="<?= isset($expenses['logistics_value']) ? htmlspecialchars(number_format((float)$expenses['logistics_value'], 2, '.', '')) : '0.00'; ?>">
                    <select name="logistics_type" id="logisticsType" class="form-select pricing-expense-control">
                        <option value="percent" <?= (isset($expenses['logistics_type']) && $expenses['logistics_type'] === 'percent') ? 'selected' : ''; ?>>%</option>
                        <option value="fixed" <?= (isset($expenses['logistics_type']) && $expenses['logistics_type'] === 'fixed') ? 'selected' : ''; ?>>Сумма</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Баллы за отзывы</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="reviews_value" id="reviewsValue" class="form-control pricing-expense-control" value="<?= isset($expenses['reviews_value']) ? htmlspecialchars(number_format((float)$expenses['reviews_value'], 2, '.', '')) : '0.00'; ?>">
                    <select name="reviews_type" id="reviewsType" class="form-select pricing-expense-control">
                        <option value="percent" <?= (isset($expenses['reviews_type']) && $expenses['reviews_type'] === 'percent') ? 'selected' : ''; ?>>%</option>
                        <option value="fixed" <?= (isset($expenses['reviews_type']) && $expenses['reviews_type'] === 'fixed') ? 'selected' : ''; ?>>Сумма</option>
                    </select>
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Применить</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info">В каталоге пока нет товаров для расчета.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="pricing-table">
                    <thead>
                        <tr>
                            <th>Наименование</th>
                            <th>Бренд</th>
                            <th>Серия</th>
                            <th>Закупочная стоимость (<?= htmlspecialchars($default_currency_code); ?>)</th>
                            <th style="width: 140px;">Цена продажи (<?= htmlspecialchars($default_currency_code); ?>)</th>
                            <th>Доходность (%)</th>
                            <th>Доходность (<?= htmlspecialchars($default_currency_code); ?>)</th>
                            <th>Размещение</th>
                            <th>Прием платежа</th>
                            <th>Логистика</th>
                            <th>Баллы за отзывы</th>
                            <th>Тотал расходы</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php
                            $purchaseCost = isset($product['purchase_cost']) ? (float)$product['purchase_cost'] : 0.0;
                            $salePriceDefault = $purchaseCost > 0 ? number_format($purchaseCost, 2, '.', '') : '0.00';
                        ?>
                        <tr data-purchase-cost="<?= htmlspecialchars(number_format($purchaseCost, 6, '.', '')); ?>" data-commission-percent="<?= htmlspecialchars(number_format((float)$product['commission_percent'], 4, '.', '')); ?>">
                            <td>
                                <?= htmlspecialchars($product['name']); ?><?php if (!empty($product['model'])): ?> <span class="text-muted">(<?= htmlspecialchars($product['model']); ?>)</span><?php endif; ?>
                            </td>
                            <td><?= !empty($product['manufacturer']) ? htmlspecialchars($product['manufacturer']) : '—'; ?></td>
                            <td><?= !empty($product['series']) ? htmlspecialchars($product['series']) : '—'; ?></td>
                            <td class="purchase-cost"><?= number_format($purchaseCost, 2, '.', ' '); ?> <?= htmlspecialchars($default_currency_code); ?></td>
                            <td><input type="number" step="0.01" min="0" class="form-control sale-price-input" value="<?= $salePriceDefault; ?>"></td>
                            <td class="profit-percent text-nowrap">0</td>
                            <td class="profit-amount text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                            <td class="placement-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                            <td class="payment-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                            <td class="logistics-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                            <td class="reviews-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                            <td class="total-expenses text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php endif; ?>

<script>
(function() {
    const currencyCode = <?= json_encode($default_currency_code); ?>;
    const expenseControls = document.querySelectorAll('.pricing-expense-control');
    const table = document.getElementById('pricing-table');

    if (!table) {
        return;
    }

    const expenses = {
        paymentType: document.getElementById('paymentType') ? document.getElementById('paymentType').value : 'percent',
        paymentValue: parseFloat(document.getElementById('paymentValue') ? document.getElementById('paymentValue').value : '0') || 0,
        logisticsType: document.getElementById('logisticsType') ? document.getElementById('logisticsType').value : 'percent',
        logisticsValue: parseFloat(document.getElementById('logisticsValue') ? document.getElementById('logisticsValue').value : '0') || 0,
        reviewsType: document.getElementById('reviewsType') ? document.getElementById('reviewsType').value : 'percent',
        reviewsValue: parseFloat(document.getElementById('reviewsValue') ? document.getElementById('reviewsValue').value : '0') || 0
    };

    function parseValue(value) {
        const number = parseFloat(value);
        return Number.isNaN(number) ? 0 : number;
    }

    function formatCurrency(value) {
        return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + currencyCode;
    }

    function formatPercent(value) {
        return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' %';
    }

    function calculateRow(row) {
        const saleInput = row.querySelector('.sale-price-input');
        const salePrice = saleInput ? parseValue(saleInput.value) : 0;
        const purchaseCost = parseValue(row.dataset.purchaseCost || '0');
        const commissionPercent = parseValue(row.dataset.commissionPercent || '0');

        const placementCost = salePrice * (commissionPercent / 100);
        const paymentCost = expenses.paymentType === 'percent' ? salePrice * (expenses.paymentValue / 100) : expenses.paymentValue;
        const logisticsCost = expenses.logisticsType === 'percent' ? salePrice * (expenses.logisticsValue / 100) : expenses.logisticsValue;
        const reviewsCost = expenses.reviewsType === 'percent' ? salePrice * (expenses.reviewsValue / 100) : expenses.reviewsValue;

        const totalExpenses = purchaseCost + placementCost + paymentCost + logisticsCost + reviewsCost;
        const profitAmount = salePrice - totalExpenses;
        const profitPercent = salePrice > 0 ? (profitAmount / salePrice) * 100 : 0;

        const profitPercentCell = row.querySelector('.profit-percent');
        const profitAmountCell = row.querySelector('.profit-amount');
        const placementCell = row.querySelector('.placement-cost');
        const paymentCell = row.querySelector('.payment-cost');
        const logisticsCell = row.querySelector('.logistics-cost');
        const reviewsCell = row.querySelector('.reviews-cost');
        const totalExpensesCell = row.querySelector('.total-expenses');

        if (profitPercentCell) {
            profitPercentCell.textContent = formatPercent(profitPercent);
            profitPercentCell.classList.toggle('text-danger', profitAmount < 0);
            profitPercentCell.classList.toggle('text-success', profitAmount > 0);
        }
        if (profitAmountCell) {
            profitAmountCell.textContent = formatCurrency(profitAmount);
        }
        if (placementCell) {
            placementCell.textContent = formatCurrency(placementCost);
        }
        if (paymentCell) {
            paymentCell.textContent = formatCurrency(paymentCost);
        }
        if (logisticsCell) {
            logisticsCell.textContent = formatCurrency(logisticsCost);
        }
        if (reviewsCell) {
            reviewsCell.textContent = formatCurrency(reviewsCost);
        }
        if (totalExpensesCell) {
            totalExpensesCell.textContent = formatCurrency(totalExpenses);
        }
    }

    function recalculateAll() {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row) => calculateRow(row));
    }

    function updateExpensesFromControl(control) {
        const type = control.getAttribute('id');
        switch (type) {
            case 'paymentType':
                expenses.paymentType = control.value;
                break;
            case 'paymentValue':
                expenses.paymentValue = parseValue(control.value);
                break;
            case 'logisticsType':
                expenses.logisticsType = control.value;
                break;
            case 'logisticsValue':
                expenses.logisticsValue = parseValue(control.value);
                break;
            case 'reviewsType':
                expenses.reviewsType = control.value;
                break;
            case 'reviewsValue':
                expenses.reviewsValue = parseValue(control.value);
                break;
            default:
                break;
        }
    }

    if (expenseControls) {
        expenseControls.forEach((control) => {
            const handler = () => {
                updateExpensesFromControl(control);
                recalculateAll();
            };
            control.addEventListener('input', handler);
            control.addEventListener('change', handler);
        });
    }

    table.querySelectorAll('.sale-price-input').forEach((input) => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            if (row) {
                calculateRow(row);
            }
        });
    });

    recalculateAll();
})();
</script>
