<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Создание поставки'; ?></h1>
    <a href="<?= admin_url('stock/supply', array('action' => 'history')); ?>" class="btn btn-secondary">История поставок</a>
</div>
<div class="card shadow-sm p-4">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" id="supply-form">
        <div class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label class="form-label">Дата поставки</label>
                <input type="date" name="supply_date" class="form-control" value="<?= htmlspecialchars(isset($supply['supply_date']) ? $supply['supply_date'] : date('Y-m-d')); ?>">
            </div>
            <div class="col-md-5 position-relative">
                <label class="form-label">Поиск товара</label>
                <input type="text" id="product-search" class="form-control" placeholder="Введите название или модель">
                <div class="list-group position-absolute w-50" id="product-suggestions" style="z-index: 1000;"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Количество</label>
                <input type="number" id="product-quantity" class="form-control" value="1" min="1">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary" id="add-product"><i class="bi bi-plus"></i> Добавить</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="supply-items">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Количество</th>
                        <th></th>
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
                            <td>
                                <input type="number" class="form-control" name="quantities[]" value="<?= (int)$item['quantity']; ?>" min="1" required>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success"><?= isset($submit_label) ? htmlspecialchars($submit_label) : 'Сохранить поставку'; ?></button>
        </div>
    </form>
</div>
<script>
const searchInput = document.getElementById('product-search');
const suggestions = document.getElementById('product-suggestions');
let selectedProduct = null;

searchInput.addEventListener('input', async (event) => {
    const term = event.target.value.trim();
    suggestions.innerHTML = '';
    if (term.length < 2) {
        selectedProduct = null;
        return;
    }
    const response = await fetch('<?= admin_url('stock/supply', array('action' => 'search')); ?>&term=' + encodeURIComponent(term));
    const items = await response.json();
    if (!items.length) {
        return;
    }
    suggestions.innerHTML = items.map(item => `<button type="button" class="list-group-item list-group-item-action" data-id="${item.id}" data-name="${item.name}" data-model="${item.model}">${item.name} (${item.model})</button>`).join('');
});

suggestions.addEventListener('click', (event) => {
    if (event.target.matches('button[data-id]')) {
        selectedProduct = {
            id: event.target.dataset.id,
            name: event.target.dataset.name,
            model: event.target.dataset.model
        };
        searchInput.value = `${selectedProduct.name} (${selectedProduct.model})`;
        suggestions.innerHTML = '';
    }
});

document.getElementById('add-product').addEventListener('click', () => {
    if (!selectedProduct) {
        alert('Выберите товар из списка.');
        return;
    }
    const quantityField = document.getElementById('product-quantity');
    const quantity = parseInt(quantityField.value, 10);
    if (!quantity || quantity <= 0) {
        alert('Количество должно быть больше нуля.');
        return;
    }
    const tbody = document.querySelector('#supply-items tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            ${selectedProduct.name} (${selectedProduct.model})
            <input type="hidden" name="product_ids[]" value="${selectedProduct.id}">
        </td>
        <td>
            <input type="number" class="form-control" name="quantities[]" value="${quantity}" min="1" required>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button>
        </td>
    `;
    tbody.appendChild(row);
    searchInput.value = '';
    quantityField.value = 1;
    selectedProduct = null;
});

document.querySelector('#supply-items tbody').addEventListener('click', (event) => {
    if (event.target.closest('.remove-item')) {
        event.target.closest('tr').remove();
    }
});
</script>
