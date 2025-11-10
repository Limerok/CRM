<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Рекомендуемые остатки</h1>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">Значения успешно сохранены.</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $message): ?>
                        <li><?= htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="<?= admin_url('stock/recommended'); ?>">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Наименование</th>
                            <th>Модель</th>
                            <th>Серия</th>
                            <th class="text-end">Остатки</th>
                            <th style="width: 220px;" class="text-end">Рекомендуемый остаток</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                                $recommendedValue = '';
                                if (array_key_exists('recommended_quantity', $product) && $product['recommended_quantity'] !== null) {
                                    $recommendedValue = (string)$product['recommended_quantity'];
                                }
                            ?>
                            <tr>
                                <td><?= (int)$product['id']; ?></td>
                                <td><?= htmlspecialchars($product['name']); ?></td>
                                <td><?= htmlspecialchars(isset($product['model']) && $product['model'] !== null ? (string)$product['model'] : '—'); ?></td>
                                <td><?= htmlspecialchars(isset($product['series']) && $product['series'] !== null ? (string)$product['series'] : '—'); ?></td>
                                <td class="text-end"><?= (int)$product['quantity']; ?></td>
                                <td class="text-end">
                                    <input type="number" name="recommended[<?= (int)$product['id']; ?>]" class="form-control text-end"
                                           value="<?= htmlspecialchars($recommendedValue); ?>" min="0" step="1" placeholder="—">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Товары отсутствуют.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($products)): ?>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
