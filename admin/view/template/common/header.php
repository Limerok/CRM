<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>CRM Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/admin/view/stylesheet/style.css" rel="stylesheet">
    <script src="/admin/view/javascript/inline-editor.js" defer></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= admin_url('common/dashboard'); ?>">CRM Admin</a>
        <div class="d-flex">
            <span class="navbar-text me-3"><?= htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : ''); ?></span>
            <a class="btn btn-outline-light btn-sm" href="<?= admin_url('common/login', array('action' => 'logout')); ?>">Выход</a>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
