<?php
require_once '../php/connect.php';
require_once '../php/log.php';
session_start();

if (!isset($_SESSION['Пользователь']) || $_SESSION['Пользователь']['role'] !== 'Администратор') {
    die('Доступ запрещён');
}

$query = "SELECT l.*, u.login FROM admin_logs l 
          JOIN users u ON l.admin_id = u.id 
          ORDER BY l.created_at DESC LIMIT 100";
$result = mysqli_query($connect, $query);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Логи администратора</title>
    <link rel="stylesheet" href="../css/logs.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <nav>
                <a href="/" class="logo">Панель <span>Админа</span></a>
                <ul class="nav-links">
                    <li><a href="/admin/">Главная</a></li>
                    <li><a href="/admin/logs.php" class="active">Логи</a></li>
                    <!-- другие ссылки -->
                </ul>
            </nav>
        </div>
    </header>

    <section class="container">
        <div class="section-title">
            <h2>Логи действий администраторов</h2>
        </div>
        <div class="news-grid">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Админ</th>
                        <th>Действие</th>
                        <th>Подробности</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['login']) ?></td>
                                <td><?= htmlspecialchars($row['action']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                                <td><?= $row['created_at'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Нет логов</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>
