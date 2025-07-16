<?php
require_once __DIR__ . '/../php/connect.php';

$submitted = false;
$errors = [];
$isEditMode = isset($_GET['edit']); // Проверяем режим редактирования

// Если это режим редактирования, загружаем существующие данные
if ($isEditMode) {
    $editId = (int) $_GET['edit'];
    $stmt = $connect->prepare("SELECT * FROM order_forms WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingData = $result->fetch_assoc();

    if (!$existingData) {
        die('Заявка не найдена');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация данных
    $event_name = trim($_POST['event_name'] ?? '');
    $date_wish = trim($_POST['date_wish'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $comments = trim($_POST['comments'] ?? '');
    $vk_link = trim($_POST['vk_link'] ?? '');

    // Проверка обязательных полей
    if (empty($event_name))
        $errors['event_name'] = 'Введите название мероприятия';
    if (empty($date_wish))
        $errors['date_wish'] = 'Укажите желаемую дату';
    if (empty($budget))
        $errors['budget'] = 'Укажите бюджет';
    if (empty($comments))
        $errors['comments'] = 'Добавьте комментарий';

    // Обработка ссылки VK
    if (!empty($vk_link)) {
        if (!preg_match('/^(https?:\/\/)?(www\.)?vk\.com\/.+/i', $vk_link)) {
            $vk_link = 'https://vk.com/' . str_replace(['https://', 'http://', 'vk.com/', '@'], '', $vk_link);
        }
    }

    if (empty($errors)) {
        if ($isEditMode) {
            // Редактирование существующей записи
            $stmt = $connect->prepare("UPDATE order_forms SET event_name=?, date_wish=?, budget=?, comments=?, vk_link_user=? WHERE id=?");
            $stmt->bind_param("ssdssi", $event_name, $date_wish, $budget, $comments, $vk_link, $editId);
        } else {
            // Создание новой записи
            $stmt = $connect->prepare("INSERT INTO order_forms (user_id, event_name, date_wish, budget, comments, vk_link_user) VALUES (NULL, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdss", $event_name, $date_wish, $budget, $comments, $vk_link);
        }

        if ($stmt->execute()) {
            $submitted = true;

            if ($isEditMode) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Заявка успешно обновлена'];
                header('Location: orders.php'); // Перенаправляем на список заказов
                exit;
            } else {
                // Очищаем поля после успешной отправки (только для новой заявки)
                $_POST = [];
            }
        } else {
            $errors['database'] = 'Ошибка при сохранении заявки. Попробуйте позже.';
        }
    }
}

// Если это редактирование и данные не отправлялись, заполняем форму существующими данными
if ($isEditMode && !$_POST && $existingData) {
    $_POST['event_name'] = $existingData['event_name'];
    $_POST['date_wish'] = $existingData['date_wish'];
    $_POST['budget'] = $existingData['budget'];
    $_POST['comments'] = $existingData['comments'];
    $_POST['vk_link'] = $existingData['vk_link_user'];
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditMode ? 'Редактирование заказа' : 'Заказ мероприятия' ?> | Kayo Prod</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8c3ecc;
            --primary-hover: #a94eef;
            --secondary: #4a76a8;
            --error: #e74c3c;
            --success: #28a745;
            --text: #333;
            --light-text: #666;
            --border: #ddd;
            --bg: #f5f5f5;
            --card-bg: #fff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
        }

        .form-container {
            max-width: 700px;
            margin: 30px auto;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary), #6a3093);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .form-header h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .form-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }

        .required-field::after {
            content: " *";
            color: var(--error);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(140, 62, 204, 0.2);
            outline: none;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }

        .input-with-icon input {
            padding-left: 45px;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-hint {
            font-size: 0.85rem;
            color: var(--light-text);
            margin-top: 5px;
            display: block;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn i {
            margin-right: 8px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .date-wrapper {
            display: flex;
            align-items: center;
        }

        .date-wrapper .form-control {
            flex: 1;
        }

        .date-icon {
            margin-left: 10px;
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* Адаптация для мобильных */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-container {
                margin: 15px auto;
                border-radius: 10px;
            }

            .form-header {
                padding: 15px;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }

            .form-body {
                padding: 20px;
            }

            .form-control {
                padding: 10px 12px;
            }
        }

        @media (max-width: 480px) {
            .form-header h2 {
                font-size: 1.3rem;
            }

            .form-header p {
                font-size: 0.85rem;
            }

            .btn {
                padding: 12px;
                font-size: 0.95rem;
            }
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="form-header">
            <h2><?= $isEditMode ? 'Редактирование заказа' : 'Заказ мероприятия' ?></h2>
            <p><?= $isEditMode ? 'Измените данные заявки' : 'Заполните форму, и мы свяжемся с вами для уточнения деталей' ?>
            </p>
        </div>

        <div class="form-body">
            <?php if ($submitted && !$isEditMode): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Заявка успешно <?= $isEditMode ? 'обновлена' : 'отправлена' ?>!</strong>
                        <p style="margin-top: 5px; font-size: 0.9rem;">Мы свяжемся с вами в ближайшее время.</p>
                    </div>
                </div>
            <?php elseif (!empty($errors['database'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['database']) ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="edit_id" value="<?= $editId ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="event_name" class="form-label required-field">Название мероприятия</label>
                    <input type="text" id="event_name" name="event_name" class="form-control"
                        placeholder="Концерт, фестиваль, корпоратив..."
                        value="<?= htmlspecialchars($_POST['event_name'] ?? '') ?>" required>
                    <?php if (!empty($errors['event_name'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['event_name'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="date_wish" class="form-label required-field">Желаемая дата</label>
                    <div class="date-wrapper">
                        <input type="date" id="date_wish" name="date_wish" class="form-control"
                            value="<?= htmlspecialchars($_POST['date_wish'] ?? '') ?>" required>
                        <i class="fas fa-calendar-alt date-icon"></i>
                    </div>
                    <?php if (!empty($errors['date_wish'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['date_wish'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="budget" class="form-label required-field">Бюджет (руб.)</label>
                    <input type="number" id="budget" name="budget" class="form-control" placeholder="10000" min="0"
                        step="100" value="<?= htmlspecialchars($_POST['budget'] ?? '') ?>" required>
                    <span class="form-hint">Укажите приблизительную сумму, которую вы готовы выделить на
                        мероприятие</span>
                    <?php if (!empty($errors['budget'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['budget'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="vk_link" class="form-label">Ваш профиль VK</label>
                    <div class="input-with-icon">
                        <i class="fab fa-vk"></i>
                        <input type="text" id="vk_link" name="vk_link" class="form-control"
                            placeholder="https://vk.com/username или @username"
                            value="<?= htmlspecialchars($_POST['vk_link'] ?? '') ?>">
                    </div>
                    <span class="form-hint">Укажите для быстрой связи. Можно ввести просто @username</span>
                </div>

                <div class="form-group">
                    <label for="comments" class="form-label required-field">Комментарий</label>
                    <textarea id="comments" name="comments" class="form-control"
                        placeholder="Опишите детали мероприятия, ваши пожелания, ожидания..."
                        required><?= htmlspecialchars($_POST['comments'] ?? '') ?></textarea>
                    <?php if (!empty($errors['comments'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['comments'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn" style="flex-grow: 1;">
                        <i class="fas fa-<?= $isEditMode ? 'save' : 'paper-plane' ?>"></i>
                        <?= $isEditMode ? 'Сохранить изменения' : 'Отправить заявку' ?>
                    </button>

                    <?php if ($isEditMode): ?>
                        <a href="orders.php" class="btn btn-secondary" style="flex-grow: 1;">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Автоматическое форматирование VK ссылки
        document.getElementById('vk_link').addEventListener('blur', function () {
            let vkLink = this.value.trim();
            if (vkLink) {
                // Если введен @username или просто username
                if (vkLink.startsWith('@') || !vkLink.includes('/')) {
                    vkLink = vkLink.replace('@', '');
                    this.value = 'https://vk.com/' + vkLink;
                }
                // Если введен vk.com/ без https
                else if (vkLink.startsWith('vk.com/')) {
                    this.value = 'https://' + vkLink;
                }
            }
        });

        // Установка минимальной даты (сегодня)
        document.addEventListener('DOMContentLoaded', function () {
            const dateField = document.getElementById('date_wish');
            const today = new Date().toISOString().split('T')[0];
            dateField.min = today;

            // Если поле пустое, установить завтрашнюю дату по умолчанию (только для новой заявки)
            if (!dateField.value && !<?= $isEditMode ? 'true' : 'false' ?>) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                dateField.value = tomorrow.toISOString().split('T')[0];
            }
        });
    </script>
</body>

</html>