<?php
require_once __DIR__ . '/../auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Admin | <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="/../css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('themeSwitch').checked = savedTheme === 'light';
        });

        function toggleTheme() {
            const isLight = document.getElementById('themeSwitch').checked;
            const theme = isLight ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
        }
    </script>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <!-- <a class="navbar-brand" href="/admin/">А-П</a> -->
            <a href="/../admin/index.php" class="logo">Kayo<span>Prod</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/admin/">Дашборд</a></li>
                    <?php if (in_array(currentUser()['role'], ['Администратор', 'Редактор'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/modules/news.php">Новости</a></li>
                    <?php endif ?>
                    <?php if (in_array(currentUser()['role'], ['Администратор'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/users.php">Пользователи</a></li>
                    <?php endif ?>
                    <?php if (in_array(currentUser()['role'], ['Администратор', 'Персонал'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/public/reports.php">Отчётность</a></li>
                        <li class="nav-item"><a class="nav-link" href="/admin/public/employment.php">Трудоустройство</a></li>
                        <li class="nav-item"><a class="nav-link" href="/admin/public/orders.php">Заказы</a></li>
                    <?php endif ?>
                    <?php if (in_array(currentUser()['role'], ['Администратор', 'Редактор'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/modules/team.php">Команда</a></li>
                    <?php endif ?>
                    <?php if (in_array(currentUser()['role'], ['Администратор'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/bug-reports.php">Репорты</a></li>
                    <?php endif ?>
                    <?php if (in_array(currentUser()['role'], ['Администратор'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/correctsite.php">Информация на сайте</a></li>
                    <?php endif ?>
                    <!-- <?php if (in_array(currentUser()['role'], ['Администратор'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/logs.php">Логи</a></li>
                    <?php endif ?> -->
                </ul>
                <div class="form-check form-switch text-white ms-3" style="margin-right: 5rem;margin-top: 0.2rem;">
                    <input class="form-check-input" type="checkbox" id="themeSwitch" onchange="toggleTheme()" />
                    <label class="form-check-label" for="themeSwitch">Тема</label>
                </div>
                <span class="navbar-text text-white me-3">
                    <?= htmlspecialchars(currentUser()['username']) ?> (<?= currentUser()['role'] ?>)
                </span>
                
                <a class="btn btn-outline-light btn-sm" href="/admin/logout.php">Выход</a>
                <!-- <button class="btn btn-outline-light btn-sm ms-2" onclick="toggleTheme()">🌗 Тема</button> -->
                
            </div>
        </div>
    </nav>
    <div class="container">