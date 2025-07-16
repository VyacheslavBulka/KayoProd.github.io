<?php
require_once __DIR__ . '/../auth.php';
requireRole('Администратор', 'Персонал');

$pageTitle = 'Отчётность';

// Обработка удаления (только для админов)
if (isset($_GET['delete'], $_GET['id']) && currentUser()['role'] === 'Администратор') {
    $stmt = $pdo->prepare('DELETE FROM report_forms WHERE id = :id');
    $stmt->execute(['id' => $_GET['id']]);
    header('Location: reports.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO report_forms (user_id, report_date, description) VALUES (:uid, :rdate, :desc)');
    $stmt->execute([
        'uid' => currentUser()['id'],
        'rdate' => $_POST['report_date'],
        'desc' => $_POST['description'],
    ]);
    header('Location: reports.php');
    exit;
}

$reports = $pdo->query('SELECT rf.*, u.username FROM report_forms rf LEFT JOIN users u ON rf.user_id = u.id ORDER BY rf.created_at DESC')->fetchAll();

require_once __DIR__ . '/../modules/_header.php';
?>

<h1>Добавить отчет</h1>

<form method="post" class="mb-4">
    <input name="report_date" type="date" class="form-control mb-2" required>
    <textarea name="description" rows="4" class="form-control mb-2" placeholder="Описание отчёта" required></textarea>
    <button class="btn btn-primary">Добавить</button>
</form>

<h2>Список отчётов</h2>
<table class="table table-bordered table-striped">
    <thead >
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Дата отчёта</th>
            <th>Описание</th>
            <th>Дата создания</th>
            <?php if (currentUser()['role'] === 'Администратор'): ?>
                <th>Действия</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reports as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['username'] ?? 'Гость') ?></td>
                <td><?= htmlspecialchars($r['report_date']) ?></td>
                <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
                <td><?= $r['created_at'] ?></td>
                <?php if (currentUser()['role'] === 'Администратор'): ?>
                    <td>
                        <a href="?delete=1&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Удалить отчёт?')">Удалить</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../modules/_footer.php'; ?>
