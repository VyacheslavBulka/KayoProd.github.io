<?php
require_once __DIR__ . '/../auth.php';  // правильный путь к auth.php, смотри структуру
requireRole('Администратор', 'Персонал');  // допуск по ролям

$pageTitle = 'Трудоустройство';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO employment_forms (user_id, fullname, vk_link, position, experience) VALUES (:uid, :n, :vk, :pos, :e)');
    $stmt->execute([
        'uid' => currentUser()['id'],
        'n' => $_POST['fullname'],
        'vk' => $_POST['link'],
        'pos' => $_POST['position'],
        'e' => $_POST['experience']
    ]);
    header('Location: employment.php');
    exit;
}

// Удаление заявки по ID
if (isset($_GET['delete'], $_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM employment_forms WHERE id = :id');
    $stmt->execute(['id' => $_GET['id']]);
    header('Location: employment.php');
    exit;
}

// Получаем список заявок
$forms = $pdo->query('SELECT ef.*, u.username FROM employment_forms ef LEFT JOIN users u ON ef.user_id = u.id ORDER BY ef.created_at DESC')->fetchAll();

require_once __DIR__ . '/../modules/_header.php';
?>

<!-- <h1>Анкета трудоустройства</h1>

<form method="post" class="mb-4">
    <input name="fullname" class="form-control mb-2" placeholder="NickName" required>
    <input name="link" class="form-control mb-2" placeholder="VK" required>
    <input name="position" class="form-control mb-2" placeholder="Желаемая должность" required>
    <textarea name="experience" rows="4" class="form-control mb-2" placeholder="Опыт, навыки"></textarea>
    <button class="btn btn-primary">Отправить</button>
</form> -->

<h1>Поданные заявки</h1>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>NickName</th>
            <th>VK</th>
            <th>Должность</th>
            <th>Опыт</th>
            <th>Дата подачи</th>
            <?php if (in_array(currentUser()['role'], ['Администратор', 'Редактор'])): ?>
                <th>Действия</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($forms as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['username'] ?? 'Гость') ?></td>
                <td><?= htmlspecialchars($f['fullname']) ?></td>
                <td><?= htmlspecialchars($f['vk_link']) ?></td>
                <td><?= htmlspecialchars($f['position']) ?></td>
                <td><?= nl2br(htmlspecialchars($f['experience'])) ?></td>
                <td><?= $f['created_at'] ?></td>
                <?php if (in_array(currentUser()['role'], ['Администратор', 'Редактор'])): ?>
                    <td class="text-end">
                        <a href="?delete=1&id=<?= $f['id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Удалить эту заявку?')">Удалить</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../modules/_footer.php'; ?>