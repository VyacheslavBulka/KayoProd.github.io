<?php
require_once '../php/connect.php';
require_once '../config/db.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['Пользователь']['id']) || $_SESSION['Пользователь']['role'] !== 'Администратор') {
    header('Location: login.php');
    exit;
}

// Handle actions
if (isset($_GET['action'])) {
    $id = (int) $_GET['id'];

    switch ($_GET['action']) {
        case 'delete':
            mysqli_query($connect, "DELETE FROM bug_reports WHERE id = $id");
            break;
        case 'resolve':
            mysqli_query($connect, "UPDATE bug_reports SET status = 'resolved' WHERE id = $id");
            break;
        case 'reopen':
            mysqli_query($connect, "UPDATE bug_reports SET status = 'open' WHERE id = $id");
            break;
    }

    header('Location: bug-reports.php');
    exit;
}

// Get all bug reports
$query = "SELECT * FROM bug_reports ORDER BY created_at DESC";
$result = mysqli_query($connect, $query);
$reports = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление отчетами об ошибках | Админ-панель</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Общие стили для таблицы */
        .bug-reports-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .bug-reports-list th,
        .bug-reports-list td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .bug-reports-list th {
            background-color: var(--header-bg);
            font-weight: bold;
            color: var(--header-color);
        }

        /* Стили для статусов */
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
        }

        tr.status-new .status-badge {
            background-color: #ffc107;
            color: #000;
        }

        tr.status-open .status-badge {
            background-color: #fd7e14;
            color: #fff;
        }

        tr.status-resolved .status-badge {
            background-color: #28a745;
            color: #fff;
        }

        /* Стили для кнопок действий */
        .actions {
            white-space: nowrap;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8em;
            margin-right: 5px;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-warning {
            background-color: #ffc107;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        /* Переменные для светлой темы */
        :root {
            --header-bg: #f8f9fa;
            --header-color: #212529;
            --border-color: #dee2e6;
            --table-bg: #ffffff;
            --text-color: #212529;
        }

        /* Переменные для темной темы */
        [data-bs-theme="dark"] {
            --header-bg: #343a40;
            --header-color: #f8f9fa;
            --border-color: #495057;
            --table-bg: #2c3034;
            --text-color: #f8f9fa;
        }

        /* Применение цветов */
        body {
            background-color: var(--table-bg);
            color: var(--text-color);
        }

        .bug-reports-list table {
            background-color: var(--table-bg);
            color: var(--text-color);
        }

        .bug-reports-list th {
            background-color: var(--header-bg);
            color: var(--header-color);
        }

        /* Стили для переключателя темы */
        .theme-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 1.2em;
        }
    </style>
</head>

<body>
    <?php include 'modules/_header.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1><i class="fas fa-bug"></i> Отчеты об ошибках</h1>
        </div>

        <div class="bug-reports-list">
            <?php if (empty($reports)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>Нет новых отчетов</h3>
                    <p>Все ошибки исправлены, отличная работа!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>VK</th>
                                <th class="description-col">Описание</th>
                                <th>Страница</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr class="status-<?= $report['status'] ?>">
                                    <td><?= $report['id'] ?></td>
                                    <td><?= htmlspecialchars($report['name']) ?></td>
                                    <td>
                                        <?php if (!empty($report['vk_link'])): ?>
                                            <a href="<?= htmlspecialchars($report['vk_link']) ?>" target="_blank">
                                                <?= htmlspecialchars($report['vk_link']) ?>
                                            </a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="description-col" title="<?= htmlspecialchars($report['description']) ?>">
                                        <?= mb_substr(htmlspecialchars($report['description']), 0, 50) ?>
                                        <?= mb_strlen($report['description']) > 50 ? '...' : '' ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($report['page_url'])): ?>
                                            <a href="<?= htmlspecialchars($report['page_url']) ?>" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($report['created_at'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $report['status'] ?>">
                                            <?php
                                            $statusText = [
                                                'new' => 'Новый',
                                                'open' => 'Открыт',
                                                'resolved' => 'Решен'
                                            ];
                                            echo $statusText[$report['status']] ?? $report['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <?php if ($report['status'] !== 'resolved'): ?>
                                            <a href="bug-reports.php?action=resolve&id=<?= $report['id'] ?>"
                                                class="btn btn-sm btn-success" title="Пометить как решенное">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="bug-reports.php?action=reopen&id=<?= $report['id'] ?>"
                                                class="btn btn-sm btn-warning" title="Переоткрыть">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="bug-reports.php?action=delete&id=<?= $report['id'] ?>"
                                            class="btn btn-sm btn-danger" title="Удалить"
                                            onclick="return confirm('Вы уверены?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>