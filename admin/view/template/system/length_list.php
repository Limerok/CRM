<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Единицы длины</h1>
    <a href="<?= admin_url('system/length', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить единицу</a>
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
            <?php if ($length_classes): ?>
                <?php foreach ($length_classes as $lengthClass): ?>
                    <?php $isDefault = ($lengthClass['id'] == $default_length_class_id); ?>
                    <tr>
                        <td><?= (int)$lengthClass['id']; ?></td>
                        <td><?= htmlspecialchars($lengthClass['name']); ?><?= $isDefault ? ' (По умолчанию)' : ''; ?></td>
                        <td><?= htmlspecialchars($lengthClass['code']); ?></td>
                        <td><?= rtrim(rtrim(number_format((float)$lengthClass['value'], 8, '.', ''), '0'), '.'); ?></td>
                        <td><?= (int)$lengthClass['sort_order']; ?></td>
                        <td><?= $lengthClass['date_modified'] ? htmlspecialchars(date('d.m.Y H:i', strtotime($lengthClass['date_modified']))) : ''; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('system/length', array('action' => 'form', 'id' => $lengthClass['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('system/length', array('action' => 'delete', 'id' => $lengthClass['id'])); ?>" class="btn btn-sm btn-outline-danger<?= $isDefault ? ' disabled' : ''; ?>"<?= $isDefault ? ' tabindex="-1" aria-disabled="true"' : ' onclick="return confirm(\'Удалить единицу длины?\');"'; ?>>Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Единицы длины не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
