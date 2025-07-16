<?php
require_once __DIR__ . '/../auth.php';
requireRole('Администратор');          // доступ только администраторам

$pageTitle = 'Команда';

/* ---------- Удаление ---------- */
if (isset($_GET['delete'], $_GET['id'])) {
    $pdo->prepare('DELETE FROM team WHERE id = :id')->execute(['id' => $_GET['id']]);
    header('Location: team.php');
    exit;
}

/* ---------- Добавление / редактирование ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'     => $_POST['name'],
        'position' => $_POST['position'],
        'vk_link'  => $_POST['vk_link'],
    ];

    if (empty($_POST['id'])) {                 // create
        $sql = 'INSERT INTO team (name, position, vk_link)
                VALUES (:name, :position, :vk_link)';
    } else {                                   // update
        $sql          = 'UPDATE team SET name = :name, position = :position, vk_link = :vk_link WHERE id = :id';
        $data['id'] = $_POST['id'];
    }
    $pdo->prepare($sql)->execute($data);
    header('Location: team.php');
    exit;
}

/* ---------- Получение данных для формы / таблицы ---------- */
$editId = $_GET['edit'] ?? null;
$item   = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM team WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $item = $stmt->fetch();
}
$members = $pdo->query('SELECT * FROM team ORDER BY id')->fetchAll();

require_once __DIR__ . '/_header.php';
?>

<h2 class="mb-4">Состав команды</h2>

<!-- форма добавления / редактирования -->
<form method="post" class="border rounded p-3 mb-4">
    <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
    <div class="row g-2">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" placeholder="Имя в игре" required
                   value="<?= htmlspecialchars($item['name'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="position" class="form-control" placeholder="Должность" required
                   value="<?= htmlspecialchars($item['position'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="url" name="vk_link" class="form-control" placeholder="Ссылка VK"
                   value="<?= htmlspecialchars($item['vk_link'] ?? '') ?>">
        </div>
        <div class="col-md-2 mt-2">
            <button class="btn btn-success w-100">Сохранить</button>
        </div>
    </div>
</form>

<!-- таблица участников -->
<table class="table table-striped table-bordered align-middle">
    <thead >
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Должность</th>
            <th>VK</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['position']) ?></td>
                <td>
                    <?php if ($m['vk_link']): ?>
                        <a href="<?= htmlspecialchars($m['vk_link']) ?>" target="_blank"><i class="bi bi-vk"></i> ссылка</a>
                    <?php endif ?>
                </td>
                <td class="text-end">
                    <a href="?edit=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Изменить</a>
                    <a href="?delete=1&id=<?= $m['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Удалить участника?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/_footer.php'; ?>
