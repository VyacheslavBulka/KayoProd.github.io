<?php
require_once 'php/connect.php';
require_once 'config/db.php';

$query = "SELECT * FROM maininfo LIMIT 1";
$siteInfo = mysqli_fetch_assoc(mysqli_query($connect, $query)) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($connect, $_POST['name'] ?? '');
    $description = mysqli_real_escape_string($connect, $_POST['description'] ?? '');
    $page_url = mysqli_real_escape_string($connect, $_POST['page_url'] ?? '');
    $created_at = date('Y-m-d H:i:s');

    // Обработка VK ссылки
    $vk_link = trim($_POST['vk_link'] ?? '');
    if (!empty($vk_link)) {
        if (!preg_match('/^(https?:\/\/)?(www\.)?vk\.com\//i', $vk_link)) {
            if (strpos($vk_link, '@') === 0) {
                $vk_link = 'https://vk.com/' . substr($vk_link, 1);
            } else {
                $vk_link = 'https://vk.com/' . $vk_link;
            }
        } elseif (strpos($vk_link, 'http') !== 0) {
            $vk_link = 'https://' . $vk_link;
        }
    }
    $vk_link = mysqli_real_escape_string($connect, $vk_link);

    $query = "INSERT INTO bug_reports (name, vk_link, description, page_url, created_at, status) 
              VALUES ('$name', '$vk_link', '$description', '$page_url', '$created_at', 'new')";
    $result = mysqli_query($connect, $query);

    if ($result) {
        header('Location: bug-report.php?success=1');
        exit;
    } else {
        // Обработка ошибки базы данных
        die("Ошибка при сохранении: " . mysqli_error($connect));
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сообщить об ошибке | <?= htmlspecialchars($siteInfo['sitename'] ?? 'Kayo Prod') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8c3ecc;
            --primary-hover: #a94eef;
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

        .bug-report-container {
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

        .bug-report-header {
            background: linear-gradient(135deg, var(--primary), #6a3093);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .bug-report-header h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .bug-report-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .bug-report-body {
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

        textarea.form-control {
            min-height: 150px;
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

        /* Адаптация для мобильных */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .bug-report-container {
                margin: 15px auto;
                border-radius: 10px;
            }

            .bug-report-header {
                padding: 20px;
            }

            .bug-report-header h2 {
                font-size: 1.5rem;
            }

            .bug-report-body {
                padding: 20px;
            }

            .form-control {
                padding: 10px 12px;
            }
        }

        @media (max-width: 480px) {
            .bug-report-header h2 {
                font-size: 1.3rem;
            }

            .bug-report-header p {
                font-size: 0.85rem;
            }

            .btn {
                padding: 12px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>
    <div class="bug-report-container">
        <div class="bug-report-header">
            <h2><i class="fas fa-bug"></i> Сообщить об ошибке</h2>
            <p>Помогите нам сделать сервис лучше! Опишите проблему, с которой вы столкнулись.</p>
        </div>

        <div class="bug-report-body">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Спасибо за ваше сообщение!</strong>
                        <p style="margin-top: 5px; font-size: 0.9rem;">Мы рассмотрим ваше сообщение в ближайшее время.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="bug-report.php" method="POST" class="bug-report-form" novalidate>
                <div class="form-group">
                    <label for="name" class="form-label required-field">Ваше имя</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Как к вам обращаться?"
                        required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="vk_link" class="form-label required-field">VK для обратной связи</label>
                    <input type="text" id="vk_link" name="vk_link" class="form-control"
                        placeholder="https://vk.com/username" required
                        value="<?= htmlspecialchars($_POST['vk_link'] ?? '') ?>">
                    <span class="form-hint">Можно указать просто @username или vk.com/username</span>
                </div>

                <div class="form-group">
                    <label for="page_url" class="form-label">Страница, где обнаружена ошибка</label>
                    <input type="url" id="page_url" name="page_url" class="form-control"
                        placeholder="https://kayoprod/index.php"
                        value="<?= htmlspecialchars($_POST['page_url'] ?? '') ?>">
                    <span class="form-hint">Укажите URL страницы, где произошла ошибка (если помните)</span>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label required-field">Подробное описание ошибки</label>
                    <textarea id="description" name="description" class="form-control"
                        placeholder="Опишите, что произошло, какие действия привели к ошибке, какого поведения вы ожидали..."
                        required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <span class="form-hint">Чем подробнее вы опишете проблему, тем быстрее мы сможем её исправить</span>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Отправить отчет
                </button>
            </form>
        </div>
    </div>

    <script>
        // Автоматическое добавление текущего URL, если пользователь не указал страницу
        document.addEventListener('DOMContentLoaded', function () {
            const pageUrlField = document.getElementById('page_url');

            if (!pageUrlField.value && document.referrer) {
                pageUrlField.value = document.referrer;
            }

            // Если поле все еще пустое, попробуем получить текущий URL
            if (!pageUrlField.value && window.location.href !== 'about:blank') {
                pageUrlField.value = window.location.href;
            }
        });
    </script>
</body>

</html>