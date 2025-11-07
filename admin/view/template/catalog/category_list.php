<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Категории</h1>
    <a href="<?= admin_url('catalog/category', array('action' => 'form')); ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить категорию</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Родитель</th>
                    <th>Сортировка</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($categories): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= (int)$category['id']; ?></td>
                        <td><?= htmlspecialchars($category['name']); ?></td>
                        <td><?= htmlspecialchars($category['parent_name']); ?></td>
                        <td><?= (int)$category['sort_order']; ?></td>
                        <td class="text-end">
                            <a href="<?= admin_url('catalog/category', array('action' => 'form', 'id' => $category['id'])); ?>" class="btn btn-sm btn-outline-secondary">Изменить</a>
                            <a href="<?= admin_url('catalog/category', array('action' => 'delete', 'id' => $category['id'])); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить категорию?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Категории не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
