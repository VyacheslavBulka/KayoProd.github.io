<?php
require_once __DIR__ . '/../php/connect.php';

$submitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация данных
    $fullname = trim($_POST['fullname'] ?? '');
    $vk_link = trim($_POST['vk_link'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $experience = trim($_POST['experience'] ?? '');

    // Проверка обязательных полей
    if (empty($fullname))
        $errors['fullname'] = 'Введите ваш NickName';
    if (empty($vk_link))
        $errors['vk_link'] = 'Укажите ссылку на VK';
    if (empty($position))
        $errors['position'] = 'Укажите желаемую должность';

    // Обработка ссылки VK
    if (!empty($vk_link) && !preg_match('/^(https?:\/\/)?(www\.)?vk\.com\/.+/i', $vk_link)) {
        $vk_link = 'https://vk.com/' . str_replace(['https://', 'http://', 'vk.com/', '@'], '', $vk_link);
    }

    if (empty($errors)) {
        $stmt = $connect->prepare("INSERT INTO employment_forms (user_id, fullname, vk_link, position, experience) VALUES (NULL, ?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $vk_link, $position, $experience);

        if ($stmt->execute()) {
            $submitted = true;
            $_POST = []; // Очищаем поля формы
        } else {
            $errors['database'] = 'Ошибка при сохранении анкеты. Попробуйте позже.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета на трудоустройство | Kayo Prod</title>
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
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .form-header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .form-header h2 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .form-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text);
            font-size: 0.95rem;
        }

        .required-field::after {
            content: " *";
            color: var(--error);
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #fafafa;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(140, 62, 204, 0.2);
            outline: none;
            background-color: white;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }

        .input-with-icon input {
            padding-left: 50px;
        }

        textarea.form-control {
            min-height: 140px;
            resize: vertical;
            line-height: 1.5;
        }

        .form-hint {
            font-size: 0.85rem;
            color: var(--light-text);
            margin-top: 8px;
            display: block;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 16px;
            font-size: 1.05rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .alert {
            padding: 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            margin-right: 12px;
            font-size: 1.3rem;
        }

        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 6px;
            font-size: 0.9rem;
        }

        /* Адаптация для мобильных */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .form-container {
                margin: 20px auto;
                border-radius: 10px;
            }

            .form-header {
                padding: 20px;
            }

            .form-header h2 {
                font-size: 1.6rem;
            }

            .form-body {
                padding: 25px;
            }

            .form-control {
                padding: 12px 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .form-header h2 {
                font-size: 1.4rem;
            }

            .form-header p {
                font-size: 0.85rem;
            }

            .form-body {
                padding: 20px;
            }

            .btn {
                padding: 14px;
                font-size: 1rem;
            }

            .alert {
                padding: 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="form-header">
            <h2>Присоединяйтесь к нашей команде</h2>
            <p>Заполните анкету, и мы рассмотрим вашу кандидатуру</p>
        </div>

        <div class="form-body">
            <?php if ($submitted): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Анкета успешно отправлена!</strong>
                        <p style="margin-top: 6px; font-size: 0.9rem;">Мы свяжемся с вами в ближайшее время.</p>
                    </div>
                </div>
            <?php elseif (!empty($errors['database'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['database']) ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="form-group">
                    <label for="fullname" class="form-label required-field">Ваш NickName</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="fullname" name="fullname" class="form-control"
                            placeholder="Как к вам обращаться?"
                            value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
                    </div>
                    <?php if (!empty($errors['fullname'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['fullname'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="vk_link" class="form-label required-field">Профиль VK</label>
                    <div class="input-with-icon">
                        <i class="fab fa-vk"></i>
                        <input type="text" id="vk_link" name="vk_link" class="form-control"
                            placeholder="https://vk.com/nickname или @nickname"
                            value="<?= htmlspecialchars($_POST['vk_link'] ?? '') ?>" required>
                    </div>
                    <span class="form-hint">Укажите для связи. Можно ввести просто @username</span>
                    <?php if (!empty($errors['vk_link'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['vk_link'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="position" class="form-label required-field">Желаемая должность</label>
                    <div class="input-with-icon">
                        <i class="fas fa-briefcase"></i>
                        <input type="text" id="position" name="position" class="form-control"
                            placeholder="Например: Сотрудник Сцены, Артист, Редактор..."
                            value="<?= htmlspecialchars($_POST['position'] ?? '') ?>" required>
                    </div>
                    <?php if (!empty($errors['position'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i>
                            <?= $errors['position'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="experience" class="form-label">Опыт и навыки</label>
                    <textarea id="experience" name="experience" class="form-control"
                        placeholder="Опишите ваш опыт работы, профессиональные навыки, образование..."><?= htmlspecialchars($_POST['experience'] ?? '') ?></textarea>
                    <span class="form-hint">Не обязательно, но поможет нам лучше понять вашу квалификацию</span>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Отправить анкету
                </button>
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

        // Плавная прокрутка при ошибках
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($errors)): ?>
                const firstError = document.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            <?php endif; ?>
        });
    </script>
</body>

</html>