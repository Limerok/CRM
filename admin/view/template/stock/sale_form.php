<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= htmlspecialchars(isset($page_title) ? $page_title : 'Продажа со склада'); ?></h1>
    <?php if (isset($page_title) && $page_title !== 'Продажа со склада'): ?>
        <a href="<?= admin_url('stock/sale'); ?>" class="btn btn-secondary">Новая продажа</a>
    <?php endif; ?>
</div>
<div class="card shadow-sm p-4">
    <?php if (!empty($errors_list)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors_list as $message): ?>
                    <li><?= htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (empty($errors_list) && !empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($allow_negative_stock)): ?>
        <div class="alert alert-warning">
            Включено оформление заказов при нехватке товара на складе. Количество может уйти в отрицательное значение при сохранении продажи.
        </div>
    <?php endif; ?>
    <?php
        $statuses = isset($statuses) && is_array($statuses) ? $statuses : array();
        $defaultStatusName = isset($default_status_name) ? $default_status_name : '';
        $isMultiSale = !empty($is_multi_sale);
        $selectedSaleDate = isset($selected_sale_date) && $selected_sale_date !== '' ? $selected_sale_date : date('Y-m-d');
    ?>
    <form method="post" action="<?= htmlspecialchars(isset($form_action) ? $form_action : admin_url('stock/sale')); ?>" id="sale-form">
        <input type="hidden" name="is_multi_sale" value="0">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="sale-multi-sale" name="is_multi_sale" value="1"<?= $isMultiSale ? ' checked' : ''; ?>>
            <label class="form-check-label" for="sale-multi-sale">Мульти продажа</label>
        </div>
        <div id="sale-items" class="d-flex flex-column gap-3 mb-4">
            <?php if (!empty($form_items)): ?>
                <?php foreach ($form_items as $item): ?>
                    <?php
                        $statusValue = isset($item['order_status']) ? (string)$item['order_status'] : '';
                        $statusMatched = false;
                        $sellerPriceValue = isset($item['seller_price']) && $item['seller_price'] !== null ? number_format((float)$item['seller_price'], 2, '.', '') : '';
                        $sourceSalePriceValue = isset($item['source_sale_price']) && $item['source_sale_price'] !== null ? number_format((float)$item['source_sale_price'], 2, '.', '') : '';
                        $saleDateValue = isset($item['sale_date']) && $item['sale_date'] !== '' ? $item['sale_date'] : $selectedSaleDate;
                    ?>
                    <div class="sale-item border rounded p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="text-muted small">Товар</div>
                                <div class="fw-semibold"><?= htmlspecialchars($item['product_name']); ?><?php if (!empty($item['product_model'])): ?> (<?= htmlspecialchars($item['product_model']); ?>)<?php endif; ?></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger sale-remove-item"><i class="bi bi-x"></i></button>
                        </div>
                        <input type="hidden" name="product_ids[]" value="<?= (int)$item['product_id']; ?>">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Дата заказа</label>
                                <input type="date" class="form-control" name="order_dates[]" value="<?= htmlspecialchars($item['order_date']); ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Дата продажи</label>
                                <input type="date" class="form-control" name="sale_dates[]" value="<?= htmlspecialchars($saleDateValue); ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Источник</label>
                                <select name="source_ids[]" class="form-select">
                                    <option value="">Не выбрано</option>
                                    <?php if (!empty($sources)): ?>
                                        <?php foreach ($sources as $source): ?>
                                            <?php $selected = isset($item['source_id']) && $item['source_id'] !== null && (int)$item['source_id'] === (int)$source['id']; ?>
                                            <option value="<?= (int)$source['id']; ?>"<?= $selected ? ' selected' : ''; ?>><?= htmlspecialchars($source['name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-12 col-md-4">
                                <label class="form-label">№ задания</label>
                                <input type="text" class="form-control" name="task_numbers[]" value="<?= htmlspecialchars(isset($item['task_number']) ? $item['task_number'] : ''); ?>" placeholder="Введите № задания">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">№ заказа</label>
                                <input type="text" class="form-control" name="order_numbers[]" value="<?= htmlspecialchars(isset($item['order_number']) ? $item['order_number'] : ''); ?>" placeholder="Введите № заказа">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Источник реализовал</label>
                                <input type="number" step="0.01" min="0" class="form-control text-end" name="source_sale_prices[]" value="<?= htmlspecialchars($sourceSalePriceValue); ?>" placeholder="0.00">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Цена продавца</label>
                                <input type="number" step="0.01" min="0" class="form-control text-end" name="seller_prices[]" value="<?= htmlspecialchars($sellerPriceValue); ?>" placeholder="0.00">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Статус заказа</label>
                                <select name="order_statuses[]" class="form-select">
                                    <option value="">Не выбрано</option>
                                    <?php if (!empty($statuses)): ?>
                                        <?php foreach ($statuses as $status): ?>
                                            <?php $isSelectedStatus = ($statusValue !== '' && $statusValue === $status['name']); ?>
                                            <?php if ($isSelectedStatus) { $statusMatched = true; } ?>
                                            <option value="<?= htmlspecialchars($status['name']); ?>"<?= $isSelectedStatus ? ' selected' : ''; ?>><?= htmlspecialchars($status['name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if ($statusValue !== '' && !$statusMatched): ?>
                                        <option value="<?= htmlspecialchars($statusValue); ?>" selected><?= htmlspecialchars($statusValue); ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="bg-light border rounded p-3 mb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <label class="form-label">Товар</label>
                    <div class="position-relative">
                        <input type="hidden" id="sale-new-product-id">
                        <input type="text" id="sale-product-search" class="form-control" placeholder="Введите название или модель">
                        <div class="list-group position-absolute w-100" id="sale-product-suggestions" style="z-index: 1000;"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">Дата заказа</label>
                    <input type="date" id="sale-new-order-date" class="form-control" value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">Дата продажи</label>
                    <input type="date" id="sale-new-sale-date" class="form-control" value="<?= htmlspecialchars($selectedSaleDate); ?>">
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">Источник</label>
                    <select id="sale-new-source" class="form-select">
                        <option value="">Не выбрано</option>
                        <?php if (!empty($sources)): ?>
                            <?php foreach ($sources as $source): ?>
                                <option value="<?= (int)$source['id']; ?>"><?= htmlspecialchars($source['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-0">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label">№ задания</label>
                    <input type="text" id="sale-new-task" class="form-control" placeholder="№ задания">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label">№ заказа</label>
                    <input type="text" id="sale-new-order" class="form-control" placeholder="№ заказа">
                </div>
            </div>
            <div class="row g-3 mt-0">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label">Источник реализовал</label>
                    <input type="number" step="0.01" min="0" id="sale-new-source-price" class="form-control text-end" placeholder="0.00">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label">Цена продавца</label>
                    <input type="number" step="0.01" min="0" id="sale-new-seller-price" class="form-control text-end" placeholder="0.00">
                </div>
            </div>
            <div class="row g-3 mt-0 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label">Статус заказа</label>
                    <select id="sale-new-status" class="form-select">
                        <option value="">Не выбрано</option>
                        <?php if (!empty($statuses)): ?>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= htmlspecialchars($status['name']); ?>"<?= ($defaultStatusName !== '' && $defaultStatusName === $status['name']) ? ' selected' : ''; ?>><?= htmlspecialchars($status['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-auto d-grid d-lg-flex justify-content-lg-end">
                    <button type="button" class="btn btn-primary mt-3 mt-lg-0" id="sale-add-product"><i class="bi bi-plus"></i> Добавить</button>
                </div>
            </div>
        </div>
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success"><?= htmlspecialchars(isset($submit_label) ? $submit_label : 'Сохранить продажу'); ?></button>
        </div>
        <?php if (isset($recent_sales)): ?>
        <div>
            <h5 class="mb-3">Последние продажи</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Дата продажи</th>
                            <th>Позиций</th>
                            <th>Создано</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($recent_sales)): ?>
                        <?php foreach ($recent_sales as $saleItem): ?>
                            <tr>
                                <td><?= (int)$saleItem['id']; ?></td>
                                <td><?= htmlspecialchars($saleItem['sale_date']); ?></td>
                                <td><?= (int)$saleItem['items_count']; ?></td>
                                <td><?= htmlspecialchars($saleItem['created_at']); ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= admin_url('stock/sale', array('action' => 'view', 'id' => $saleItem['id'])); ?>" class="btn btn-outline-secondary">Подробнее</a>
                                        <a href="<?= admin_url('stock/sale', array('action' => 'edit', 'id' => $saleItem['id'])); ?>" class="btn btn-outline-primary">Редактировать</a>
                                        <button type="submit"
                                            class="btn btn-outline-danger"
                                            formaction="<?= admin_url('stock/sale', array('action' => 'delete')); ?>"
                                            formmethod="post"
                                            name="sale_id"
                                            value="<?= (int)$saleItem['id']; ?>"
                                            formnovalidate
                                            onclick="return confirm('Удалить продажу #<?= (int)$saleItem['id']; ?>?');">Удалить</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Продажи ещё не зафиксированы</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>
<?php
$sourcesForJs = array();
if (!empty($sources)) {
    foreach ($sources as $source) {
        $sourcesForJs[] = array('id' => (int)$source['id'], 'name' => $source['name']);
    }
}
$statusesForJs = array();
if (!empty($statuses)) {
    foreach ($statuses as $status) {
        $statusesForJs[] = array('name' => $status['name']);
    }
}
$defaultStatusForJs = isset($defaultStatusName) ? $defaultStatusName : '';
?>
<script>
const saleSearchInput = document.getElementById('sale-product-search');
const saleSuggestions = document.getElementById('sale-product-suggestions');
const saleNewProductId = document.getElementById('sale-new-product-id');
const saleNewOrderDate = document.getElementById('sale-new-order-date');
const saleNewSaleDate = document.getElementById('sale-new-sale-date');
const saleNewSource = document.getElementById('sale-new-source');
const saleNewStatus = document.getElementById('sale-new-status');
const saleNewTask = document.getElementById('sale-new-task');
const saleNewOrder = document.getElementById('sale-new-order');
const saleNewSellerPrice = document.getElementById('sale-new-seller-price');
const saleNewSourcePrice = document.getElementById('sale-new-source-price');
const saleItemsContainer = document.getElementById('sale-items');
const saleMultiSale = document.getElementById('sale-multi-sale');
let saleSelectedProduct = null;
const saleSources = <?= json_encode($sourcesForJs, JSON_UNESCAPED_UNICODE); ?>;
const saleStatuses = <?= json_encode($statusesForJs, JSON_UNESCAPED_UNICODE); ?>;
const saleDefaultStatus = <?= json_encode($defaultStatusForJs, JSON_UNESCAPED_UNICODE); ?>;

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

saleSearchInput.addEventListener('input', async (event) => {
    const term = event.target.value.trim();
    saleSelectedProduct = null;
    saleNewProductId.value = '';
    saleSuggestions.innerHTML = '';
    if (term.length < 2) {
        return;
    }
    try {
        const response = await fetch('<?= admin_url('stock/supply', array('action' => 'search')); ?>&term=' + encodeURIComponent(term));
        if (!response.ok) {
            return;
        }
        const items = await response.json();
        if (!Array.isArray(items) || !items.length) {
            return;
        }
        saleSuggestions.innerHTML = items.map(item => {
            const displayName = escapeHtml(item.name);
            const displayModel = item.model ? escapeHtml(item.model) : '';
            const encodedName = encodeURIComponent(item.name);
            const encodedModel = item.model ? encodeURIComponent(item.model) : '';
            return `<button type="button" class="list-group-item list-group-item-action" data-id="${item.id}" data-name="${encodedName}" data-model="${encodedModel}">${displayName}${displayModel ? ' (' + displayModel + ')' : ''}</button>`;
        }).join('');
    } catch (error) {
        console.error(error);
    }
});

saleSuggestions.addEventListener('click', (event) => {
    if (event.target.matches('button[data-id]')) {
        const button = event.target;
        const decodedName = button.dataset.name ? decodeURIComponent(button.dataset.name) : '';
        const decodedModel = button.dataset.model ? decodeURIComponent(button.dataset.model) : '';
        saleSelectedProduct = {
            id: button.dataset.id,
            name: decodedName,
            model: decodedModel
        };
        saleSearchInput.value = decodedName + (decodedModel ? ` (${decodedModel})` : '');
        saleNewProductId.value = saleSelectedProduct.id;
        saleSuggestions.innerHTML = '';
    }
});

function buildSourceOptions(selectedValue = '') {
    const options = ['<option value="">Не выбрано</option>'];
    saleSources.forEach((source) => {
        const isSelected = selectedValue !== '' && String(source.id) === String(selectedValue);
        options.push(`<option value="${source.id}"${isSelected ? ' selected' : ''}>${escapeHtml(source.name)}</option>`);
    });
    return options.join('');
}

function buildStatusOptions(selectedValue = '') {
    const options = ['<option value="">Не выбрано</option>'];
    let matched = false;
    saleStatuses.forEach((status) => {
        const isSelected = selectedValue !== '' && status.name === selectedValue;
        if (isSelected) {
            matched = true;
        }
        options.push(`<option value="${escapeHtml(status.name)}"${isSelected ? ' selected' : ''}>${escapeHtml(status.name)}</option>`);
    });
    if (selectedValue && !matched) {
        options.push(`<option value="${escapeHtml(selectedValue)}" selected>${escapeHtml(selectedValue)}</option>`);
    }
    return options.join('');
}

document.getElementById('sale-add-product').addEventListener('click', () => {
    if (!saleSelectedProduct || !saleSelectedProduct.id) {
        alert('Выберите товар из списка.');
        return;
    }

    if (saleMultiSale && !saleMultiSale.checked) {
        const existingItems = saleItemsContainer.querySelectorAll('.sale-item').length;
        if (existingItems >= 1) {
            alert('Для одиночной продажи можно добавить только один товар.');
            return;
        }
    }

    const orderDate = saleNewOrderDate.value;
    if (!orderDate) {
        alert('Укажите дату заказа.');
        return;
    }

    const saleDate = saleNewSaleDate ? saleNewSaleDate.value : '';
    if (!saleDate) {
        alert('Укажите дату продажи.');
        return;
    }

    const statusValue = saleNewStatus.value;
    const taskValue = saleNewTask.value.trim();
    const orderValue = saleNewOrder.value.trim();
    const sourceValue = saleNewSource.value;
    const sellerPriceValue = saleNewSellerPrice.value.trim();
    const sourcePriceValue = saleNewSourcePrice.value.trim();

    const wrapper = document.createElement('div');
    wrapper.className = 'sale-item border rounded p-3';
    wrapper.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="text-muted small">Товар</div>
                <div class="fw-semibold">${escapeHtml(saleSelectedProduct.name)}${saleSelectedProduct.model ? ' (' + escapeHtml(saleSelectedProduct.model) + ')' : ''}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger sale-remove-item"><i class="bi bi-x"></i></button>
        </div>
        <input type="hidden" name="product_ids[]" value="${saleSelectedProduct.id}">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Дата заказа</label>
                <input type="date" class="form-control" name="order_dates[]" value="${escapeHtml(orderDate)}" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Дата продажи</label>
                <input type="date" class="form-control" name="sale_dates[]" value="${escapeHtml(saleDate)}" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Источник</label>
                <select name="source_ids[]" class="form-select">
                    ${buildSourceOptions(sourceValue)}
                </select>
            </div>
        </div>
        <div class="row g-3 mt-0">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">№ задания</label>
                <input type="text" class="form-control" name="task_numbers[]" value="${escapeHtml(taskValue)}" placeholder="№ задания">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">№ заказа</label>
                <input type="text" class="form-control" name="order_numbers[]" value="${escapeHtml(orderValue)}" placeholder="№ заказа">
            </div>
        </div>
        <div class="row g-3 mt-0">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">Источник реализовал</label>
                <input type="number" step="0.01" min="0" class="form-control text-end" name="source_sale_prices[]" value="${escapeHtml(sourcePriceValue)}" placeholder="0.00">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">Цена продавца</label>
                <input type="number" step="0.01" min="0" class="form-control text-end" name="seller_prices[]" value="${escapeHtml(sellerPriceValue)}" placeholder="0.00">
            </div>
        </div>
        <div class="row g-3 mt-0">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label">Статус заказа</label>
                <select name="order_statuses[]" class="form-select">
                    ${buildStatusOptions(statusValue || saleDefaultStatus || '')}
                </select>
            </div>
        </div>
    `;

    saleItemsContainer.appendChild(wrapper);

    saleSearchInput.value = '';
    saleNewProductId.value = '';
    saleSuggestions.innerHTML = '';
    saleNewOrderDate.value = orderDate;
    if (saleNewSaleDate) {
        saleNewSaleDate.value = saleDate;
    }
    saleNewSource.value = sourceValue;
    saleNewStatus.value = statusValue || (saleDefaultStatus || '');
    saleNewTask.value = '';
    saleNewOrder.value = '';
    saleNewSellerPrice.value = '';
    saleNewSourcePrice.value = '';
    saleSelectedProduct = null;
});

saleItemsContainer.addEventListener('click', (event) => {
    if (event.target.closest('.sale-remove-item')) {
        event.target.closest('.sale-item').remove();
    }
});

if (saleMultiSale) {
    saleMultiSale.addEventListener('change', () => {
        if (!saleMultiSale.checked) {
            const itemsCount = saleItemsContainer.querySelectorAll('.sale-item').length;
            if (itemsCount > 1) {
                alert('Для одиночной продажи оставьте только один товар.');
            }
        }
    });
}
</script>

