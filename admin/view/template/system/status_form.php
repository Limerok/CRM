<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= isset($status['id']) ? 'Редактирование статуса' : 'Добавление статуса'; ?></h1>
    <a href="<?= admin_url('system/status'); ?>" class="btn btn-secondary">К списку статусов</a>
</div>
<div class="card shadow-sm p-4">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Название статуса</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(isset($status['name']) ? $status['name'] : ''); ?>" required>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </form>
</div>
