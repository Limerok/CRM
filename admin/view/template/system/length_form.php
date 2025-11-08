<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= !empty($length_class) ? 'Редактирование единицы длины' : 'Добавление единицы длины'; ?></h1>
    <a href="<?= admin_url('system/length'); ?>" class="btn btn-secondary">Назад</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= admin_url('system/length', !empty($length_class['id']) ? array('action' => 'form', 'id' => $length_class['id']) : array('action' => 'form')); ?>" class="card shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Название</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(!empty($length_class['name']) ? $length_class['name'] : ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Обозначение</label>
            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars(!empty($length_class['code']) ? $length_class['code'] : ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Значение</label>
            <input type="number" name="value" step="0.0001" min="0" class="form-control" value="<?= htmlspecialchars(!empty($length_class['value']) ? $length_class['value'] : '1'); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Сортировка</label>
            <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars(!empty($length_class['sort_order']) ? $length_class['sort_order'] : '0'); ?>">
        </div>
    </div>
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary">Сохранить</button>
    </div>
</form>
