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
    ?>
    <form method="post" action="<?= htmlspecialchars(isset($form_action) ? $form_action : admin_url('stock/sale')); ?>" id="sale-form">
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle" id="sale-items">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th style="width: 160px;">Дата заказа</th>
                        <th style="width: 200px;">Источник</th>
                        <th style="width: 200px;">Статус заказа</th>
                        <th style="width: 160px;">№ задания</th>
                        <th style="width: 160px;">№ заказа</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($form_items)): ?>
                    <?php foreach ($form_items as $item): ?>
                        <?php
                            $statusValue = isset($item['order_status']) ? (string)$item['order_status'] : '';
                            $statusMatched = false;
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['product_name']); ?><?php if (!empty($item['product_model'])): ?> (<?= htmlspecialchars($item['product_model']); ?>)<?php endif; ?>
                                <input type="hidden" name="product_ids[]" value="<?= (int)$item['product_id']; ?>">
                            </td>
                            <td><input type="date" class="form-control" name="order_dates[]" value="<?= htmlspecialchars($item['order_date']); ?>" required></td>
                            <td>
                                <select name="source_ids[]" class="form-select">
                                    <option value="">Не выбрано</option>
                                    <?php if (!empty($sources)): ?>
                                        <?php foreach ($sources as $source): ?>
                                            <?php $selected = isset($item['source_id']) && $item['source_id'] !== null && (int)$item['source_id'] === (int)$source['id']; ?>
                                            <option value="<?= (int)$source['id']; ?>"<?= $selected ? ' selected' : ''; ?>><?= htmlspecialchars($source['name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
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
                            </td>
                            <td><input type="text" class="form-control" name="task_numbers[]" value="<?= htmlspecialchars(isset($item['task_number']) ? $item['task_number'] : ''); ?>" placeholder="Введите № задания"></td>
                            <td><input type="text" class="form-control" name="order_numbers[]" value="<?= htmlspecialchars(isset($item['order_number']) ? $item['order_number'] : ''); ?>" placeholder="Введите № заказа"></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger sale-remove-item"><i class="bi bi-x"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-light border rounded p-3 mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label">Товар</label>
                    <div class="position-relative">
                        <input type="hidden" id="sale-new-product-id">
                        <input type="text" id="sale-product-search" class="form-control" placeholder="Введите название или модель">
                        <div class="list-group position-absolute w-100" id="sale-product-suggestions" style="z-index: 1000;"></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label">Дата заказа</label>
                    <input type="date" id="sale-new-order-date" class="form-control" value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-6 col-md-4 col-lg-2">
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
                <div class="col-6 col-md-4 col-lg-2">
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
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label">№ задания</label>
                    <input type="text" id="sale-new-task" class="form-control" placeholder="№ задания">
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label">№ заказа</label>
                    <input type="text" id="sale-new-order" class="form-control" placeholder="№ заказа">
                </div>
                <div class="col-12 col-lg-auto d-grid d-lg-flex justify-content-lg-end">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <button type="button" class="btn btn-primary" id="sale-add-product"><i class="bi bi-plus"></i> Добавить</button>
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
                                        <form method="post" action="<?= admin_url('stock/sale', array('action' => 'delete')); ?>" class="d-inline">
                                            <input type="hidden" name="sale_id" value="<?= (int)$saleItem['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Удалить продажу #<?= (int)$saleItem['id']; ?>?');">Удалить</button>
                                        </form>
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
const saleNewSource = document.getElementById('sale-new-source');
const saleNewStatus = document.getElementById('sale-new-status');
const saleNewTask = document.getElementById('sale-new-task');
const saleNewOrder = document.getElementById('sale-new-order');
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

function buildSourceOptions() {
    const options = ['<option value="">Не выбрано</option>'];
    saleSources.forEach((source) => {
        options.push(`<option value="${source.id}">${escapeHtml(source.name)}</option>`);
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

    const orderDate = saleNewOrderDate.value;
    if (!orderDate) {
        alert('Укажите дату заказа.');
        return;
    }

    const tbody = document.querySelector('#sale-items tbody');
    const row = document.createElement('tr');
    const statusValue = saleNewStatus.value;
    const taskValue = saleNewTask.value.trim();
    const orderValue = saleNewOrder.value.trim();
    const sourceValue = saleNewSource.value;

    row.innerHTML = `
        <td>
            ${escapeHtml(saleSelectedProduct.name)}${saleSelectedProduct.model ? ' (' + escapeHtml(saleSelectedProduct.model) + ')' : ''}
            <input type="hidden" name="product_ids[]" value="${saleSelectedProduct.id}">
        </td>
        <td><input type="date" class="form-control" name="order_dates[]" value="${escapeHtml(orderDate)}" required></td>
        <td>
            <select name="source_ids[]" class="form-select">
                ${buildSourceOptions()}
            </select>
        </td>
        <td>
            <select name="order_statuses[]" class="form-select">
                ${buildStatusOptions(statusValue)}
            </select>
        </td>
        <td><input type="text" class="form-control" name="task_numbers[]" value="${escapeHtml(taskValue)}" placeholder="Введите № задания"></td>
        <td><input type="text" class="form-control" name="order_numbers[]" value="${escapeHtml(orderValue)}" placeholder="Введите № заказа"></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger sale-remove-item"><i class="bi bi-x"></i></button></td>
    `;

    tbody.appendChild(row);

    const select = row.querySelector('select[name="source_ids[]"]');
    if (select && sourceValue) {
        select.value = sourceValue;
    }

    saleSearchInput.value = '';
    saleNewProductId.value = '';
    saleSuggestions.innerHTML = '';
    saleNewOrderDate.value = orderDate;
    saleNewSource.value = sourceValue;
    if (statusValue) {
        saleNewStatus.value = statusValue;
    } else if (saleDefaultStatus) {
        saleNewStatus.value = saleDefaultStatus;
    } else {
        saleNewStatus.value = '';
    }
    saleNewTask.value = '';
    saleNewOrder.value = '';
    saleSelectedProduct = null;
});

document.querySelector('#sale-items tbody').addEventListener('click', (event) => {
    if (event.target.closest('.sale-remove-item')) {
        event.target.closest('tr').remove();
    }
});
</script>
