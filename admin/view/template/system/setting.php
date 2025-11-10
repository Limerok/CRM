<?php
$availableTabs = array('localization', 'statuses', 'orders');
$currentTab = 'localization';
if (isset($active_tab) && in_array($active_tab, $availableTabs, true)) {
    $currentTab = $active_tab;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Настройки</h1>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">Настройки успешно сохранены.</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header border-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="settingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link<?= $currentTab === 'localization' ? ' active' : ''; ?>" id="tab-localization-tab" data-bs-toggle="tab" data-bs-target="#tab-localization" type="button" role="tab">Локализация</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link<?= $currentTab === 'statuses' ? ' active' : ''; ?>" id="tab-statuses-tab" data-bs-toggle="tab" data-bs-target="#tab-statuses" type="button" role="tab">Статусы</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link<?= $currentTab === 'orders' ? ' active' : ''; ?>" id="tab-orders-tab" data-bs-toggle="tab" data-bs-target="#tab-orders" type="button" role="tab">Заказы</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="settingTabsContent">
            <div class="tab-pane fade<?= $currentTab === 'localization' ? ' show active' : ''; ?>" id="tab-localization" role="tabpanel" aria-labelledby="tab-localization-tab">
                <form method="post" action="<?= admin_url('system/setting'); ?>" class="row g-4">
                    <input type="hidden" name="form" value="localization">
                    <div class="col-md-4">
                        <label class="form-label">Валюта магазина</label>
                        <select name="config_currency_id" class="form-select" required>
                            <option value="">- Выберите валюту -</option>
                            <?php foreach ($currencies as $currency): ?>
                                <option value="<?= (int)$currency['id']; ?>" <?= ($config_currency_id == $currency['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($currency['name']); ?> (<?= htmlspecialchars($currency['code']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Единица измерения</label>
                        <select name="config_length_class_id" class="form-select" required>
                            <option value="">- Выберите единицу длины -</option>
                            <?php foreach ($length_classes as $lengthClass): ?>
                                <option value="<?= (int)$lengthClass['id']; ?>" <?= ($config_length_class_id == $lengthClass['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($lengthClass['name']); ?> (<?= htmlspecialchars($lengthClass['code']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Вес</label>
                        <select name="config_weight_class_id" class="form-select" required>
                            <option value="">- Выберите единицу веса -</option>
                            <?php foreach ($weight_classes as $weightClass): ?>
                                <option value="<?= (int)$weightClass['id']; ?>" <?= ($config_weight_class_id == $weightClass['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($weightClass['name']); ?> (<?= htmlspecialchars($weightClass['code']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade<?= $currentTab === 'statuses' ? ' show active' : ''; ?>" id="tab-statuses" role="tabpanel" aria-labelledby="tab-statuses-tab">
                <form method="post" action="<?= admin_url('system/setting'); ?>" class="row g-4">
                    <input type="hidden" name="form" value="statuses">
                    <div class="col-md-6">
                        <label class="form-label">Статус заказа по умолчанию</label>
                        <select name="default_order_status_id" class="form-select">
                            <option value="0">- Не выбран -</option>
                            <?php foreach ($order_statuses as $status): ?>
                                <option value="<?= (int)$status['id']; ?>" <?= ($default_order_status_id == $status['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($status['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-text">Управление списком статусов доступно на странице <a href="<?= admin_url('system/status'); ?>">"Статусы заказов"</a>.</div>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade<?= $currentTab === 'orders' ? ' show active' : ''; ?>" id="tab-orders" role="tabpanel" aria-labelledby="tab-orders-tab">
                <form method="post" action="<?= admin_url('system/setting'); ?>" class="row g-4">
                    <input type="hidden" name="form" value="orders">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="allowNegativeStock" name="allow_negative_stock" value="1" <?= !empty($allow_negative_stock) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="allowNegativeStock">Разрешить оформление заказов при нехватке товара на складе</label>
                        </div>
                        <div class="form-text">При включении опции система позволит добавлять товары в продажу даже при отсутствии остатка, а количество на складе может стать отрицательным.</div>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
