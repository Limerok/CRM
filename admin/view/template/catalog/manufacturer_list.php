<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Производители</h1>
    <div class="d-flex gap-2">
        <a href="<?= admin_url('catalog/manufacturer', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить производителя</a>
        <button type="submit" form="manufacturer-bulk-form" class="btn btn-danger" id="manufacturer-bulk-delete" disabled>
            <i class="bi bi-trash"></i> Удалить выбранные
        </button>
    </div>
</div>
<form method="post" action="<?= admin_url('catalog/manufacturer', array('action' => 'delete')); ?>" id="manufacturer-bulk-form" class="card shadow-sm" onsubmit="return confirm('Удалить выбранных производителей?');">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 36px;">
                        <input type="checkbox" id="manufacturer-select-all" class="form-check-input">
                    </th>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Сортировка</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($manufacturers): ?>
                <?php foreach ($manufacturers as $manufacturer): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input manufacturer-bulk-checkbox" name="selected[]" value="<?= (int)$manufacturer['id']; ?>">
                        </td>
                        <td><?= (int)$manufacturer['id']; ?></td>
                        <td><?= htmlspecialchars($manufacturer['name']); ?></td>
                        <td><?= (int)$manufacturer['sort_order']; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('catalog/manufacturer', array('action' => 'form', 'id' => $manufacturer['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('catalog/manufacturer', array('action' => 'delete', 'id' => $manufacturer['id'])); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить производителя?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Производители не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>
<script>
(() => {
    const selectAll = document.getElementById('manufacturer-select-all');
    const checkboxes = Array.from(document.querySelectorAll('.manufacturer-bulk-checkbox'));
    const bulkDeleteButton = document.getElementById('manufacturer-bulk-delete');

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
