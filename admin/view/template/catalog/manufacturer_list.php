<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Производители</h1>
    <a href="<?= admin_url('catalog/manufacturer', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить производителя</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
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
</div>
