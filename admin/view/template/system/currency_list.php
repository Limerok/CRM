<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Валюты</h1>
    <a href="<?= admin_url('system/currency', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить валюту</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Код</th>
                    <th>Курс</th>
                    <th>Дата обновления</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($currencies): ?>
                <?php foreach ($currencies as $currency): ?>
                    <?php $isDefault = ($currency['id'] == $default_currency_id); ?>
                    <tr>
                        <td><?= (int)$currency['id']; ?></td>
                        <td><?= htmlspecialchars($currency['name']); ?><?= $isDefault ? ' (По умолчанию)' : ''; ?></td>
                        <td><?= htmlspecialchars($currency['code']); ?></td>
                        <td><?= number_format((float)$currency['value'], 4, '.', ' '); ?></td>
                        <td><?= $currency['date_modified'] ? htmlspecialchars(date('d.m.Y H:i', strtotime($currency['date_modified']))) : ''; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('system/currency', array('action' => 'form', 'id' => $currency['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('system/currency', array('action' => 'delete', 'id' => $currency['id'])); ?>" class="btn btn-sm btn-outline-danger<?= $isDefault ? ' disabled' : ''; ?>"<?= $isDefault ? ' tabindex="-1" aria-disabled="true"' : ' onclick="return confirm(\'Удалить валюту?\');"'; ?>>Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Валюты не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
