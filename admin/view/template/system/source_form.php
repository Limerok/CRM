<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= isset($source['id']) ? 'Редактирование источника' : 'Добавление источника'; ?></h1>
    <a href="<?= admin_url('system/source'); ?>" class="btn btn-secondary">К списку источников</a>
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
            <label class="form-label">Название источника</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(isset($source['name']) ? $source['name'] : ''); ?>" required>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </form>
</div>
