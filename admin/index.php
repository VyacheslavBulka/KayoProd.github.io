<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pageTitle = 'Дашборд';

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM order_forms")->fetchColumn();
$totalEmployment = $pdo->query("SELECT COUNT(*) FROM employment_forms")->fetchColumn();
$totalReports = $pdo->query("SELECT COUNT(*) FROM report_forms")->fetchColumn();
$users = $pdo->query("SELECT id, username, email, role FROM users ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/modules/_header.php';
?>
<link rel="stylesheet" href="../css/dashboard.css">

<h1 class="mb-4 text-center">Дашборд</h1>

<!-- Карточки статистики - изменено на row-cols-2 для мобильных -->
<div class="row row-cols-2 row-cols-md-2 row-cols-lg-4 g-3 mb-4">
    <div class="col">
        <a href="/admin/users.php" class="text-decoration-none">
            <div class="card stat-card card-hover-fill-blue border-primary h-100">
                <div class="card-body text-primary text-center">
                    <h5 class="card-title fs-6">Пользователи</h5>
                    <p class="card-text fs-4"><?= $totalUsers ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="/admin/public/orders.php" class="text-decoration-none">
            <div class="card stat-card card-hover-fill-green border-success h-100">
                <div class="card-body text-success text-center">
                    <h5 class="card-title fs-6">Заказы</h5>
                    <p class="card-text fs-4"><?= $totalOrders ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="/admin/public/employment.php" class="text-decoration-none">
            <div class="card stat-card card-hover-fill-yellow border-warning h-100">
                <div class="card-body text-warning text-center">
                    <h5 class="card-title fs-6">Трудоустройство</h5>
                    <p class="card-text fs-4"><?= $totalEmployment ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="/admin/public/reports.php" class="text-decoration-none">
            <div class="card stat-card card-hover-fill-red border-danger h-100">
                <div class="card-body text-danger text-center">
                    <h5 class="card-title fs-6">Отчёты</h5>
                    <p class="card-text fs-4"><?= $totalReports ?></p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php
// Группировка пользователей по ролям
$grouped = [
    'Администратор' => [],
    'Редактор' => [],
    'Персонал' => [],
];

foreach ($users as $u) {
    if (
        isset($grouped[$u['role']]) &&
        $u['id'] != 0 // исключаем пользователя с id = 0
    ) {
        $grouped[$u['role']][] = $u;
    }
}
foreach ($grouped as $role => &$group) {
    usort($group, function ($a, $b) {
        return strcasecmp($a['username'], $b['username']); // регистронезависимая сортировка
    });
}
unset($group); // защищаем ссылку
?>

<!-- Администраторы -->
<div class="card mb-3">
    <div class="card-header bg-danger text-white">
        <h3 class="h5 mb-0 text-center">Администраторы</h3>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php foreach ($grouped['Администратор'] as $u): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-truncate"><?= htmlspecialchars($u['username']) ?></span>
                    <span class="badge bg-danger"><?= $u['role'] ?></span>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
</div>

<!-- Редакторы -->
<div class="card mb-3">
    <div class="card-header bg-warning text-dark">
        <h3 class="h5 mb-0 text-center">Редакторы</h3>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php foreach ($grouped['Редактор'] as $u): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-truncate"><?= htmlspecialchars($u['username']) ?></span>
                    <span class="badge bg-warning text-dark"><?= $u['role'] ?></span>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
</div>

<!-- Персонал -->
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <h3 class="h5 mb-0 text-center">Персонал</h3>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php foreach ($grouped['Персонал'] as $u): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-truncate"><?= htmlspecialchars($u['username']) ?></span>
                    <span class="badge bg-success"><?= $u['role'] ?></span>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/modules/_footer.php'; ?>