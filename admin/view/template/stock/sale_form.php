<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Продажа со склада</h1>
</div>
<div class="card shadow-sm p-4">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" id="sale-form">
        <div class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label class="form-label">Дата продажи</label>
                <input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-md-5 position-relative">
                <label class="form-label">Поиск товара</label>
                <input type="text" id="sale-product-search" class="form-control" placeholder="Введите название или модель">
                <div class="list-group position-absolute w-50" id="sale-product-suggestions" style="z-index: 1000;"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Количество</label>
                <input type="number" id="sale-product-quantity" class="form-control" value="1" min="1">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary" id="sale-add-product"><i class="bi bi-plus"></i> Добавить</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="sale-items">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Количество</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">Сохранить продажу</button>
        </div>
    </form>
</div>
<script>
const saleSearchInput = document.getElementById('sale-product-search');
const saleSuggestions = document.getElementById('sale-product-suggestions');
let saleSelectedProduct = null;

saleSearchInput.addEventListener('input', async (event) => {
    const term = event.target.value.trim();
    saleSuggestions.innerHTML = '';
    if (term.length < 2) {
        saleSelectedProduct = null;
        return;
    }
    const response = await fetch('<?= admin_url('stock/supply', array('action' => 'search')); ?>&term=' + encodeURIComponent(term));
    const items = await response.json();
    if (!items.length) {
        return;
    }
    saleSuggestions.innerHTML = items.map(item => `<button type="button" class="list-group-item list-group-item-action" data-id="${item.id}" data-name="${item.name}" data-model="${item.model}">${item.name} (${item.model})</button>`).join('');
});

saleSuggestions.addEventListener('click', (event) => {
    if (event.target.matches('button[data-id]')) {
        saleSelectedProduct = {
            id: event.target.dataset.id,
            name: event.target.dataset.name,
            model: event.target.dataset.model
        };
        saleSearchInput.value = `${saleSelectedProduct.name} (${saleSelectedProduct.model})`;
        saleSuggestions.innerHTML = '';
    }
});

document.getElementById('sale-add-product').addEventListener('click', () => {
    if (!saleSelectedProduct) {
        alert('Выберите товар из списка.');
        return;
    }
    const quantityField = document.getElementById('sale-product-quantity');
    const quantity = parseInt(quantityField.value, 10);
    if (!quantity || quantity <= 0) {
        alert('Количество должно быть больше нуля.');
        return;
    }
    const tbody = document.querySelector('#sale-items tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            ${saleSelectedProduct.name} (${saleSelectedProduct.model})
            <input type="hidden" name="product_ids[]" value="${saleSelectedProduct.id}">
        </td>
        <td>
            <input type="number" class="form-control" name="quantities[]" value="${quantity}" min="1" required>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger sale-remove-item"><i class="bi bi-x"></i></button>
        </td>
    `;
    tbody.appendChild(row);
    saleSearchInput.value = '';
    quantityField.value = 1;
    saleSelectedProduct = null;
});

document.querySelector('#sale-items tbody').addEventListener('click', (event) => {
    if (event.target.closest('.sale-remove-item')) {
        event.target.closest('tr').remove();
    }
});
</script>
