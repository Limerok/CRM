<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Единицы веса</h1>
    <a href="<?= admin_url('system/weight', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить единицу</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Обозначение</th>
                    <th>Значение</th>
                    <th>Сортировка</th>
                    <th>Дата обновления</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($weight_classes): ?>
                <?php foreach ($weight_classes as $weightClass): ?>
                    <?php $isDefault = ($weightClass['id'] == $default_weight_class_id); ?>
                    <tr>
                        <td><?= (int)$weightClass['id']; ?></td>
                        <td><?= htmlspecialchars($weightClass['name']); ?><?= $isDefault ? ' (По умолчанию)' : ''; ?></td>
                        <td><?= htmlspecialchars($weightClass['code']); ?></td>
                        <td><?= rtrim(rtrim(number_format((float)$weightClass['value'], 8, '.', ''), '0'), '.'); ?></td>
                        <td><?= (int)$weightClass['sort_order']; ?></td>
                        <td><?= $weightClass['date_modified'] ? htmlspecialchars(date('d.m.Y H:i', strtotime($weightClass['date_modified']))) : ''; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('system/weight', array('action' => 'form', 'id' => $weightClass['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('system/weight', array('action' => 'delete', 'id' => $weightClass['id'])); ?>" class="btn btn-sm btn-outline-danger<?= $isDefault ? ' disabled' : ''; ?>"<?= $isDefault ? ' tabindex="-1" aria-disabled="true"' : ' onclick="return confirm(\'Удалить единицу веса?\');"'; ?>>Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Единицы веса не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
