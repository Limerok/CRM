<?php
    $selectedSourceName = isset($selected_source_name) ? $selected_source_name : '';
    $hasSources = !empty($sources);
    $currentSourceId = isset($selected_source_id) ? (int)$selected_source_id : 0;
    $defaults = isset($pricing_defaults) && is_array($pricing_defaults) ? $pricing_defaults : array();
    $defaultTaxPercent = isset($defaults['tax_percent']) ? (float)$defaults['tax_percent'] : 0.0;
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
            </div>
            <p class="text-muted small mb-0 mt-3">Если у конкретного товара не указаны значения, будут использованы параметры по умолчанию.</p>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">В каталоге пока нет товаров для расчета.</div>
    <?php else: ?>
        <?php
            $pricingNameHints = array();
            $pricingBrandOptions = array();
            $pricingSeriesOptions = array();
            foreach ($products as $product) {
                if (!empty($product['name'])) {
                    $pricingNameHints[trim($product['name'])] = true;
                }
                if (!empty($product['manufacturer'])) {
                    $pricingBrandOptions[trim($product['manufacturer'])] = true;
                }
                if (!empty($product['series'])) {
                    $pricingSeriesOptions[trim($product['series'])] = true;
                }
            }
            $pricingNameHints = array_keys($pricingNameHints);
            sort($pricingNameHints, SORT_NATURAL | SORT_FLAG_CASE);
            $pricingBrandOptions = array_keys($pricingBrandOptions);
            sort($pricingBrandOptions, SORT_NATURAL | SORT_FLAG_CASE);
            $pricingSeriesOptions = array_keys($pricingSeriesOptions);
            sort($pricingSeriesOptions, SORT_NATURAL | SORT_FLAG_CASE);
        ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <p class="mb-0 text-muted">Изменяйте значения прямо в таблице &mdash; все расчеты обновляются мгновенно.</p>
                    <button type="button" class="btn btn-primary" id="pricing-save-button">Сохранить</button>
                </div>
                <div id="pricing-alert" class="alert d-none" role="alert"></div>
                <div class="bg-light border rounded p-3 mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Наименование</label>
                            <input type="text" class="form-control form-control-sm" id="pricing-filter-name" placeholder="Введите наименование" list="pricing-names-list">
                            <datalist id="pricing-names-list">
                                <?php foreach ($pricingNameHints as $hint): ?>
                                    <option value="<?= htmlspecialchars($hint); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label">Бренд</label>
                            <select class="form-select form-select-sm" id="pricing-filter-brand">
                                <option value="">Все бренды</option>
                                <?php foreach ($pricingBrandOptions as $brand): ?>
                                    <option value="<?= htmlspecialchars($brand); ?>"><?= htmlspecialchars($brand); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label">Серия</label>
                            <select class="form-select form-select-sm" id="pricing-filter-series">
                                <option value="">Все серии</option>
                                <?php foreach ($pricingSeriesOptions as $series): ?>
                                    <option value="<?= htmlspecialchars($series); ?>"><?= htmlspecialchars($series); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-2 d-grid">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="pricing-filter-reset">Сбросить</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="pricing-table" data-source-id="<?= $currentSourceId; ?>">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort-key="name">Наименование</th>
                                <th class="sortable" data-sort-key="brand">Бренд</th>
                                <th class="sortable" data-sort-key="series">Серия</th>
                                <th>Закупка (<?= htmlspecialchars($default_currency_code); ?>)</th>
                                <th style="width: 160px;">Цена продажи</th>
                                <th class="sortable" data-sort-key="profit" style="width: 200px;">Доходность %/<?= htmlspecialchars($default_currency_code); ?></th>
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
                                $paymentSelectValue = in_array($paymentType, array('percent', 'fixed'), true) ? $paymentType : 'percent';
                                $logisticsSelectValue = in_array($logisticsType, array('percent', 'fixed'), true) ? $logisticsType : 'percent';
                                $reviewsSelectValue = in_array($reviewsType, array('percent', 'fixed'), true) ? $reviewsType : 'percent';
                            ?>
                            <?php
                                $productNameValue = isset($product['name']) ? (string)$product['name'] : '';
                                $brandValue = !empty($product['manufacturer']) ? (string)$product['manufacturer'] : '';
                                $seriesValue = !empty($product['series']) ? (string)$product['series'] : '';
                            ?>
                            <tr
                                data-product-id="<?= (int)$product['id']; ?>"
                                data-purchase-cost="<?= htmlspecialchars(number_format($purchaseCost, 6, '.', '')); ?>"
                                data-commission-percent="<?= htmlspecialchars(number_format((float)$product['commission_percent'], 4, '.', '')); ?>"
                                data-profit-provided="<?= $hasProfitOverride ? '1' : '0'; ?>"
                                data-product-name="<?= htmlspecialchars($productNameValue); ?>"
                                data-brand="<?= htmlspecialchars($brandValue); ?>"
                                data-series="<?= htmlspecialchars($seriesValue); ?>"
                            >
                                <td><?= htmlspecialchars($productNameValue); ?></td>
                                <td><?= $brandValue !== '' ? htmlspecialchars($brandValue) : '—'; ?></td>
                                <td><?= $seriesValue !== '' ? htmlspecialchars($seriesValue) : '—'; ?></td>
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
                                            <input type="number" step="0.01" min="0" class="form-control text-end profit-percent-input" value="<?= $profitPercentValue; ?>">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="text-muted small profit-amount text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></div>
                                    </div>
                                </td>
                                <td class="placement-cost text-nowrap">0 <?= htmlspecialchars($default_currency_code); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end payment-value-input" value="<?= $paymentValue; ?>" placeholder="0.00">
                                        <select class="form-select form-select-sm payment-type-select">
                                            <option value="percent"<?= $paymentSelectValue === 'percent' ? ' selected' : ''; ?>>%</option>
                                            <option value="fixed"<?= $paymentSelectValue === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                                        </select>
                                    </div>
                                    <div class="text-muted small payment-cost mt-1">0 <?= htmlspecialchars($default_currency_code); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end logistics-value-input" value="<?= $logisticsValue; ?>" placeholder="0.00">
                                        <select class="form-select form-select-sm logistics-type-select">
                                            <option value="percent"<?= $logisticsSelectValue === 'percent' ? ' selected' : ''; ?>>%</option>
                                            <option value="fixed"<?= $logisticsSelectValue === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                                        </select>
                                    </div>
                                    <div class="text-muted small logistics-cost mt-1">0 <?= htmlspecialchars($default_currency_code); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end reviews-value-input" value="<?= $reviewsValue; ?>" placeholder="0.00">
                                        <select class="form-select form-select-sm reviews-type-select">
                                            <option value="percent"<?= $reviewsSelectValue === 'percent' ? ' selected' : ''; ?>>%</option>
                                            <option value="fixed"<?= $reviewsSelectValue === 'fixed' ? ' selected' : ''; ?>><?= htmlspecialchars($default_currency_code); ?></option>
                                        </select>
                                    </div>
                                    <div class="text-muted small reviews-cost mt-1">0 <?= htmlspecialchars($default_currency_code); ?></div>
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
    ), JSON_UNESCAPED_UNICODE); ?>;
    const table = document.getElementById('pricing-table');
    const itemsSaveButton = document.getElementById('pricing-save-button');
    const saveDefaultsButton = document.getElementById('pricing-save-defaults-button');
    const itemsAlertBox = document.getElementById('pricing-alert');
    const defaultsAlertBox = document.getElementById('pricing-defaults-alert');
    const pricingNameFilter = document.getElementById('pricing-filter-name');
    const pricingBrandFilter = document.getElementById('pricing-filter-brand');
    const pricingSeriesFilter = document.getElementById('pricing-filter-series');
    const pricingResetFilters = document.getElementById('pricing-filter-reset');

    const defaultControls = {
        tax: document.getElementById('default-tax-percent'),
    };

    const pricingDefaults = {
        tax_percent: typeof defaultsData.tax_percent === 'number' ? defaultsData.tax_percent : parseNumber(defaultsData.tax_percent),
    };

    if (!table) {
        return;
    }

    const pricingTbody = table.querySelector('tbody');
    const pricingSortHeaders = table.querySelectorAll('thead th[data-sort-key]');
    let pricingRows = Array.from(pricingTbody.querySelectorAll('tr'));
    let pricingSortState = { key: null, direction: 'asc' };

    function normalizeText(value) {
        return value ? String(value).trim().toLowerCase() : '';
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

    function applyPricingFilters() {
        const nameTerm = normalizeText(pricingNameFilter ? pricingNameFilter.value : '');
        const brandValue = normalizeText(pricingBrandFilter ? pricingBrandFilter.value : '');
        const seriesValue = normalizeText(pricingSeriesFilter ? pricingSeriesFilter.value : '');

        pricingRows.forEach((row) => {
            const rowName = normalizeText(row.dataset.productName || '');
            const rowBrand = normalizeText(row.dataset.brand || '');
            const rowSeries = normalizeText(row.dataset.series || '');

            let visible = true;

            if (nameTerm && !rowName.includes(nameTerm)) {
                visible = false;
            }

            if (visible && brandValue && rowBrand !== brandValue) {
                visible = false;
            }

            if (visible && seriesValue && rowSeries !== seriesValue) {
                visible = false;
            }

            row.classList.toggle('d-none', !visible);
        });
    }

    function getPricingSortValue(row, key) {
        switch (key) {
            case 'name':
                return normalizeText(row.dataset.productName || '');
            case 'brand':
                return normalizeText(row.dataset.brand || '');
            case 'series':
                return normalizeText(row.dataset.series || '');
            case 'profit': {
                const profitInput = row.querySelector('.profit-percent-input');
                const value = profitInput ? parseNullableNumber(profitInput.value) : null;
                return value !== null ? value : null;
            }
            default:
                return '';
        }
    }

    function comparePricingValues(aValue, bValue, key, direction) {
        const dirMultiplier = direction === 'desc' ? -1 : 1;
        if (key === 'profit') {
            const aNumber = typeof aValue === 'number' ? aValue : -Infinity;
            const bNumber = typeof bValue === 'number' ? bValue : -Infinity;
            if (aNumber === bNumber) {
                return 0;
            }
            return aNumber > bNumber ? dirMultiplier : -dirMultiplier;
        }

        const aText = aValue !== null && aValue !== undefined ? String(aValue) : '';
        const bText = bValue !== null && bValue !== undefined ? String(bValue) : '';
        const aEmpty = aText === '';
        const bEmpty = bText === '';

        if (aEmpty && bEmpty) {
            return 0;
        }
        if (aEmpty) {
            return direction === 'asc' ? 1 : -1;
        }
        if (bEmpty) {
            return direction === 'asc' ? -1 : 1;
        }

        return aText.localeCompare(bText, undefined, { sensitivity: 'base' }) * dirMultiplier;
    }

    function sortPricingRows(key, direction) {
        if (!key) {
            return;
        }

        const normalizedDirection = direction === 'desc' ? 'desc' : 'asc';
        const sorted = Array.from(pricingRows).sort((a, b) => {
            const aValue = getPricingSortValue(a, key);
            const bValue = getPricingSortValue(b, key);
            return comparePricingValues(aValue, bValue, key, normalizedDirection);
        });

        sorted.forEach((row) => {
            pricingTbody.appendChild(row);
        });

        pricingRows = sorted;
    }

    function updatePricingSortIndicators() {
        pricingSortHeaders.forEach((header) => {
            const headerKey = header.dataset.sortKey;
            if (pricingSortState.key === headerKey) {
                header.setAttribute('data-sort-direction', pricingSortState.direction);
                header.setAttribute('aria-sort', pricingSortState.direction === 'asc' ? 'ascending' : 'descending');
            } else {
                header.removeAttribute('data-sort-direction');
                header.removeAttribute('aria-sort');
            }
        });
    }

    function resortPricingIfNeeded() {
        if (pricingSortState.key) {
            sortPricingRows(pricingSortState.key, pricingSortState.direction);
            updatePricingSortIndicators();
            applyPricingFilters();
        }
    }

    function updateDefaultsFromInputs() {
        if (defaultControls.tax) {
            const value = parseNumber(defaultControls.tax.value);
            pricingDefaults.tax_percent = value >= 0 ? value : 0;
        }
    }

    function resolveExpense(prefix, row) {
        const select = row.querySelector(`.${prefix}-type-select`);
        const input = row.querySelector(`.${prefix}-value-input`);
        const type = select && select.value === 'fixed' ? 'fixed' : 'percent';
        const rawValue = parseNullableNumber(input ? input.value : null);
        return {
            type,
            value: rawValue !== null ? rawValue : 0,
            provided: rawValue !== null,
        };
    }

    function getExpenseParts(row) {
        return {
            payment: resolveExpense('payment', row),
            logistics: resolveExpense('logistics', row),
            reviews: resolveExpense('reviews', row),
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
        resortPricingIfNeeded();
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

    if (pricingNameFilter) {
        pricingNameFilter.addEventListener('input', () => {
            applyPricingFilters();
        });
    }

    if (pricingBrandFilter) {
        pricingBrandFilter.addEventListener('change', () => {
            applyPricingFilters();
        });
    }

    if (pricingSeriesFilter) {
        pricingSeriesFilter.addEventListener('change', () => {
            applyPricingFilters();
        });
    }

    if (pricingResetFilters) {
        pricingResetFilters.addEventListener('click', () => {
            if (pricingNameFilter) {
                pricingNameFilter.value = '';
            }
            if (pricingBrandFilter) {
                pricingBrandFilter.value = '';
            }
            if (pricingSeriesFilter) {
                pricingSeriesFilter.value = '';
            }
            applyPricingFilters();
        });
    }

    pricingSortHeaders.forEach((header) => {
        header.addEventListener('click', () => {
            const key = header.dataset.sortKey;
            if (!key) {
                return;
            }

            let direction = 'asc';
            if (pricingSortState.key === key) {
                direction = pricingSortState.direction === 'asc' ? 'desc' : 'asc';
            }

            pricingSortState = { key, direction };
            sortPricingRows(key, direction);
            updatePricingSortIndicators();
            applyPricingFilters();
        });
    });

    updatePricingSortIndicators();
    applyPricingFilters();

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row) => {
        const saleInput = row.querySelector('.sale-price-input');
        if (saleInput) {
            saleInput.addEventListener('input', () => {
                calculateRow(row);
                resortPricingIfNeeded();
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
                resortPricingIfNeeded();
            });
        }

        ['payment', 'logistics', 'reviews'].forEach((prefix) => {
            const valueInput = row.querySelector(`.${prefix}-value-input`);
            const typeSelect = row.querySelector(`.${prefix}-type-select`);
            if (valueInput) {
                valueInput.addEventListener('input', () => {
                    calculateRow(row);
                    resortPricingIfNeeded();
                });
            }
            if (typeSelect) {
                typeSelect.addEventListener('change', () => {
                    calculateRow(row);
                    resortPricingIfNeeded();
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

                const calculation = calculateRow(row);
                const saleInput = row.querySelector('.sale-price-input');
                const profitInput = row.querySelector('.profit-percent-input');

                const salePrice = calculation ? calculation.salePrice : (saleInput ? parseNumber(saleInput.value) : 0);
                const profitProvided = row.dataset.profitProvided === '1';
                const profitValue = profitProvided && profitInput ? parseNullableNumber(profitInput.value) : null;

                const expenses = calculation ? calculation.expenses : getExpenseParts(row);
                const paymentExpense = expenses.payment;
                const logisticsExpense = expenses.logistics;
                const reviewsExpense = expenses.reviews;

                items.push({
                    product_id: productId,
                    sale_price: salePrice,
                    profit_percent: profitValue,
                    payment_type: paymentExpense.provided ? paymentExpense.type : null,
                    payment_value: paymentExpense.provided ? paymentExpense.value : null,
                    logistics_type: logisticsExpense.provided ? logisticsExpense.type : null,
                    logistics_value: logisticsExpense.provided ? logisticsExpense.value : null,
                    reviews_type: reviewsExpense.provided ? reviewsExpense.type : null,
                    reviews_value: reviewsExpense.provided ? reviewsExpense.value : null,
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
