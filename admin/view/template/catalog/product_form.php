<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= $product ? 'Редактирование товара' : 'Добавление товара'; ?></h1>
    <a href="<?= admin_url('catalog/product'); ?>" class="btn btn-secondary">Назад</a>
</div>
<form method="post" class="card shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Наименование товара</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(isset($product['name']) ? $product['name'] : ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Модель</label>
            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars(isset($product['model']) ? $product['model'] : ''); ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Серия</label>
            <input type="text" name="series" class="form-control" value="<?= htmlspecialchars(isset($product['series']) ? $product['series'] : ''); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Производитель</label>
            <select name="manufacturer_id" class="form-select">
                <option value="">- Не выбран -</option>
                <?php foreach ($manufacturers as $manufacturer): ?>
                    <option value="<?= $manufacturer['id']; ?>" <?= (!empty($product['manufacturer_id']) && $product['manufacturer_id'] == $manufacturer['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($manufacturer['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Главная категория</label>
            <select name="category_id" class="form-select">
                <option value="">- Не выбрана -</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id']; ?>" <?= (!empty($product['category_id']) && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Сортировка</label>
            <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars(isset($product['sort_order']) ? $product['sort_order'] : ''); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Цена закупки</label>
            <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= htmlspecialchars(isset($product['purchase_price']) ? $product['purchase_price'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Валюта закупки</label>
            <select name="purchase_currency" class="form-select">
                <?php foreach (array('RUB' => 'Рубли', 'USD' => 'Доллары', 'EUR' => 'Евро') as $value => $label): ?>
                    <option value="<?= $value; ?>" <?= ((isset($product['purchase_currency']) ? $product['purchase_currency'] : 'RUB') === $value) ? 'selected' : ''; ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Вес товара</label>
            <input type="number" step="0.001" name="weight" class="form-control" value="<?= htmlspecialchars(isset($product['weight']) ? $product['weight'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Единица веса</label>
            <select name="weight_unit" class="form-select">
                <?php foreach (array('kg' => 'Килограммы', 'g' => 'Граммы') as $value => $label): ?>
                    <option value="<?= $value; ?>" <?= ((isset($product['weight_unit']) ? $product['weight_unit'] : 'kg') === $value) ? 'selected' : ''; ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Вес в упаковке</label>
            <input type="number" step="0.001" name="weight_package" class="form-control" value="<?= htmlspecialchars(isset($product['weight_package']) ? $product['weight_package'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Единица длины</label>
            <select name="length_unit" class="form-select">
                <?php foreach (array('mm' => 'Миллиметры', 'cm' => 'Сантиметры', 'm' => 'Метры') as $value => $label): ?>
                    <option value="<?= $value; ?>" <?= ((isset($product['length_unit']) ? $product['length_unit'] : 'mm') === $value) ? 'selected' : ''; ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Габариты (Ш)</label>
            <input type="number" step="0.1" name="width" class="form-control" value="<?= htmlspecialchars(isset($product['width']) ? $product['width'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Габариты (В)</label>
            <input type="number" step="0.1" name="height" class="form-control" value="<?= htmlspecialchars(isset($product['height']) ? $product['height'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Габариты (Г)</label>
            <input type="number" step="0.1" name="length" class="form-control" value="<?= htmlspecialchars(isset($product['length']) ? $product['length'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Габариты в упаковке (Ш)</label>
            <input type="number" step="0.1" name="width_package" class="form-control" value="<?= htmlspecialchars(isset($product['width_package']) ? $product['width_package'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Габариты в упаковке (В)</label>
            <input type="number" step="0.1" name="height_package" class="form-control" value="<?= htmlspecialchars(isset($product['height_package']) ? $product['height_package'] : '0'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Габариты в упаковке (Г)</label>
            <input type="number" step="0.1" name="length_package" class="form-control" value="<?= htmlspecialchars(isset($product['length_package']) ? $product['length_package'] : '0'); ?>">
        </div>
    </div>
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary">Сохранить</button>
    </div>
</form>
