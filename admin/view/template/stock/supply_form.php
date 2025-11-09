<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Создание поставки'; ?></h1>
    <a href="<?= admin_url('stock/supply', array('action' => 'history')); ?>" class="btn btn-secondary">История поставок</a>
</div>
<div class="card shadow-sm p-4">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" id="supply-form">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Дата поставки</label>
                <input type="date" name="supply_date" class="form-control" value="<?= htmlspecialchars(isset($supply['supply_date']) ? $supply['supply_date'] : date('Y-m-d')); ?>">
            </div>
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle" id="supply-items">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th style="width: 160px;">Количество</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($form_items)): ?>
                    <?php foreach ($form_items as $item): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['name']); ?> (<?= htmlspecialchars($item['model']); ?>)
                                <input type="hidden" name="product_ids[]" value="<?= (int)$item['product_id']; ?>">
                            </td>
                            <td><input type="number" class="form-control" name="quantities[]" value="<?= (int)$item['quantity']; ?>" min="1" required></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            <div class="position-relative">
                                <input type="hidden" id="supply-new-product-id">
                                <input type="text" id="supply-product-search" class="form-control" placeholder="Введите название или модель">
                                <div class="list-group position-absolute w-100" id="supply-product-suggestions" style="z-index: 1000;"></div>
                            </div>
                        </td>
                        <td><input type="number" id="supply-new-quantity" class="form-control" value="1" min="1"></td>
                        <td class="text-end"><button type="button" class="btn btn-primary" id="supply-add-product"><i class="bi bi-plus"></i> Добавить</button></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success"><?= isset($submit_label) ? htmlspecialchars($submit_label) : 'Сохранить поставку'; ?></button>
        </div>
    </form>
</div>
<script>
const supplySearchInput = document.getElementById('supply-product-search');
const supplySuggestions = document.getElementById('supply-product-suggestions');
const supplyNewProductId = document.getElementById('supply-new-product-id');
const supplyNewQuantity = document.getElementById('supply-new-quantity');
let supplySelectedProduct = null;

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

supplySearchInput.addEventListener('input', async (event) => {
    const term = event.target.value.trim();
    supplySelectedProduct = null;
    supplyNewProductId.value = '';
    supplySuggestions.innerHTML = '';
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
        supplySuggestions.innerHTML = items.map(item => {
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

supplySuggestions.addEventListener('click', (event) => {
    if (event.target.matches('button[data-id]')) {
        const button = event.target;
        const decodedName = button.dataset.name ? decodeURIComponent(button.dataset.name) : '';
        const decodedModel = button.dataset.model ? decodeURIComponent(button.dataset.model) : '';
        supplySelectedProduct = {
            id: button.dataset.id,
            name: decodedName,
            model: decodedModel
        };
        supplySearchInput.value = decodedName + (decodedModel ? ` (${decodedModel})` : '');
        supplyNewProductId.value = supplySelectedProduct.id;
        supplySuggestions.innerHTML = '';
    }
});

document.getElementById('supply-add-product').addEventListener('click', () => {
    if (!supplySelectedProduct || !supplySelectedProduct.id) {
        alert('Выберите товар из списка.');
        return;
    }
    const quantity = parseInt(supplyNewQuantity.value, 10);
    if (!quantity || quantity <= 0) {
        alert('Количество должно быть больше нуля.');
        return;
    }
    const tbody = document.querySelector('#supply-items tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            ${escapeHtml(supplySelectedProduct.name)}${supplySelectedProduct.model ? ' (' + escapeHtml(supplySelectedProduct.model) + ')' : ''}
            <input type="hidden" name="product_ids[]" value="${supplySelectedProduct.id}">
        </td>
        <td><input type="number" class="form-control" name="quantities[]" value="${quantity}" min="1" required></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
    `;
    tbody.appendChild(row);
    supplySearchInput.value = '';
    supplyNewProductId.value = '';
    supplySuggestions.innerHTML = '';
    supplyNewQuantity.value = 1;
    supplySelectedProduct = null;
});

document.querySelector('#supply-items tbody').addEventListener('click', (event) => {
    if (event.target.closest('.remove-item')) {
        event.target.closest('tr').remove();
    }
});
</script>
