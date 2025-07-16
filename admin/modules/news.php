<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../auth.php';
requireRole('Администратор', 'Редактор');

// Конфигурация загрузки изображений
define('UPLOAD_DIR', __DIR__ . '/../../uploads/news/');
define('UPLOAD_URL', '/../../uploads/news/');
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxFileSize = 10 * 1024 * 1024; // 10MB

// Создаем директорию для загрузки, если не существует
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Обработка удаления
if (isset($_GET['delete'], $_GET['id'])) {
    try {
        // Сначала получаем информацию о файле для удаления
        $stmt = $pdo->prepare('SELECT filename FROM news WHERE id = :id');
        $stmt->execute(['id' => $_GET['id']]);
        $news = $stmt->fetch();

        // Удаляем запись из БД
        $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
        $stmt->execute(['id' => $_GET['id']]);

        // Удаляем файл изображения, если он существует и находится в нашей папке загрузок
        if ($news && strpos($news['filename'], UPLOAD_URL) === 0) {
            $filePath = str_replace(UPLOAD_URL, UPLOAD_DIR, $news['filename']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Новость успешно удалена'];
        header('Location: news.php');
        exit;
    } catch (PDOException $e) {
        die('Ошибка при удалении: ' . $e->getMessage());
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = trim($_POST['existing_filename'] ?? '');
    $nametext = trim(stripslashes($_POST['nametext'] ?? ''));
    $text = trim(stripslashes($_POST['text'] ?? ''));
    $link = trim($_POST['link'] ?? '');
    $date = $_POST['date'] ?: date('Y-m-d');

    // Обработка загруженного файла
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];

        // Проверка ошибок загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {
            die('Ошибка загрузки файла: ' . $file['error']);
        }

        // Проверка типа файла
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedTypes)) {
            die('Недопустимый тип файла. Разрешены только JPEG, PNG и WebP.');
        }

        // Проверка размера файла
        if ($file['size'] > $maxFileSize) {
            die('Файл слишком большой. Максимальный размер: 10MB.');
        }

        // Генерируем уникальное имя файла
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = UPLOAD_URL . uniqid('news_') . '.' . $ext;
        $destination = str_replace(UPLOAD_URL, UPLOAD_DIR, $filename);

        // Перемещаем файл
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            die('Ошибка при сохранении файла.');
        }
    }

    // Валидация обязательных полей
    if (empty($nametext)) {
        die('Ошибка: Заголовок обязателен.');
    }

    if (empty($filename)) {
        die('Ошибка: Изображение обязательно.');
    }

    $data = compact('filename', 'nametext', 'text', 'link', 'date');

    try {
        if (empty($_POST['id'])) {
            $sql = 'INSERT INTO news (filename, nametext, text, link, date)
                    VALUES (:filename, :nametext, :text, :link, :date)';
        } else {
            $sql = 'UPDATE news SET filename=:filename, nametext=:nametext, text=:text, link=:link, date=:date
                    WHERE id=:id';
            $data['id'] = $_POST['id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Новость успешно сохранена'];
        header('Location: news.php');
        exit;
    } catch (PDOException $e) {
        die('Ошибка записи в БД: ' . $e->getMessage());
    }
}

// Получение данных для редактирования
$item = null;
$editId = $_GET['edit'] ?? null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $item = $stmt->fetch();
}

// Получение списка новостей
$items = $pdo->query('SELECT * FROM news ORDER BY date DESC, id DESC')->fetchAll();
$pageTitle = 'Управление новостями';
require_once __DIR__ . '/_header.php';

// Отображение flash-сообщений
if (isset($_SESSION['flash'])) {
    echo '<div class="alert alert-' . $_SESSION['flash']['type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['flash']['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['flash']);
}
?>

<style>
    /* Темная тема */
    [data-bs-theme="dark"] {
        --table-bg: #212529;
        --table-color: #dee2e6;
        --table-border-color: #495057;
        --table-hover-bg: #343a40;
        --table-striped-bg: #2c3034;
        --table-active-bg: #373b3e;
    }

    [data-bs-theme="dark"] .card {
        background-color: var(--bs-dark-bg-subtle);
        border-color: var(--bs-border-color);
    }

    [data-bs-theme="dark"] .table {
        --bs-table-bg: var(--table-bg);
        --bs-table-color: var(--table-color);
        --bs-table-border-color: var(--table-border-color);
        --bs-table-striped-bg: var(--table-striped-bg);
        --bs-table-striped-color: var(--table-color);
        --bs-table-active-bg: var(--table-active-bg);
        --bs-table-active-color: var(--table-color);
        --bs-table-hover-bg: var(--table-hover-bg);
        --bs-table-hover-color: var(--table-color);
        color: var(--bs-table-color);
        border-color: var(--bs-table-border-color);
    }

    [data-bs-theme="dark"] .table-light {
        --bs-table-bg: var(--table-bg);
        --bs-table-color: var(--table-color);
        --bs-table-border-color: var(--table-border-color);
        --bs-table-striped-bg: var(--table-striped-bg);
        --bs-table-striped-color: var(--table-color);
        --bs-table-active-bg: var(--table-active-bg);
        --bs-table-active-color: var(--table-color);
        --bs-table-hover-bg: var(--table-hover-bg);
        --bs-table-hover-color: var(--table-color);
    }

    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
        background-color: var(--bs-dark-bg-subtle);
        color: var(--bs-body-color);
        border-color: var(--bs-border-color);
    }

    [data-bs-theme="dark"] .form-control:focus,
    [data-bs-theme="dark"] .form-select:focus {
        background-color: var(--bs-dark-bg-subtle);
        color: var(--bs-body-color);
    }

    [data-bs-theme="dark"] .form-text {
        color: var(--bs-secondary-color) !important;
    }

    [data-bs-theme="dark"] .card-header {
        border-bottom-color: var(--bs-border-color);
    }

    /* Адаптация для мобильных */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .btn-group {
            flex-direction: column;
            gap: 5px;
        }

        .btn-group .btn {
            width: 100%;
            margin-right: 0 !important;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }

        .col-12>.d-flex>a,
        .col-12>.d-flex>button {
            width: 100%;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 mb-0"><?= $pageTitle ?></h2>
                </div>

                <div class="card-body">
                    <!-- Форма добавления/редактирования -->
                    <form method="post" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
                        <input type="hidden" name="existing_filename"
                            value="<?= htmlspecialchars($item['filename'] ?? '') ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nametext" class="form-label">Заголовок новости *</label>
                                <input type="text" id="nametext" name="nametext" class="form-control" required
                                    value="<?= htmlspecialchars($item['nametext'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="date" class="form-label">Дата публикации</label>
                                <input type="date" id="date" name="date" class="form-control"
                                    value="<?= htmlspecialchars($item['date'] ?? date('Y-m-d')) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="image" class="form-label">Изображение *</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*"
                                    <?= empty($item['filename']) ? 'required' : '' ?>>
                                <div class="form-text">Разрешены JPG, PNG, WebP до 10MB</div>

                                <?php if (!empty($item['filename'])): ?>
                                    <div class="mt-2">
                                        <img src="<?= htmlspecialchars($item['filename']) ?>" class="img-thumbnail"
                                            style="max-height: 100px">
                                        <div class="form-text">Текущее изображение</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="link" class="form-label">Ссылка (если есть)</label>
                                <input type="url" id="link" name="link" class="form-control"
                                    value="<?= htmlspecialchars($item['link'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="text" class="form-label">Текст новости</label>
                                <textarea id="text" name="text" rows="5"
                                    class="form-control"><?= htmlspecialchars($item['text'] ?? '') ?></textarea>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="fas fa-save me-2"></i> Сохранить
                                    </button>

                                    <?php if ($editId): ?>
                                        <a href="news.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i> Отмена
                                        </a>
                                    <?php endif; ?>

                                    <a href="/../admin/scripts/vk_import.php" class="btn btn-primary"
                                        onclick="return confirm('Импортировать последние посты из VK?');"
                                        target="_blank">
                                        <i class="fab fa-vk me-2"></i> Импорт из VK
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Таблица новостей -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="120">Дата</th>
                                    <th>Заголовок</th>
                                    <th width="120">Изображение</th>
                                    <th width="150" class="text-end">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $n): ?>
                                    <tr>
                                        <td><?= $n['id'] ?></td>
                                        <td><?= date('d.m.Y', strtotime($n['date'])) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($n['nametext']) ?></div>
                                            <?php if ($n['link']): ?>
                                                <a href="<?= htmlspecialchars($n['link']) ?>" target="_blank"
                                                    class="small text-muted">
                                                    <?= htmlspecialchars(parse_url($n['link'], PHP_URL_HOST)) ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($n['filename']): ?>
                                                <img src="<?= htmlspecialchars($n['filename']) ?>" class="img-fluid rounded"
                                                    style="max-height: 50px">
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="?edit=<?= $n['id'] ?>" class="btn btn-outline-primary"
                                                    title="Редактировать" style="margin-right: 30px">Редактировать
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=1&id=<?= $n['id'] ?>" class="btn btn-outline-danger"
                                                    onclick="return confirm('Удалить эту новость?')" title="Удалить">
                                                    Удалить
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>