<?php
    $selectedSourceName = isset($selected_source_name) ? $selected_source_name : '';
    $hasSources = !empty($sources);
    $currentSourceId = isset($selected_source_id) ? (int)$selected_source_id : 0;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Расчет цен</h1>
        <?php if ($selectedSourceName !== ''): ?>
            <div class="text-muted small mt-1">Источник: <?= htmlspecialchars($selectedSourceName); ?></div>
        <?php endif; ?>
    </div>
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
    <?php if (empty($products)): ?>
        <div class="alert alert-info">В каталоге пока нет товаров для расчета.</div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <p class="mb-0 text-muted">Изменяйте значения прямо в таблице &mdash; все расчеты обновляются мгновенно.</p>
                    <button type="button" class="btn btn-primary" id="pricing-save-button">Сохранить</button>
                </div>
                <div id="pricing-alert" class="alert d-none" role="alert"></div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="pricing-table" data-source-id="<?= $currentSourceId; ?>">
                        <thead>
                            <tr>
                                <th>Наименование</th>
                                <th>Бренд</th>
                                <th>Серия</th>
                                <th>Закупка (<?= htmlspecialchars($default_currency_code); ?>)</th>
                                <th style="width: 160px;">Цена продажи</th>
                                <th style="width: 140px;">Доходность (%)</th>
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
                                $salePriceRaw = array_key_exists('sale_price', $product) ? $product['sale_price'] : null;
                                $salePriceValue = ($salePriceRaw !== null) ? number_format((float)$salePriceRaw, 2, '.', '') : number_format($purchaseCost, 2, '.', '');
                                $profitPercentRaw = array_key_exists('profit_percent', $product) ? $product['profit_percent'] : 0.0;
                                $profitPercentValue = number_format((float)$profitPercentRaw, 2, '.', '');
                                $paymentValueRaw = array_key_exists('payment_value', $product) ? $product['payment_value'] : 0.0;
                                $logisticsValueRaw = array_key_exists('logistics_value', $product) ? $product['logistics_value'] : 0.0;
                                $reviewsValueRaw = array_key_exists('reviews_value', $product) ? $product['reviews_value'] : 0.0;
                                $paymentValue = number_format((float)$paymentValueRaw, 2, '.', '');
                                $logisticsValue = number_format((float)$logisticsValueRaw, 2, '.', '');
                                $reviewsValue = number_format((float)$reviewsValueRaw, 2, '.', '');
                                $paymentType = isset($product['payment_type']) ? $product['payment_type'] : 'percent';
                                $logisticsType = isset($product['logistics_type']) ? $product['logistics_type'] : 'percent';
                                $reviewsType = isset($product['reviews_type']) ? $product['reviews_type'] : 'percent';
                            ?>
                            <tr data-product-id="<?= (int)$product['id']; ?>" data-purchase-cost="<?= htmlspecialchars(number_format($purchaseCost, 6, '.', '')); ?>" data-commission-percent="<?= htmlspecialchars(number_format((float)$product['commission_percent'], 4, '.', '')); ?>">
                                <td><?= htmlspecialchars($product['name']); ?></td>
                                <td><?= !empty($product['manufacturer']) ? htmlspecialchars($product['manufacturer']) : '—'; ?></td>
                                <td><?= !empty($product['series']) ? htmlspecialchars($product['series']) : '—'; ?></td>
                                <td class="purchase-cost text-nowrap"><?= number_format($purchaseCost, 2, '.', ' '); ?> <?= htmlspecialchars($default_currency_code); ?></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" min="0" class="form-control text-end sale-price-input" value="<?= $salePriceValue; ?>">
                                        <span class="input-group-text"><?= htmlspecialchars($default_currency_code); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" class="form-control text-end profit-percent-input" value="<?= $profitPercentValue; ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </td>
                                <td class="profit-amount text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                                <td class="placement-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end payment-value-input" value="<?= $paymentValue; ?>">
                                        <select class="form-select form-select-sm payment-type-select">
                                            <option value="percent" <?= $paymentType === 'percent' ? 'selected' : ''; ?>>%</option>
                                            <option value="fixed" <?= $paymentType === 'fixed' ? 'selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                                        </select>
                                        <div class="text-muted small payment-cost">0 <?= htmlspecialchars($default_currency_code); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end logistics-value-input" value="<?= $logisticsValue; ?>">
                                        <select class="form-select form-select-sm logistics-type-select">
                                            <option value="percent" <?= $logisticsType === 'percent' ? 'selected' : ''; ?>>%</option>
                                            <option value="fixed" <?= $logisticsType === 'fixed' ? 'selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                                        </select>
                                        <div class="text-muted small logistics-cost">0 <?= htmlspecialchars($default_currency_code); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end reviews-value-input" value="<?= $reviewsValue; ?>">
                                        <select class="form-select form-select-sm reviews-type-select">
                                            <option value="percent" <?= $reviewsType === 'percent' ? 'selected' : ''; ?>>%</option>
                                            <option value="fixed" <?= $reviewsType === 'fixed' ? 'selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                                        </select>
                                        <div class="text-muted small reviews-cost">0 <?= htmlspecialchars($default_currency_code); ?></div>
                                    </div>
                                </td>
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
    const selectedSourceId = <?= (int)$currentSourceId; ?>;
    const saveUrl = <?= json_encode(admin_url('sales/pricing', array('action' => 'save'))); ?>;
    const table = document.getElementById('pricing-table');
    const saveButton = document.getElementById('pricing-save-button');
    const alertBox = document.getElementById('pricing-alert');

    if (!table) {
        return;
    }

    function parseValue(value) {
        const number = parseFloat(value);
        return Number.isNaN(number) ? 0 : number;
    }

    function formatCurrency(value) {
        return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + currencyCode;
    }

    function setProfitInputState(input, profitAmount) {
        if (!input) {
            return;
        }
        input.classList.toggle('text-success', profitAmount > 0);
        input.classList.toggle('text-danger', profitAmount < 0);
    }

    function getExpenseParts(row) {
        const paymentTypeSelect = row.querySelector('.payment-type-select');
        const paymentValueInput = row.querySelector('.payment-value-input');
        const paymentType = paymentTypeSelect ? paymentTypeSelect.value : 'percent';
        const paymentValue = paymentValueInput ? parseValue(paymentValueInput.value) : 0;
        const paymentRate = paymentType === 'percent' ? paymentValue / 100 : 0;
        const paymentFixed = paymentType === 'fixed' ? paymentValue : 0;

        const logisticsTypeSelect = row.querySelector('.logistics-type-select');
        const logisticsValueInput = row.querySelector('.logistics-value-input');
        const logisticsType = logisticsTypeSelect ? logisticsTypeSelect.value : 'percent';
        const logisticsValue = logisticsValueInput ? parseValue(logisticsValueInput.value) : 0;
        const logisticsRate = logisticsType === 'percent' ? logisticsValue / 100 : 0;
        const logisticsFixed = logisticsType === 'fixed' ? logisticsValue : 0;

        const reviewsTypeSelect = row.querySelector('.reviews-type-select');
        const reviewsValueInput = row.querySelector('.reviews-value-input');
        const reviewsType = reviewsTypeSelect ? reviewsTypeSelect.value : 'percent';
        const reviewsValue = reviewsValueInput ? parseValue(reviewsValueInput.value) : 0;
        const reviewsRate = reviewsType === 'percent' ? reviewsValue / 100 : 0;
        const reviewsFixed = reviewsType === 'fixed' ? reviewsValue : 0;

        return {
            paymentType,
            paymentValue,
            paymentRate,
            paymentFixed,
            logisticsType,
            logisticsValue,
            logisticsRate,
            logisticsFixed,
            reviewsType,
            reviewsValue,
            reviewsRate,
            reviewsFixed,
        };
    }

    function calculateRow(row) {
        const saleInput = row.querySelector('.sale-price-input');
        const profitInput = row.querySelector('.profit-percent-input');
        if (!saleInput) {
            return null;
        }

        const salePrice = parseValue(saleInput.value);
        const purchaseCost = parseValue(row.dataset.purchaseCost || '0');
        const commissionPercent = parseValue(row.dataset.commissionPercent || '0');
        const commissionRate = commissionPercent / 100;

        const expenses = getExpenseParts(row);

        const placementCost = salePrice * commissionRate;
        const paymentCost = expenses.paymentType === 'percent' ? salePrice * expenses.paymentRate : expenses.paymentFixed;
        const logisticsCost = expenses.logisticsType === 'percent' ? salePrice * expenses.logisticsRate : expenses.logisticsFixed;
        const reviewsCost = expenses.reviewsType === 'percent' ? salePrice * expenses.reviewsRate : expenses.reviewsFixed;

        const totalExpenses = purchaseCost + placementCost + paymentCost + logisticsCost + reviewsCost;
        const profitAmount = salePrice - totalExpenses;
        const profitPercent = salePrice > 0 ? (profitAmount / salePrice) * 100 : 0;

        const profitAmountCell = row.querySelector('.profit-amount');
        const placementCell = row.querySelector('.placement-cost');
        const paymentCell = row.querySelector('.payment-cost');
        const logisticsCell = row.querySelector('.logistics-cost');
        const reviewsCell = row.querySelector('.reviews-cost');
        const totalExpensesCell = row.querySelector('.total-expenses');

        if (profitInput) {
            profitInput.value = profitPercent.toFixed(2);
            setProfitInputState(profitInput, profitAmount);
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

        return {
            salePrice,
            profitPercent,
            profitAmount,
            expenses: {
                payment_type: expenses.paymentType,
                payment_value: expenses.paymentValue,
                logistics_type: expenses.logisticsType,
                logistics_value: expenses.logisticsValue,
                reviews_type: expenses.reviewsType,
                reviews_value: expenses.reviewsValue,
            },
        };
    }

    function updateSalePriceFromProfit(row) {
        const saleInput = row.querySelector('.sale-price-input');
        const profitInput = row.querySelector('.profit-percent-input');
        if (!saleInput || !profitInput) {
            return;
        }

        const purchaseCost = parseValue(row.dataset.purchaseCost || '0');
        const commissionPercent = parseValue(row.dataset.commissionPercent || '0');
        const commissionRate = commissionPercent / 100;
        const expenses = getExpenseParts(row);

        const percentExpenses = commissionRate + expenses.paymentRate + expenses.logisticsRate + expenses.reviewsRate;
        const fixedExpenses = purchaseCost + expenses.paymentFixed + expenses.logisticsFixed + expenses.reviewsFixed;

        let targetPercent = parseValue(profitInput.value);
        let targetRatio = targetPercent / 100;
        const maxRatio = Math.max(0, 1 - percentExpenses - 0.0001);
        if (targetRatio > maxRatio) {
            targetRatio = maxRatio;
            profitInput.value = (targetRatio * 100).toFixed(2);
        }

        const denominator = 1 - percentExpenses - targetRatio;
        let salePrice = 0;
        if (denominator <= 0) {
            salePrice = fixedExpenses;
        } else {
            salePrice = fixedExpenses / denominator;
        }

        if (!Number.isFinite(salePrice) || salePrice < 0) {
            salePrice = 0;
        }

        saleInput.value = salePrice.toFixed(2);
    }

    function recalculateAll() {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row) => {
            calculateRow(row);
        });
    }

    function showAlert(message, type) {
        if (!alertBox) {
            return;
        }
        alertBox.textContent = message;
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        alertBox.classList.add('alert-' + type);
    }

    function clearAlert() {
        if (!alertBox) {
            return;
        }
        alertBox.classList.add('d-none');
        alertBox.classList.remove('alert-success', 'alert-danger', 'alert-warning');
        alertBox.textContent = '';
    }

    table.querySelectorAll('.sale-price-input').forEach((input) => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            if (row) {
                calculateRow(row);
            }
        });
    });

    table.querySelectorAll('.profit-percent-input').forEach((input) => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            if (row) {
                updateSalePriceFromProfit(row);
                calculateRow(row);
            }
        });
    });

    table.querySelectorAll('.payment-value-input, .logistics-value-input, .reviews-value-input').forEach((input) => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            if (row) {
                calculateRow(row);
            }
        });
    });

    table.querySelectorAll('.payment-type-select, .logistics-type-select, .reviews-type-select').forEach((select) => {
        select.addEventListener('change', () => {
            const row = select.closest('tr');
            if (row) {
                calculateRow(row);
            }
        });
    });

    if (saveButton) {
        saveButton.addEventListener('click', async () => {
            clearAlert();

            if (!selectedSourceId) {
                showAlert('Выберите источник заказа, чтобы сохранить данные.', 'warning');
                return;
            }

            const rows = table.querySelectorAll('tbody tr');
            if (!rows.length) {
                showAlert('Нет данных для сохранения.', 'warning');
                return;
            }

            const items = [];
            rows.forEach((row) => {
                const productId = parseInt(row.dataset.productId || '0', 10);
                if (!productId) {
                    return;
                }

                const saleInput = row.querySelector('.sale-price-input');
                const profitInput = row.querySelector('.profit-percent-input');
                const rowData = calculateRow(row) || { expenses: {} };
                const expenses = rowData.expenses || {};

                items.push({
                    product_id: productId,
                    sale_price: saleInput ? parseValue(saleInput.value) : 0,
                    profit_percent: profitInput ? parseValue(profitInput.value) : 0,
                    payment_type: expenses.payment_type || 'percent',
                    payment_value: expenses.payment_value !== undefined ? parseValue(expenses.payment_value) : 0,
                    logistics_type: expenses.logistics_type || 'percent',
                    logistics_value: expenses.logistics_value !== undefined ? parseValue(expenses.logistics_value) : 0,
                    reviews_type: expenses.reviews_type || 'percent',
                    reviews_value: expenses.reviews_value !== undefined ? parseValue(expenses.reviews_value) : 0,
                });
            });

            saveButton.disabled = true;
            saveButton.classList.add('disabled');

            try {
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        source_id: selectedSourceId,
                        items,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();
                if (data && data.success) {
                    showAlert('Данные успешно сохранены.', 'success');
                } else {
                    showAlert((data && data.error) ? data.error : 'Не удалось сохранить данные.', 'danger');
                }
            } catch (error) {
                showAlert('Не удалось сохранить данные. Попробуйте еще раз.', 'danger');
            } finally {
                saveButton.disabled = false;
                saveButton.classList.remove('disabled');
            }
        });
    }

    recalculateAll();
})();
</script>
