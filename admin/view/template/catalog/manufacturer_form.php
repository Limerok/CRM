<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= $manufacturer ? 'Редактирование производителя' : 'Добавление производителя'; ?></h1>
    <a href="<?= admin_url('catalog/manufacturer'); ?>" class="btn btn-secondary">Назад</a>
</div>
<form method="post" class="card shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Наименование производителя</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(isset($manufacturer['name']) ? $manufacturer['name'] : ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Сортировка</label>
            <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars(isset($manufacturer['sort_order']) ? $manufacturer['sort_order'] : ''); ?>">
        </div>
    </div>
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary">Сохранить</button>
    </div>
</form>
