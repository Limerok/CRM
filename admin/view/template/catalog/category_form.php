<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= $category ? 'Редактирование категории' : 'Добавление категории'; ?></h1>
    <a href="<?= admin_url('catalog/category'); ?>" class="btn btn-secondary">Назад</a>
</div>
<form method="post" class="card shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Наименование категории</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(isset($category['name']) ? $category['name'] : ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Родительская категория</label>
            <select name="parent_id" class="form-select">
                <option value="">- Нет -</option>
                <?php foreach ($categories as $parent): ?>
                    <option value="<?= $parent['id']; ?>" <?= (!empty($category['parent_id']) && $category['parent_id'] == $parent['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($parent['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Сортировка</label>
            <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars(isset($category['sort_order']) ? $category['sort_order'] : ''); ?>">
        </div>
    </div>
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary">Сохранить</button>
    </div>
</form>
