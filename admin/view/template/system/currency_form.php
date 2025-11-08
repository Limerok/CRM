<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= !empty($currency) ? 'Редактирование валюты' : 'Добавление валюты'; ?></h1>
    <a href="<?= admin_url('system/currency'); ?>" class="btn btn-secondary">Назад</a>
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

<form method="post" action="<?= admin_url('system/currency', !empty($currency['id']) ? array('action' => 'form', 'id' => $currency['id']) : array('action' => 'form')); ?>" class="card shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Название валюты</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(!empty($currency['name']) ? $currency['name'] : ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Код</label>
            <input type="text" name="code" class="form-control text-uppercase" maxlength="3" value="<?= htmlspecialchars(!empty($currency['code']) ? $currency['code'] : ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Курс</label>
            <input type="number" name="value" step="0.0001" min="0" class="form-control" value="<?= htmlspecialchars(!empty($currency['value']) ? $currency['value'] : '1'); ?>" required>
        </div>
    </div>
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary">Сохранить</button>
    </div>
</form>
