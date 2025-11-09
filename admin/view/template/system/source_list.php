<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Источники заказов</h1>
    <a href="<?= admin_url('system/source', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить источник</a>
</div>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Создан</th>
                    <th>Обновлён</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($sources)): ?>
                <?php foreach ($sources as $source): ?>
                    <tr>
                        <td><?= (int)$source['id']; ?></td>
                        <td><?= htmlspecialchars($source['name']); ?></td>
                        <td><?= htmlspecialchars($source['created_at']); ?></td>
                        <td><?= htmlspecialchars($source['updated_at']); ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('system/source', array('action' => 'form', 'id' => $source['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('system/source', array('action' => 'delete', 'id' => $source['id'])); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить источник?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Источники не найдены</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
