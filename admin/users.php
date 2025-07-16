<?php
require_once __DIR__ . '/auth.php';
requireRole('Администратор');

$pageTitle = 'Пользователи';

// Обработка удаления
if (isset($_GET['delete'], $_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $_GET['id']]);
    header('Location: users.php');
    exit;
}

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'role' => $_POST['role'],
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }

    if (empty($_POST['id'])) { // create
        $sql = 'INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)';
        $pdo->prepare($sql)->execute($data);
    } else { // update
        if (empty($_POST['password'])) {
            $sql = 'UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id';
        } else {
            $sql = 'UPDATE users SET username = :username, email = :email, password = :password, role = :role WHERE id = :id';
        }
        $data['id'] = $_POST['id'];
        $pdo->prepare($sql)->execute($data);
    }
    header('Location: users.php');
    exit;
}

// Получение пользователя для редактирования
$editId = $_GET['edit'] ?? null;
$item = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $item = $stmt->fetch();
}

// Получаем всех пользователей
$items = $pdo->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();

require_once __DIR__ . '/modules/_header.php';
?>

<style>/* Адаптация для мобильных устройств */
@media (max-width: 768px) {
    /* Форма */
    .border.p-3.mb-4.rounded {
        padding: 1rem !important;
    }
    
    /* Таблица */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table thead {
        display: none;
    }
    
    .table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
    
    .table tbody td::before {
        content: attr(data-label);
        font-weight: bold;
        margin-right: 1rem;
        flex: 0 0 100px;
    }
    
    .table tbody td:last-child {
        border-bottom: none;
        justify-content: flex-end;
    }
    
    /* Добавляем метки для ячеек */
    .table tbody td:nth-child(1)::before { content: "ID:"; }
    .table tbody td:nth-child(2)::before { content: "Логин:"; }
    .table tbody td:nth-child(3)::before { content: "Email:"; }
    .table tbody td:nth-child(4)::before { content: "Роль:"; }
    .table tbody td:nth-child(5)::before { content: "Пароль:"; }
    
    /* Кнопки действий */
    .btn-group .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    /* Поле пароля */
    .input-group-sm {
        width: 100%;
    }
    
    /* Кнопки в форме */
    .btn {
        margin-bottom: 0.5rem;
    }
}

/* Темная тема */
[data-bs-theme="dark"] .table-bordered {
    border-color: #495057;
}

[data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd) {
    --bs-table-accent-bg: rgba(255, 255, 255, 0.05);
    color: var(--bs-table-color);
}</style>

<h2>Пользователи</h2>

<form method="post" class="border p-3 mb-4 rounded">
    <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
    <div class="mb-3">
        <input type="text" name="username" placeholder="Логин" class="form-control" required
            value="<?= htmlspecialchars($item['username'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <input type="email" name="email" placeholder="Email" class="form-control" required
            value="<?= htmlspecialchars($item['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <input type="text" name="password" placeholder="Пароль (оставьте пустым, чтобы не менять)" class="form-control">
    </div>
    <div class="mb-3">
        <select name="role" class="form-select" required>
            <?php
            $roles = ['Администратор', 'Редактор', 'Персонал', 'Пользователь'];
            foreach ($roles as $role) {
                $selected = ($item['role'] ?? '') === $role ? 'selected' : '';
                echo "<option value=\"$role\" $selected>$role</option>";
            }
            ?>
        </select>
    </div>
    <button class="btn btn-primary">Сохранить</button>
    <?php if ($editId): ?>
        <a href="users.php" class="btn btn-secondary">Отмена</a>
    <?php endif; ?>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Пароль</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control"
                                value="<?= htmlspecialchars($u['password']) ?>" readonly>
                            <button type="button" class="btn btn-outline-secondary toggle-password">👁️</button>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="?edit=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
                            <a href="?delete=1&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Удалить пользователя?')">Удалить</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.textContent = isHidden ? '🙈' : '👁️';
        });
    });
</script>

<?php require_once __DIR__ . '/modules/_footer.php'; ?>