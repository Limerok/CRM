<?php
    $selectedSourceName = isset($selected_source_name) ? $selected_source_name : '';
    $hasSources = !empty($sources);
    $currentSourceId = isset($selected_source_id) ? (int)$selected_source_id : 0;
    $defaults = isset($pricing_defaults) && is_array($pricing_defaults) ? $pricing_defaults : array();
    $defaultTaxPercent = isset($defaults['tax_percent']) ? (float)$defaults['tax_percent'] : 0.0;
    $defaultProfitPercent = isset($defaults['profit_percent']) && $defaults['profit_percent'] !== null ? (float)$defaults['profit_percent'] : null;
    $defaultPaymentType = isset($defaults['payment_type']) ? $defaults['payment_type'] : 'percent';
    $defaultPaymentValue = isset($defaults['payment_value']) && $defaults['payment_value'] !== null ? (float)$defaults['payment_value'] : null;
    $defaultLogisticsType = isset($defaults['logistics_type']) ? $defaults['logistics_type'] : 'percent';
    $defaultLogisticsValue = isset($defaults['logistics_value']) && $defaults['logistics_value'] !== null ? (float)$defaults['logistics_value'] : null;
    $defaultReviewsType = isset($defaults['reviews_type']) ? $defaults['reviews_type'] : 'percent';
    $defaultReviewsValue = isset($defaults['reviews_value']) && $defaults['reviews_value'] !== null ? (float)$defaults['reviews_value'] : null;
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
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="card-title mb-0">Значения по умолчанию</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="pricing-save-defaults-button">Сохранить</button>
            </div>
            <div id="pricing-defaults-alert" class="alert d-none" role="alert"></div>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">Налог %</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="default-tax-percent" value="<?= htmlspecialchars(number_format($defaultTaxPercent, 2, '.', '')); ?>">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">Доходность (%)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="default-profit-percent" value="<?= $defaultProfitPercent !== null ? htmlspecialchars(number_format($defaultProfitPercent, 2, '.', '')) : ''; ?>" placeholder="<?= $defaultProfitPercent === null ? '' : ''; ?>">
                </div>
            </div>
            <div class="row g-3 align-items-end mt-0">
                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label">Прием платежа</label>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" id="default-payment-value" value="<?= $defaultPaymentValue !== null ? htmlspecialchars(number_format($defaultPaymentValue, 2, '.', '')) : ''; ?>">
                        <select class="form-select form-select-sm" id="default-payment-type">
                            <option value="percent"<?= $defaultPaymentType === 'percent' ? ' selected' : ''; ?>>%</option>
                            <option value="fixed"<?= $defaultPaymentType === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                        </select>
                    </div>
                    <div class="text-muted small mt-1">Валюта: <?= htmlspecialchars($default_currency_code); ?></div>
                </div>
                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label">Логистика</label>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" id="default-logistics-value" value="<?= $defaultLogisticsValue !== null ? htmlspecialchars(number_format($defaultLogisticsValue, 2, '.', '')) : ''; ?>">
                        <select class="form-select form-select-sm" id="default-logistics-type">
                            <option value="percent"<?= $defaultLogisticsType === 'percent' ? ' selected' : ''; ?>>%</option>
                            <option value="fixed"<?= $defaultLogisticsType === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label">Баллы за отзывы</label>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" id="default-reviews-value" value="<?= $defaultReviewsValue !== null ? htmlspecialchars(number_format($defaultReviewsValue, 2, '.', '')) : ''; ?>">
                        <select class="form-select form-select-sm" id="default-reviews-type">
                            <option value="percent"<?= $defaultReviewsType === 'percent' ? ' selected' : ''; ?>>%</option>
                            <option value="fixed"<?= $defaultReviewsType === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <p class="text-muted small mb-0 mt-3">Если у конкретного товара не указаны значения, будут использованы параметры по умолчанию.</p>
        </div>
    </div>

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
                                <th style="width: 180px;">Доходность (%)</th>
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
                                $profitPercentRaw = array_key_exists('profit_percent', $product) ? $product['profit_percent'] : null;
                                $profitPercentValue = ($profitPercentRaw !== null) ? number_format((float)$profitPercentRaw, 2, '.', '') : '';
                                $paymentValueRaw = array_key_exists('payment_value', $product) ? $product['payment_value'] : null;
                                $logisticsValueRaw = array_key_exists('logistics_value', $product) ? $product['logistics_value'] : null;
                                $reviewsValueRaw = array_key_exists('reviews_value', $product) ? $product['reviews_value'] : null;
                                $paymentValue = ($paymentValueRaw !== null) ? number_format((float)$paymentValueRaw, 2, '.', '') : '';
                                $logisticsValue = ($logisticsValueRaw !== null) ? number_format((float)$logisticsValueRaw, 2, '.', '') : '';
                                $reviewsValue = ($reviewsValueRaw !== null) ? number_format((float)$reviewsValueRaw, 2, '.', '') : '';
                                $paymentType = isset($product['payment_type']) ? $product['payment_type'] : null;
                                $logisticsType = isset($product['logistics_type']) ? $product['logistics_type'] : null;
                                $reviewsType = isset($product['reviews_type']) ? $product['reviews_type'] : null;
                                $hasProfitOverride = $profitPercentRaw !== null;
                                $hasPaymentOverride = ($paymentValueRaw !== null) || ($paymentType !== null);
                                $hasLogisticsOverride = ($logisticsValueRaw !== null) || ($logisticsType !== null);
                                $hasReviewsOverride = ($reviewsValueRaw !== null) || ($reviewsType !== null);
                                $paymentSelectValue = $paymentType !== null ? $paymentType : '';
                                $logisticsSelectValue = $logisticsType !== null ? $logisticsType : '';
                                $reviewsSelectValue = $reviewsType !== null ? $reviewsType : '';
                            ?>
                            <tr
                                data-product-id="<?= (int)$product['id']; ?>"
                                data-purchase-cost="<?= htmlspecialchars(number_format($purchaseCost, 6, '.', '')); ?>"
                                data-commission-percent="<?= htmlspecialchars(number_format((float)$product['commission_percent'], 4, '.', '')); ?>"
                                data-profit-provided="<?= $hasProfitOverride ? '1' : '0'; ?>"
                                data-payment-provided="<?= $hasPaymentOverride ? '1' : '0'; ?>"
                                data-logistics-provided="<?= $hasLogisticsOverride ? '1' : '0'; ?>"
                                data-reviews-provided="<?= $hasReviewsOverride ? '1' : '0'; ?>"
                            >
                                <td><?= htmlspecialchars($product['name']); ?></td>
                                <td><?= !empty($product['manufacturer']) ? htmlspecialchars($product['manufacturer']) : '—'; ?></td>
                                <td><?= !empty($product['series']) ? htmlspecialchars($product['series']) : '—'; ?></td>
                                <td class="purchase-cost text-nowrap"></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" min="0" class="form-control text-end sale-price-input" value="<?= $salePriceValue; ?>">
                                        <span class="input-group-text"><?= htmlspecialchars($default_currency_code); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" min="0" class="form-control text-end profit-percent-input" value="<?= $profitPercentValue; ?>" placeholder="<?= $defaultProfitPercent !== null ? htmlspecialchars(number_format($defaultProfitPercent, 2, '.', '')) : ''; ?>">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="text-muted small profit-percent-info"></div>
                                    </div>
                                </td>
                                <td class="profit-amount text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                                <td class="placement-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
        <input type="number" step="0.01" min="0"
               class="form-control form-control-sm text-end payment-value-input"
               value="<?= $paymentValue; ?>"
               placeholder="<?= $defaultPaymentValue !== null ? htmlspecialchars(number_format($defaultPaymentValue, 2, '.', '')) : '0.00'; ?>">
        <select class="form-select form-select-sm payment-type-select">
            <option value="">По умолчанию</option>
            <option value="percent"<?= $paymentSelectValue === 'percent' ? ' selected' : ''; ?>>%</option>
            <option value="fixed"<?= $paymentSelectValue === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
        </select>
        <div class="text-muted small payment-cost">0 <?= htmlspecialchars($default_currency_code); ?></div>
    </div>  
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
    <input type="number" step="0.01" min="0"
           class="form-control form-control-sm text-end logistics-value-input"
           value="<?= $logisticsValue; ?>"
           placeholder="<?= $defaultLogisticsValue !== null ? htmlspecialchars(number_format($defaultLogisticsValue, 2, '.', '')) : '0.00'; ?>">
    <select class="form-select form-select-sm logistics-type-select">
        <option value="">По умолчанию</option>
        <option value="percent"<?= $logisticsSelectValue === 'percent' ? ' selected' : ''; ?>>%</option>
        <option value="fixed"<?= $logisticsSelectValue === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
    </select>
    <div class="text-muted small logistics-cost">0 <?= htmlspecialchars($default_currency_code); ?></div>
</div>

                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
    <input type="number" step="0.01" min="0"
           class="form-control form-control-sm text-end reviews-value-input"
           value="<?= $reviewsValue; ?>"
           placeholder="<?= $defaultReviewsValue !== null ? htmlspecialchars(number_format($defaultReviewsValue, 2, '.', '')) : '0.00'; ?>">
    <select class="form-select form-select-sm reviews-type-select">
        <option value="">По умолчанию</option>
        <option value="percent"<?= $reviewsSelectValue === 'percent' ? ' selected' : ''; ?>>%</option>
        <option value="fixed"<?= $reviewsSelectValue === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
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
    const saveDefaultsUrl = <?= json_encode(admin_url('sales/pricing', array('action' => 'save_defaults'))); ?>;
    const defaultsData = <?= json_encode(array(
        'tax_percent' => $defaultTaxPercent,
        'profit_percent' => $defaultProfitPercent,
        'payment_type' => $defaultPaymentType,
        'payment_value' => $defaultPaymentValue,
        'logistics_type' => $defaultLogisticsType,
        'logistics_value' => $defaultLogisticsValue,
        'reviews_type' => $defaultReviewsType,
        'reviews_value' => $defaultReviewsValue,
    ), JSON_UNESCAPED_UNICODE); ?>;
    const table = document.getElementById('pricing-table');
    const itemsSaveButton = document.getElementById('pricing-save-button');
    const saveDefaultsButton = document.getElementById('pricing-save-defaults-button');
    const itemsAlertBox = document.getElementById('pricing-alert');
    const defaultsAlertBox = document.getElementById('pricing-defaults-alert');

    const defaultControls = {
        tax: document.getElementById('default-tax-percent'),
        profit: document.getElementById('default-profit-percent'),
        paymentValue: document.getElementById('default-payment-value'),
        paymentType: document.getElementById('default-payment-type'),
        logisticsValue: document.getElementById('default-logistics-value'),
        logisticsType: document.getElementById('default-logistics-type'),
        reviewsValue: document.getElementById('default-reviews-value'),
        reviewsType: document.getElementById('default-reviews-type'),
    };

    const pricingDefaults = {
        tax_percent: typeof defaultsData.tax_percent === 'number' ? defaultsData.tax_percent : parseNumber(defaultsData.tax_percent),
        profit_percent: defaultsData.profit_percent !== null ? parseNumber(defaultsData.profit_percent) : null,
        payment_type: defaultsData.payment_type || 'percent',
        payment_value: defaultsData.payment_value !== null ? parseNumber(defaultsData.payment_value) : null,
        logistics_type: defaultsData.logistics_type || 'percent',
        logistics_value: defaultsData.logistics_value !== null ? parseNumber(defaultsData.logistics_value) : null,
        reviews_type: defaultsData.reviews_type || 'percent',
        reviews_value: defaultsData.reviews_value !== null ? parseNumber(defaultsData.reviews_value) : null,
    };

    if (!table) {
        return;
    }

    function parseNumber(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }
        const number = parseFloat(value);
        return Number.isNaN(number) ? 0 : number;
    }

    function parseNullableNumber(value) {
        if (value === null || value === undefined) {
            return null;
        }
        const trimmed = String(value).trim();
        if (trimmed === '') {
            return null;
        }
        const number = parseFloat(trimmed);
        return Number.isNaN(number) ? null : number;
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

    function updateDefaultsFromInputs() {
        if (defaultControls.tax) {
            const value = parseNumber(defaultControls.tax.value);
            pricingDefaults.tax_percent = value >= 0 ? value : 0;
        }
        if (defaultControls.profit) {
            const value = parseNullableNumber(defaultControls.profit.value);
            pricingDefaults.profit_percent = value !== null && value >= 0 ? value : (value === null ? null : 0);
        }
        if (defaultControls.paymentType) {
            pricingDefaults.payment_type = defaultControls.paymentType.value === 'fixed' ? 'fixed' : 'percent';
        }
        if (defaultControls.paymentValue) {
            const value = parseNullableNumber(defaultControls.paymentValue.value);
            pricingDefaults.payment_value = value !== null && value >= 0 ? value : (value === null ? null : 0);
        }
        if (defaultControls.logisticsType) {
            pricingDefaults.logistics_type = defaultControls.logisticsType.value === 'fixed' ? 'fixed' : 'percent';
        }
        if (defaultControls.logisticsValue) {
            const value = parseNullableNumber(defaultControls.logisticsValue.value);
            pricingDefaults.logistics_value = value !== null && value >= 0 ? value : (value === null ? null : 0);
        }
        if (defaultControls.reviewsType) {
            pricingDefaults.reviews_type = defaultControls.reviewsType.value === 'fixed' ? 'fixed' : 'percent';
        }
        if (defaultControls.reviewsValue) {
            const value = parseNullableNumber(defaultControls.reviewsValue.value);
            pricingDefaults.reviews_value = value !== null && value >= 0 ? value : (value === null ? null : 0);
        }
        updatePlaceholders();
    }

    function updatePlaceholders() {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row) => {
            const profitInput = row.querySelector('.profit-percent-input');
            if (profitInput) {
                profitInput.placeholder = pricingDefaults.profit_percent !== null ? pricingDefaults.profit_percent.toFixed(2) : '';
            }
            const paymentInput = row.querySelector('.payment-value-input');
            if (paymentInput) {
                paymentInput.placeholder = pricingDefaults.payment_value !== null ? pricingDefaults.payment_value.toFixed(2) : '0.00';
            }
            const logisticsInput = row.querySelector('.logistics-value-input');
            if (logisticsInput) {
                logisticsInput.placeholder = pricingDefaults.logistics_value !== null ? pricingDefaults.logistics_value.toFixed(2) : '0.00';
            }
            const reviewsInput = row.querySelector('.reviews-value-input');
            if (reviewsInput) {
                reviewsInput.placeholder = pricingDefaults.reviews_value !== null ? pricingDefaults.reviews_value.toFixed(2) : '0.00';
            }
        });
    }

    function updateExpenseProvided(row, prefix) {
        const valueInput = row.querySelector(`.${prefix}-value-input`);
        const typeSelect = row.querySelector(`.${prefix}-type-select`);
        const hasValue = valueInput && valueInput.value.trim() !== '';
        const hasType = typeSelect && typeSelect.value !== '';
        row.dataset[`${prefix}Provided`] = (hasValue || hasType) ? '1' : '0';
    }

    function resolveExpense(prefix, defaultsType, defaultsValue, row) {
        const select = row.querySelector(`.${prefix}-type-select`);
        const input = row.querySelector(`.${prefix}-value-input`);
        const provided = row.dataset[`${prefix}Provided`] === '1';
        let type = defaultsType;
        let value = defaultsValue !== null ? defaultsValue : 0;

        if (provided) {
            if (select && select.value !== '') {
                type = select.value;
            }
            if (input && input.value.trim() !== '') {
                value = parseNumber(input.value);
            } else if (defaultsValue === null) {
                value = 0;
            }
        } else if (select && select.value !== '') {
            type = select.value;
        }

        return { provided, type, value };
    }

    function getExpenseParts(row) {
        return {
            payment: resolveExpense('payment', pricingDefaults.payment_type, pricingDefaults.payment_value, row),
            logistics: resolveExpense('logistics', pricingDefaults.logistics_type, pricingDefaults.logistics_value, row),
            reviews: resolveExpense('reviews', pricingDefaults.reviews_type, pricingDefaults.reviews_value, row),
        };
    }

    function calculateRow(row) {
        const saleInput = row.querySelector('.sale-price-input');
        const profitInput = row.querySelector('.profit-percent-input');
        if (!saleInput) {
            return null;
        }

        const salePrice = parseNumber(saleInput.value);
        const purchaseCost = parseNumber(row.dataset.purchaseCost || '0');
        const commissionPercent = parseNumber(row.dataset.commissionPercent || '0');
        const commissionRate = commissionPercent / 100;
        const taxRate = pricingDefaults.tax_percent / 100;

        const expenses = getExpenseParts(row);

        const placementCost = salePrice * commissionRate;
        const paymentCost = expenses.payment.type === 'percent' ? salePrice * (expenses.payment.value / 100) : expenses.payment.value;
        const logisticsCost = expenses.logistics.type === 'percent' ? salePrice * (expenses.logistics.value / 100) : expenses.logistics.value;
        const reviewsCost = expenses.reviews.type === 'percent' ? salePrice * (expenses.reviews.value / 100) : expenses.reviews.value;
        const taxCost = salePrice * taxRate;

        const totalExpenses = purchaseCost + placementCost + paymentCost + logisticsCost + reviewsCost + taxCost;
        const profitAmount = salePrice - totalExpenses;
        const profitPercent = salePrice > 0 ? (profitAmount / salePrice) * 100 : 0;

        const profitAmountCell = row.querySelector('.profit-amount');
        const placementCell = row.querySelector('.placement-cost');
        const paymentCell = row.querySelector('.payment-cost');
        const logisticsCell = row.querySelector('.logistics-cost');
        const reviewsCell = row.querySelector('.reviews-cost');
        const totalExpensesCell = row.querySelector('.total-expenses');
        const profitInfo = row.querySelector('.profit-percent-info');
        const purchaseCell = row.querySelector('.purchase-cost');

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
        if (purchaseCell) {
            purchaseCell.textContent = formatCurrency(purchaseCost);
        }
        if (profitInfo) {
            const parts = [`Фактическая: ${profitPercent.toFixed(2)}%`];
            if (row.dataset.profitProvided !== '1' && pricingDefaults.profit_percent !== null) {
                parts.push(`По умолчанию ${pricingDefaults.profit_percent.toFixed(2)}%`);
            }
            profitInfo.textContent = parts.join(' · ');
        }

        return {
            salePrice,
            profitPercent,
            expenses,
        };
    }

    function updateSalePriceFromProfit(row) {
        const profitInput = row.querySelector('.profit-percent-input');
        if (!profitInput || row.dataset.profitProvided !== '1') {
            return;
        }

        const purchaseCost = parseNumber(row.dataset.purchaseCost || '0');
        const commissionPercent = parseNumber(row.dataset.commissionPercent || '0');
        const commissionRate = commissionPercent / 100;
        const taxRate = pricingDefaults.tax_percent / 100;
        const targetPercent = parseNullableNumber(profitInput.value);
        if (targetPercent === null) {
            return;
        }

        const expenses = getExpenseParts(row);
        const paymentRate = expenses.payment.type === 'percent' ? expenses.payment.value / 100 : 0;
        const logisticsRate = expenses.logistics.type === 'percent' ? expenses.logistics.value / 100 : 0;
        const reviewsRate = expenses.reviews.type === 'percent' ? expenses.reviews.value / 100 : 0;

        const fixedExpenses = purchaseCost
            + (expenses.payment.type === 'fixed' ? expenses.payment.value : 0)
            + (expenses.logistics.type === 'fixed' ? expenses.logistics.value : 0)
            + (expenses.reviews.type === 'fixed' ? expenses.reviews.value : 0);

        const percentExpenses = commissionRate + paymentRate + logisticsRate + reviewsRate + taxRate;
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

        const saleInput = row.querySelector('.sale-price-input');
        if (saleInput) {
            saleInput.value = salePrice.toFixed(2);
        }
    }

    function recalculateAll() {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row) => {
            calculateRow(row);
        });
    }

    function showItemsAlert(message, type) {
        if (!itemsAlertBox) {
            return;
        }
        itemsAlertBox.textContent = message;
        itemsAlertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        itemsAlertBox.classList.add('alert-' + type);
    }

    function clearItemsAlert() {
        if (!itemsAlertBox) {
            return;
        }
        itemsAlertBox.classList.add('d-none');
        itemsAlertBox.classList.remove('alert-success', 'alert-danger', 'alert-warning');
        itemsAlertBox.textContent = '';
    }

    function showDefaultsAlert(message, type) {
        if (!saveDefaultsButton || !defaultsAlertBox) {
            return;
        }
        defaultsAlertBox.textContent = message;
        defaultsAlertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        defaultsAlertBox.classList.add('alert-' + type);
    }

    function clearDefaultsAlert() {
        if (!defaultsAlertBox) {
            return;
        }
        defaultsAlertBox.classList.add('d-none');
        defaultsAlertBox.classList.remove('alert-success', 'alert-danger', 'alert-warning');
        defaultsAlertBox.textContent = '';
    }

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row) => {
        const saleInput = row.querySelector('.sale-price-input');
        if (saleInput) {
            saleInput.addEventListener('input', () => {
                calculateRow(row);
            });
        }

        const profitInput = row.querySelector('.profit-percent-input');
        if (profitInput) {
            profitInput.addEventListener('input', () => {
                const hasValue = profitInput.value.trim() !== '';
                row.dataset.profitProvided = hasValue ? '1' : '0';
                if (hasValue) {
                    updateSalePriceFromProfit(row);
                }
                calculateRow(row);
            });
        }

        ['payment', 'logistics', 'reviews'].forEach((prefix) => {
            const valueInput = row.querySelector(`.${prefix}-value-input`);
            const typeSelect = row.querySelector(`.${prefix}-type-select`);
            if (valueInput) {
                valueInput.addEventListener('input', () => {
                    updateExpenseProvided(row, prefix);
                    calculateRow(row);
                });
            }
            if (typeSelect) {
                typeSelect.addEventListener('change', () => {
                    updateExpenseProvided(row, prefix);
                    calculateRow(row);
                });
            }
        });
    });

    if (defaultControls.tax) {
        defaultControls.tax.addEventListener('input', () => {
            updateDefaultsFromInputs();
            recalculateAll();
        });
    }
    if (defaultControls.profit) {
        defaultControls.profit.addEventListener('input', () => {
            updateDefaultsFromInputs();
            recalculateAll();
        });
    }
    ['payment', 'logistics', 'reviews'].forEach((prefix) => {
        const valueKey = `${prefix}Value`;
        const typeKey = `${prefix}Type`;
        if (defaultControls[valueKey]) {
            defaultControls[valueKey].addEventListener('input', () => {
                updateDefaultsFromInputs();
                recalculateAll();
            });
        }
        if (defaultControls[typeKey]) {
            defaultControls[typeKey].addEventListener('change', () => {
                updateDefaultsFromInputs();
                recalculateAll();
            });
        }
    });

    updateDefaultsFromInputs();
    recalculateAll();
    if (saveDefaultsButton) {
        saveDefaultsButton.addEventListener('click', async () => {
            clearDefaultsAlert();

            if (!selectedSourceId) {
                showDefaultsAlert('Выберите источник заказа, чтобы сохранить значения по умолчанию.', 'warning');
                return;
            }

            updateDefaultsFromInputs();

            saveDefaultsButton.disabled = true;
            saveDefaultsButton.classList.add('disabled');

            try {
                const response = await fetch(saveDefaultsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        source_id: selectedSourceId,
                        defaults: {
                            tax_percent: pricingDefaults.tax_percent,
                            profit_percent: pricingDefaults.profit_percent,
                            payment_type: pricingDefaults.payment_type,
                            payment_value: pricingDefaults.payment_value,
                            logistics_type: pricingDefaults.logistics_type,
                            logistics_value: pricingDefaults.logistics_value,
                            reviews_type: pricingDefaults.reviews_type,
                            reviews_value: pricingDefaults.reviews_value,
                        },
                    }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();
                if (data && data.success) {
                    showDefaultsAlert('Значения по умолчанию сохранены.', 'success');
                } else {
                    showDefaultsAlert((data && data.error) ? data.error : 'Не удалось сохранить значения по умолчанию.', 'danger');
                }
            } catch (error) {
                showDefaultsAlert('Не удалось сохранить значения по умолчанию. Попробуйте еще раз.', 'danger');
            } finally {
                saveDefaultsButton.disabled = false;
                saveDefaultsButton.classList.remove('disabled');
            }
        });
    }

    if (itemsSaveButton) {
        itemsSaveButton.addEventListener('click', async () => {
            clearItemsAlert();

            if (!selectedSourceId) {
                showItemsAlert('Выберите источник заказа, чтобы сохранить данные.', 'warning');
                return;
            }

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            if (!rows.length) {
                showItemsAlert('Нет данных для сохранения.', 'warning');
                return;
            }

            const items = [];
            updateDefaultsFromInputs();
            rows.forEach((row) => {
                const productId = parseInt(row.dataset.productId || '0', 10);
                if (!productId) {
                    return;
                }

                calculateRow(row);

                const saleInput = row.querySelector('.sale-price-input');
                const profitInput = row.querySelector('.profit-percent-input');

                const salePrice = saleInput ? parseNumber(saleInput.value) : 0;
                const profitProvided = row.dataset.profitProvided === '1';
                const profitValue = profitProvided && profitInput ? parseNullableNumber(profitInput.value) : null;

                const paymentSelect = row.querySelector('.payment-type-select');
                const paymentInput = row.querySelector('.payment-value-input');
                const paymentProvided = row.dataset.paymentProvided === '1';
                const paymentType = paymentProvided && paymentSelect ? (paymentSelect.value !== '' ? paymentSelect.value : pricingDefaults.payment_type) : null;
                const paymentValue = paymentProvided && paymentInput ? parseNullableNumber(paymentInput.value) : null;

                const logisticsSelect = row.querySelector('.logistics-type-select');
                const logisticsInput = row.querySelector('.logistics-value-input');
                const logisticsProvided = row.dataset.logisticsProvided === '1';
                const logisticsType = logisticsProvided && logisticsSelect ? (logisticsSelect.value !== '' ? logisticsSelect.value : pricingDefaults.logistics_type) : null;
                const logisticsValue = logisticsProvided && logisticsInput ? parseNullableNumber(logisticsInput.value) : null;

                const reviewsSelect = row.querySelector('.reviews-type-select');
                const reviewsInput = row.querySelector('.reviews-value-input');
                const reviewsProvided = row.dataset.reviewsProvided === '1';
                const reviewsType = reviewsProvided && reviewsSelect ? (reviewsSelect.value !== '' ? reviewsSelect.value : pricingDefaults.reviews_type) : null;
                const reviewsValue = reviewsProvided && reviewsInput ? parseNullableNumber(reviewsInput.value) : null;

                items.push({
                    product_id: productId,
                    sale_price: salePrice,
                    profit_percent: profitValue,
                    payment_type: paymentType,
                    payment_value: paymentValue,
                    logistics_type: logisticsType,
                    logistics_value: logisticsValue,
                    reviews_type: reviewsType,
                    reviews_value: reviewsValue,
                });
            });

            itemsSaveButton.disabled = true;
            itemsSaveButton.classList.add('disabled');

            try {
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
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
                    showItemsAlert('Данные успешно сохранены.', 'success');
                } else {
                    showItemsAlert((data && data.error) ? data.error : 'Не удалось сохранить данные.', 'danger');
                }
            } catch (error) {
                showItemsAlert('Не удалось сохранить данные. Попробуйте еще раз.', 'danger');
            } finally {
                itemsSaveButton.disabled = false;
                itemsSaveButton.classList.remove('disabled');
            }
        });
    }
})();
</script>
