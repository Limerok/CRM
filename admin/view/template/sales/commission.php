<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Комиссия по категориям</h1>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">Настройки комиссий успешно сохранены.</div>
<?php endif; ?>

<?php if (empty($sources) || empty($categories)): ?>
    <div class="alert alert-info">Для настройки комиссий необходимо добавить хотя бы один источник и одну категорию.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 260px;">Категория</th>
                                <?php foreach ($sources as $source): ?>
                                    <th><?= htmlspecialchars($source['name']); ?> (%)</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryId = (int)$category['id']; ?>
                            <tr>
                                <td><?= htmlspecialchars($category['name']); ?></td>
                                <?php foreach ($sources as $source): ?>
                                    <?php
                                        $sourceId = (int)$source['id'];
                                        $value = 0;
                                        if (isset($commission_map[$categoryId]) && isset($commission_map[$categoryId][$sourceId])) {
                                            $value = (float)$commission_map[$categoryId][$sourceId];
                                        }
                                    ?>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="commissions[<?= $categoryId; ?>][<?= $sourceId; ?>]" class="form-control" value="<?= htmlspecialchars(number_format($value, 2, '.', '')); ?>" placeholder="0.00">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-text mb-3">Оставьте поле пустым, чтобы сбросить комиссию для выбранной комбинации.</div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
