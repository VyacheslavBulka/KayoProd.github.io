<?php
require_once __DIR__ . '/../auth.php';
requireRole('Администратор', 'Персонал');

$pageTitle = 'Заказы';

/* --- Удаление по ID --- */
if (isset($_GET['delete'], $_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM order_forms WHERE id = :id');
    $stmt->execute(['id' => $_GET['id']]);
    header('Location: orders.php');
    exit;
}

/* --- Одобрение заказа --- */
if (isset($_GET['approve'], $_GET['id'])) {
    $stmt = $pdo->prepare('UPDATE order_forms SET approved = TRUE WHERE id = :id');
    $stmt->execute(['id' => $_GET['id']]);
    header('Location: orders.php');
    exit;
}

/* --- Редактирование заказа --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $budget = floatval($_POST['budget']);
    if ($budget < 0)
        $budget = 0;

    $vk_link = !empty($_POST['vk_link']) ? $_POST['vk_link'] : null;

    $stmt = $pdo->prepare('UPDATE order_forms SET 
        event_name = :ename, 
        date_wish = :dw, 
        budget = :budget, 
        comments = :comments, 
        vk_link_user = :vk_link 
        WHERE id = :id');
    $stmt->execute([
        'id' => $_POST['edit_id'],
        'ename' => $_POST['event_name'],
        'dw' => !empty($_POST['date_wish']) ? $_POST['date_wish'] : null,
        'budget' => $budget,
        'comments' => $_POST['comments'],
        'vk_link' => $vk_link
    ]);

    header('Location: orders.php');
    exit;
}

/* --- Добавление --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $user = currentUser();
    if (!$user || !isset($user['id'])) {
        header('Location: orders.php');
        exit;
    }

    $budget = floatval($_POST['budget']);
    if ($budget < 0)
        $budget = 0;

    $vk_link = !empty($_POST['vk_link']) ? $_POST['vk_link'] : null;

    $stmt = $pdo->prepare('INSERT INTO order_forms (user_id, event_name, date_wish, budget, comments, vk_link_user) 
        VALUES (:uid, :ename, :dw, :budget, :comments, :vk_link)');
    $stmt->execute([
        'uid' => $user['id'],
        'ename' => $_POST['event_name'],
        'dw' => !empty($_POST['date_wish']) ? $_POST['date_wish'] : null,
        'budget' => $budget,
        'comments' => $_POST['comments'],
        'vk_link' => $vk_link
    ]);

    header('Location: orders.php');
    exit;
}

/* --- Получение всех заказов --- */
$orders = $pdo->query('SELECT o.*, u.username FROM order_forms o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC')->fetchAll();

require_once __DIR__ . '/../modules/_header.php';
?>
<style>
    /* Адаптация для мобильных */
    @media (max-width: 767.98px) {
        /* Строки таблицы как карточки */
        table tr {
            display: block;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        
        /* Ячейки с метками */
        table td {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            border: none;
            border-bottom: 1px solid #dee2e6;
        }
        
        /* Метки для ячеек */
        table td::before {
            content: attr(data-label);
            font-weight: bold;
            margin-right: 1rem;
            flex: 0 0 120px;
        }
        
        /* Убираем нижнюю границу у последней ячейки */
        table td:last-child {
            border-bottom: none;
        }
        
        /* Кнопки действий */
        .justify-content-end {
            justify-content: flex-start !important;
        }
        
        /* Форма */
        .form-control, .btn {
            width: 100%;
        }
    }
    
    /* Темная тема */
    [data-bs-theme="dark"] table tr {
        border-color: #495057;
    }
    
    [data-bs-theme="dark"] table td {
        border-color: #495057;
    }
    
    [data-bs-theme="dark"] .bg-light {
        background-color: #343a40 !important;
    }
</style>
<h1>Форма заказа</h1>

<form method="post" class="mb-4">
    <?php if (isset($_GET['edit'])):
        $editOrder = $pdo->prepare('SELECT * FROM order_forms WHERE id = ?');
        $editOrder->execute([$_GET['edit']]);
        $editData = $editOrder->fetch();
        ?>
        <input type="hidden" name="edit_id" value="<?= $_GET['edit'] ?>">
        <div class="mb-3">
            <input name="event_name" class="form-control mb-2" placeholder="Название мероприятия"
                value="<?= htmlspecialchars($editData['event_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <input name="date_wish" type="date" class="form-control mb-2" placeholder="Желаемая дата"
                value="<?= htmlspecialchars($editData['date_wish'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <input name="budget" type="number" step="0.01" class="form-control mb-2" placeholder="Бюджет"
                value="<?= htmlspecialchars($editData['budget'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <input name="vk_link" type="url" class="form-control mb-2"
                placeholder="Ссылка на ваш VK (https://vk.com/username)"
                value="<?= htmlspecialchars($editData['vk_link_user'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <textarea name="comments" rows="3" class="form-control mb-2"
                placeholder="Комментарии"><?= htmlspecialchars($editData['comments'] ?? '') ?></textarea>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить изменения
            </button>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Отмена
            </a>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <input name="event_name" class="form-control mb-2" placeholder="Название мероприятия" required>
        </div>
        <div class="mb-3">
            <input name="date_wish" type="date" class="form-control mb-2" placeholder="Желаемая дата" required>
        </div>
        <div class="mb-3">
            <input name="budget" type="number" step="0.01" class="form-control mb-2" placeholder="Бюджет">
        </div>
        <div class="mb-3">
            <input name="vk_link" type="url" class="form-control mb-2"
                placeholder="Ссылка на ваш VK (https://vk.com/username)">
        </div>
        <div class="mb-3">
            <textarea name="comments" rows="3" class="form-control mb-2" placeholder="Комментарии"></textarea>
        </div>
        <button class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Отправить
        </button>
    <?php endif; ?>
</form>

<h2>Список заказов</h2>
<table class="table table-bordered table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Мероприятие</th>
            <th>Дата</th>
            <th>Бюджет</th>
            <th>Комментарии</th>
            <th>VK пользователя</th>
            <th>Статус</th>
            <th>Дата подачи</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['username'] ?? 'Гость') ?></td>
                <td><?= htmlspecialchars($o['event_name']) ?></td>
                <td><?= htmlspecialchars($o['date_wish']) ?></td>
                <td><?= htmlspecialchars($o['budget']) ?></td>
                <td><?= nl2br(htmlspecialchars($o['comments'])) ?></td>
                <td>
                    <?php if (!empty($o['vk_link_user'])): ?>
                        <a href="<?= htmlspecialchars($o['vk_link_user']) ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fab fa-vk"></i> Перейти
                        </a>
                    <?php else: ?>
                        Не указан
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-<?= $o['approved'] ? 'success' : 'dark' ?>">
                        <?= $o['approved'] ? 'Одобрено' : 'На рассмотрении' ?>
                    </span>
                </td>
                <td><?= $o['created_at'] ?></td>
                <td class="text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <?php if (!$o['approved']): ?>
                            <a href="?edit=<?= $o['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Изменить
                            </a>
                            <a href="?approve=1&id=<?= $o['id'] ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> Одобрить
                            </a>
                        <?php endif; ?>
                        <a href="?delete=1&id=<?= $o['id'] ?>" onclick="return confirm('Удалить этот заказ?')"
                            class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Удалить
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        const vkLink = document.querySelector('[name="vk_link"]').value;
        if (vkLink && !vkLink.match(/^(https?:\/\/)?(www\.)?vk\.com\/.+/i)) {
            alert('Пожалуйста, введите корректную ссылку VK (например: https://vk.com/vyacheslavbu)');
            e.preventDefault();
        }
    });
</script>

<?php require_once __DIR__ . '/../modules/_footer.php'; ?>