<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Категории</h1>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('catalog/category', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить категорию</a>
        <button type="submit" form="category-bulk-form" class="btn btn-danger" id="category-bulk-delete" disabled>
            <i class="bi bi-trash"></i> Удалить выбранные
        </button>
    </div>
</div>
<form method="post" action="<?= admin_url('catalog/category', array('action' => 'delete')); ?>" id="category-bulk-form" class="card shadow-sm" onsubmit="return confirm('Удалить выбранные категории?');">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 36px;">
                        <input type="checkbox" id="category-select-all" class="form-check-input">
                    </th>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Родитель</th>
                    <th>Сортировка</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($categories): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input category-bulk-checkbox" name="selected[]" value="<?= (int)$category['id']; ?>">
                        </td>
                        <td><?= (int)$category['id']; ?></td>
                        <td><?= htmlspecialchars($category['name']); ?></td>
                        <td><?= htmlspecialchars($category['parent_name']); ?></td>
                        <td><?= (int)$category['sort_order']; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('catalog/category', array('action' => 'form', 'id' => $category['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('catalog/category', array('action' => 'delete', 'id' => $category['id'])); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить категорию?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Категории не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>
<script>
(() => {
    const selectAll = document.getElementById('category-select-all');
    const checkboxes = Array.from(document.querySelectorAll('.category-bulk-checkbox'));
    const bulkDeleteButton = document.getElementById('category-bulk-delete');

    const updateBulkState = () => {
        const anyChecked = checkboxes.some((checkbox) => checkbox.checked);
        if (bulkDeleteButton) {
            bulkDeleteButton.disabled = !anyChecked;
        }
    };

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            updateBulkState();
        });
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateBulkState);
    });
})();
</script>
