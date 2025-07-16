<?php
require_once __DIR__ . '/auth.php';
requireLogin();

// Проверка прав доступа
if (!in_array(currentUser()['role'], ['Администратор'])) {
    header('Location: /admin/');
    exit();
}

// Подключаем конфигурацию базы данных
require_once __DIR__ . '/../config/db.php';

// Получаем текущие данные
try {
    $stmt = $pdo->query("SELECT * FROM maininfo WHERE id = 1");
    $currentData = $stmt->fetch();
    
    if (!$currentData) {
        die("Запись с id=1 не найдена в таблице maininfo");
    }
} catch (PDOException $e) {
    die("Ошибка при получении данных: " . $e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE maininfo SET 
            sitename = :sitename,
            link_vk = :link_vk,
            email = :email,
            address = :address,
            phone = :phone,
            logo_path = :logo_path,
            promo_image1 = :promo_image1,
            promo_text1 = :promo_text1,
            promo_image2 = :promo_image2,
            promo_text2 = :promo_text2,
            promo_nametext1 = :promo_nametext1,
            promo_nametext2 = :promo_nametext2,
            promo_nametext3 = :promo_nametext3,
            updated_at = NOW()
            WHERE id = 1");

        $stmt->execute([
            ':sitename' => $_POST['sitename'],
            ':link_vk' => $_POST['link_vk'],
            ':email' => $_POST['email'],
            ':address' => $_POST['address'],
            ':phone' => $_POST['phone'],
            ':logo_path' => $_POST['logo_path'],
            ':promo_image1' => $_POST['promo_image1'],
            ':promo_text1' => $_POST['promo_text1'],
            ':promo_image2' => $_POST['promo_image2'],
            ':promo_text2' => $_POST['promo_text2'],
            ':promo_nametext1' => $_POST['promo_nametext1'],
            ':promo_nametext2' => $_POST['promo_nametext2'],
            ':promo_nametext3' => $_POST['promo_nametext3']
        ]);

        $success = "Данные успешно обновлены!";
        // Обновляем текущие данные
        $stmt = $pdo->query("SELECT * FROM maininfo WHERE id = 1");
        $currentData = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Ошибка при обновлении данных: " . $e->getMessage();
    }
}

require_once __DIR__ . '/modules/_header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование основной информации | KayoProd</title>
    <link rel="stylesheet" href="/../css/header.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .form-section {
            background-color: var(--bs-body-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        textarea {
            min-height: 100px;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
            display: block;
        }
        .image-preview.empty {
            display: none;
        }
        .image-upload-container {
            margin-bottom: 20px;
        }
        h2.section-title {
            border-bottom: 2px solid var(--bs-primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--bs-primary);
        }
        @media (max-width: 768px) {
            .form-section {
                padding: 15px;
            }
            .row > div {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Редактирование основной информации сайта</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-section">
                <h2 class="section-title">Основная информация</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sitename" class="form-label">Название сайта</label>
                            <input type="text" class="form-control" id="sitename" name="sitename" 
                                   value="<?= htmlspecialchars($currentData['sitename'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($currentData['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($currentData['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="link_vk" class="form-label">Ссылка VK</label>
                            <input type="url" class="form-control" id="link_vk" name="link_vk" 
                                   value="<?= htmlspecialchars($currentData['link_vk'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Адрес</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= htmlspecialchars($currentData['address'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group image-upload-container">
                            <label for="logo_path" class="form-label">Путь к логотипу</label>
                            <input type="text" class="form-control" id="logo_path" name="logo_path" 
                                   value="<?= htmlspecialchars($currentData['logo_path'] ?? '') ?>">
                            <img src="/<?= htmlspecialchars($currentData['logo_path'] ?? '') ?>" 
                                 alt="Текущий логотип" class="image-preview <?= empty($currentData['logo_path']) ? 'empty' : '' ?>" 
                                 id="logo_path_preview">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2 class="section-title">Промо-блоки</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group image-upload-container">
                            <label for="promo_image1" class="form-label">Изображение промо 1 (URL)</label>
                            <input type="text" class="form-control" id="promo_image1" name="promo_image1" 
                                   value="<?= htmlspecialchars($currentData['promo_image1'] ?? '') ?>">
                            <img src="<?= htmlspecialchars($currentData['promo_image1'] ?? '') ?>" 
                                 alt="Промо изображение 1" class="image-preview <?= empty($currentData['promo_image1']) ? 'empty' : '' ?>" 
                                 id="promo_image1_preview">
                        </div>
                        
                        <div class="form-group">
                            <label for="promo_text1" class="form-label">Текст промо 1</label>
                            <textarea class="form-control" id="promo_text1" name="promo_text1" 
                                      required><?= htmlspecialchars($currentData['promo_text1'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group image-upload-container">
                            <label for="promo_image2" class="form-label">Изображение промо 2 (URL)</label>
                            <input type="text" class="form-control" id="promo_image2" name="promo_image2" 
                                   value="<?= htmlspecialchars($currentData['promo_image2'] ?? '') ?>">
                            <img src="<?= htmlspecialchars($currentData['promo_image2'] ?? '') ?>" 
                                 alt="Промо изображение 2" class="image-preview <?= empty($currentData['promo_image2']) ? 'empty' : '' ?>" 
                                 id="promo_image2_preview">
                        </div>
                        
                        <div class="form-group">
                            <label for="promo_text2" class="form-label">Текст промо 2</label>
                            <textarea class="form-control" id="promo_text2" name="promo_text2" 
                                      required><?= htmlspecialchars($currentData['promo_text2'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="promo_nametext1" class="form-label">Промо текст 1 (заголовок)</label>
                            <textarea class="form-control" id="promo_nametext1" name="promo_nametext1" 
                                      required><?= htmlspecialchars($currentData['promo_nametext1'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="promo_nametext2" class="form-label">Промо текст 2 (заголовок)</label>
                            <textarea class="form-control" id="promo_nametext2" name="promo_nametext2" 
                                      required><?= htmlspecialchars($currentData['promo_nametext2'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="promo_nametext3" class="form-label">Промо текст 3 (заголовок)</label>
                            <textarea class="form-control" id="promo_nametext3" name="promo_nametext3" 
                                      required><?= htmlspecialchars($currentData['promo_nametext3'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="/admin/" class="btn btn-secondary">Назад</a>
                <button type="submit" name="submit" class="btn btn-primary">Сохранить изменения</button>
            </div>
        </form>
    </div>

    <?php require_once __DIR__ . '/modules/_footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Функция для предпросмотра изображений
        function setupImagePreview(inputId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(inputId + '_preview');
            
            input.addEventListener('input', function() {
                if (this.value) {
                    preview.src = this.value;
                    preview.classList.remove('empty');
                } else {
                    preview.classList.add('empty');
                }
            });
        }
        
        // Настройка предпросмотра для всех изображений
        setupImagePreview('logo_path');
        setupImagePreview('promo_image1');
        setupImagePreview('promo_image2');
    </script>
</body>
</html>