<?php
require_once __DIR__ . '/../auth.php';
requireRole('Администратор');

// Конфигурация VK API
$config = [
    'group_id' => '224286659',
    'access_token' => '5decd2755decd2755decd275b95ed9036255dec5decd275358b3cb524662656e967f425',
    'limit' => 10,
    'max_text_length' => 500,
    'min_text_length' => 20,
    'skip_reposts' => true,
    'skip_ads' => true
];

// Функция для очистки текста из VK
function cleanVkText($text) {
    // Удаляем экранированные кавычки (\" и \')
    $text = str_replace(['\"', "\'"], ['"', "'"], $text);
    
    // Остальная очистка (как у вас было)
    $text = preg_replace('/\[(id|club)\d+\|([^\]]+)\]/', '$2', $text);
    $text = preg_replace('/\[[^\]]+\]/', '', $text);
    $text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);
    $text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $text);
    $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);
    $text = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $text);
    $text = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return $text;
}

// Функция для получения лучшего доступного изображения из поста
function getBestImage($post) {
    if (empty($post['attachments'])) {
        return null;
    }
    
    // Сначала проверяем фото
    foreach ($post['attachments'] as $attachment) {
        if ($attachment['type'] === 'photo') {
            if (isset($attachment['photo']['sizes'])) {
                $sizes = $attachment['photo']['sizes'];
                $largest = end($sizes);
                return $largest['url'];
            }
        }
    }
    
    // Затем проверяем видео (берем первый кадр)
    foreach ($post['attachments'] as $attachment) {
        if ($attachment['type'] === 'video') {
            if (isset($attachment['video']['image'])) {
                $sizes = $attachment['video']['image'];
                if (is_array($sizes)) {
                    $largest = end($sizes);
                    return $largest['url'];
                }
                return $attachment['video']['image'];
            }
        }
    }
    
    // Проверяем ссылки (превью ссылки)
    foreach ($post['attachments'] as $attachment) {
        if ($attachment['type'] === 'link') {
            if (isset($attachment['link']['photo']['sizes'])) {
                $sizes = $attachment['link']['photo']['sizes'];
                $largest = end($sizes);
                return $largest['url'];
            }
        }
    }
    
    // Для других типов вложений можно добавить аналогичную обработку
    
    return null;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Импорт новостей из VK | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
        }
        
        .admin-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 15px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .post-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .post-image {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }
        
        .no-image {
            background: linear-gradient(135deg, #f5f7fa, #e4e8eb);
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a1a8b3;
        }
        
        .progress-container {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin: 1.5rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transition: width 0.6s ease;
        }
        
        .btn-vk {
            background-color: #4a76a8;
            color: white;
            border: none;
        }
        
        .btn-vk:hover {
            background-color: #3a5f8a;
            color: white;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a0cb1, #1a65e0);
            color: white;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 0 10px;
            }
            
            .card-header {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">
                        <i class="fab fa-vk me-2"></i>Импорт новостей из VK
                    </h2>
                    <span class="badge bg-light text-dark">
                        Группа ID: <?= htmlspecialchars($config['group_id']) ?>
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <?php
                // Функции cleanVkText и getBestImage остаются как в предыдущем примере
                
                try {
                    echo '<div class="d-flex justify-content-between align-items-center mb-4">';
                    echo '<h4 class="mb-0"><i class="fas fa-sync-alt fa-spin me-2"></i>Импорт новостей</h4>';
                    echo '<span class="badge status-badge bg-primary">Загрузка...</span>';
                    echo '</div>';
                    
                    echo '<div class="progress-container">';
                    echo '<div class="progress-bar" id="importProgress" style="width: 0%"></div>';
                    echo '</div>';
                    
                    $url = "https://api.vk.com/method/wall.get?owner_id=-{$config['group_id']}&count={$config['limit']}&access_token={$config['access_token']}&v=5.131";
                    $response = json_decode(file_get_contents($url), true);

                    if (!isset($response['response']['items'])) {
                        throw new Exception('Ошибка получения данных от VK API');
                    }

                    $items = $response['response']['items'];
                    $importedCount = 0;
                    $skippedCount = 0;
                    $total = count($items);
                    
                    echo '<div class="row" id="postsContainer">';
                    
                    foreach ($items as $index => $post) {
                        $progress = round(($index + 1) / $total * 100);
                        echo "<script>document.getElementById('importProgress').style.width = '{$progress}%';</script>";
                        
                        // Пропускаем репосты и рекламу
                        if (($config['skip_reposts'] && !empty($post['copy_history'])) || 
                           ($config['skip_ads'] && !empty($post['marked_as_ads']))) {
                            $skippedCount++;
                            continue;
                        }
                        
                        $text = cleanVkText($post['text'] ?? '');
                        if (mb_strlen($text) < $config['min_text_length']) {
                            $skippedCount++;
                            continue;
                        }
                        
                        $date = date('Y-m-d', $post['date']);
                        $link = 'https://vk.com/wall-' . $config['group_id'] . '_' . $post['id'];
                        $photo = getBestImage($post);
                        
                        // Проверка дубликатов
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE link = :link");
                        $stmt->execute(['link' => $link]);
                        if ($stmt->fetchColumn() > 0) {
                            $skippedCount++;
                            continue;
                        }
                        
                        // Вставка в базу данных
                        $stmt = $pdo->prepare("INSERT INTO news (filename, date, nametext, text, link) VALUES (:filename, :date, :nametext, :text, :link)");
                        $stmt->execute([
                            'filename' => $photo,
                            'date' => $date,
                            'nametext' => mb_substr($text, 0, 60),
                            'text' => mb_substr($text, 0, $config['max_text_length']),
                            'link' => $link
                        ]);
                        $importedCount++;
                        
                        // Отображение карточки поста
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="post-card card h-100">';
                        
                        if ($photo) {
                            echo '<img src="' . htmlspecialchars($photo) . '" class="post-image" alt="Изображение новости">';
                        } else {
                            echo '<div class="no-image">';
                            echo '<i class="fas fa-image fa-3x"></i>';
                            echo '</div>';
                        }
                        
                        echo '<div class="card-body">';
                        echo '<div class="d-flex justify-content-between align-items-start mb-2">';
                        echo '<h5 class="card-title">' . htmlspecialchars(mb_substr($text, 0, 60)) . '</h5>';
                        echo '<span class="badge bg-light text-dark">' . date('d.m.Y', strtotime($date)) . '</span>';
                        echo '</div>';
                        echo '<p class="card-text text-muted">' . nl2br(htmlspecialchars(mb_substr($text, 0, 150) . (mb_strlen($text) > 150 ? '...' : ''))) . '</p>';
                        echo '</div>';
                        
                        echo '<div class="card-footer bg-white border-top-0">';
                        echo '<div class="d-flex justify-content-between align-items-center">';
                        echo '<a href="' . htmlspecialchars($link) . '" target="_blank" class="btn btn-sm btn-vk">';
                        echo '<i class="fab fa-vk me-1"></i> Открыть';
                        echo '</a>';
                        echo '<span class="badge status-badge bg-success">Импортировано</span>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '</div>'; // закрываем post-card
                        echo '</div>'; // закрываем col-md-6
                    }
                    
                    echo '</div>'; // закрываем row
                    
                    // Итоговый блок
                    echo '<div class="alert alert-success mt-4">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<i class="fas fa-check-circle fa-2x me-3"></i>';
                    echo '<div>';
                    echo '<h4 class="alert-heading mb-2">Импорт завершен!</h4>';
                    echo '<div class="mb-2"><strong>Импортировано:</strong> ' . $importedCount . ' новостей</div>';
                    if ($skippedCount > 0) {
                        echo '<div class="mb-2"><strong>Пропущено:</strong> ' . $skippedCount . ' (дубликаты/репосты)</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<hr>';
                    echo '<div class="d-flex justify-content-between">';
                    echo '<a href="../modules/news.php" class="btn btn-gradient">';
                    echo '<i class="fas fa-newspaper me-2"></i>Перейти к новостям';
                    echo '</a>';
                    echo '<a href="?" class="btn btn-outline-secondary">';
                    echo '<i class="fas fa-redo me-2"></i>Запустить снова';
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<i class="fas fa-exclamation-triangle fa-2x me-3"></i>';
                    echo '<div>';
                    echo '<h4 class="alert-heading mb-2">Ошибка импорта</h4>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    
                    if (isset($response)) {
                        echo '<button class="btn btn-sm btn-outline-dark mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#responseData">';
                        echo '<i class="fas fa-code me-1"></i>Показать ответ API';
                        echo '</button>';
                        echo '<div class="collapse mt-3" id="responseData">';
                        echo '<pre class="bg-light p-3 rounded">' . htmlspecialchars(print_r($response, true)) . '</pre>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Плавная прокрутка к верху страницы
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        });
        
        // Анимация появления карточек
        document.addEventListener('DOMContentLoaded', function() {
            const posts = document.querySelectorAll('.post-card');
            posts.forEach((post, index) => {
                setTimeout(() => {
                    post.style.opacity = '1';
                    post.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>