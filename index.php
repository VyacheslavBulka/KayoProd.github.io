<?php
session_start();
//echo '<pre>SESSION: ' . print_r($_SESSION, true) . '</pre>';
require_once 'php/connect.php';
require_once 'config/db.php';
$query = "SELECT * FROM maininfo LIMIT 1";
$siteInfo = mysqli_fetch_assoc(mysqli_query($connect, $query)) ?? [];

$userRole = 'Пользователь'; // значение по умолчанию

if (isset($_SESSION['Пользователь']['role'])) {
    $userRole = $_SESSION['Пользователь']['role'];
}

if (isset($_SESSION['Пользователь']['id']) && $_SESSION['Пользователь']['id'] > 0) {
    $userId = (int) $_SESSION['Пользователь']['id'];
    $query = "SELECT role FROM users WHERE id = $userId LIMIT 1";
    $result = mysqli_query($connect, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['role'])) {
            $userRole = $row['role'];
        }
        mysqli_free_result($result);
    }
}
?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteInfo['sitename'] ?? 'Kayo Prod - Организация концертов') ?></title>
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="shortcut icon" href="image/favicon.ico" type="image/x-icon">
</head>

<body>
    <?php if ($userRole !== 'Пользователь'): ?>
        <div class="admin-banner">
            Вы вошли как <strong><?= htmlspecialchars($userRole) ?></strong>.
            <a href="/admin/login.php" style="color: #000; text-decoration: underline; margin-left: 10px;">Перейти в
                админ-панель</a>
        </div>
        <style>
            /* Чтобы header и остальное не оказались под плашкой */
            header {
                margin-top: 40px;
                /* или высота плашки */
            }

            .no-news {
                text-align: center;
                padding: 2rem;
                grid-column: 1 / -1;
            }

            .no-news-icon {
                font-size: 3rem;
                color: #ccc;
                margin-bottom: 1rem;
            }

            .no-news h3 {
                color: #333;
                margin-bottom: 0.5rem;
            }

            .no-news p {
                color: #666;
                margin-bottom: 1.5rem;
            }
        </style>
    <?php endif; ?>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">Kayo<span>Prod</span></a>
                <ul class="nav-links">
                    <li><a onclick="scrollToInfo('#home')">Главная</a></li>
                    <li><a onclick="scrollToInfo('#about')">О нас</a></li>
                    <li><a onclick="scrollToInfo('#news')">Новости</a></li>
                    <li><a onclick="scrollToInfo('#team')">Команда</a></li>
                    <li><a onclick="scrollToInfo('#contact')">Контакты</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1><?= escape($siteInfo['promo_text1']) ?></h1>
                <p><?= escape($siteInfo['promo_text2']) ?>
                </p>
                <a href="<?= escape($siteInfo['link_vk'] ?? '#') ?>" class="btn">Связаться с нами</a>
            </div>
        </div>
    </section>
    <section class="applications-section">
        <div class="container">
            <div class="section-title">
                <h2>Подайте заявку</h2>
            </div>
            <div class="applications-grid">
                <!-- Трудоустройство -->
                <div class="application-card">
                    <h3>Хотите стать частью нашей команды?</h3>
                    <p>Заполните анкету на трудоустройство. Укажите свои навыки, опыт и мы обязательно свяжемся с вами!
                    </p>
                    <a href="statements/employment-form.php" class="btn full-width">Заполнить анкету</a>
                </div>

                <!-- Заказ мероприятия -->
                <div class="application-card">
                    <h3>Планируете мероприятие?</h3>
                    <p>Расскажите нам о вашем будущем событии. Мы предложим наилучшее решение по организации и
                        проведению!</p>
                    <a href="statements/order-form.php" class="btn full-width">Оформить заказ</a>
                </div>
            </div>
        </div>
    </section>


    <section id="about" class="about">
        <div class="container">
            <div class="section-title">
                <h2>О Kayo Prod</h2>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <p><?= escape($siteInfo['promo_nametext1']) ?></p>
                    <p><?= escape($siteInfo['promo_nametext2']) ?></p>
                    <p><?= escape($siteInfo['promo_nametext3']) ?></p>
                </div>
                <div class="about-image">
                    <img src="<?= escape($siteInfo['promo_image2']) ?>" alt="О компании">
                </div>
            </div>
        </div>
    </section>

    <section id="news" class="news">
        <div class="container">
            <div class="section-title">
                <h2>Новости и события</h2>
            </div>
            <div class="news-grid">
                <?php
                $query = "SELECT * FROM news ORDER BY date DESC, id DESC LIMIT 3";
                $result = mysqli_query($connect, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Определяем путь к изображению
                        $imagePath = !empty($row['filename']) ? escape($row['filename']) : 'image/standart.jpg';

                        echo '<div class="news-card">
                        <div class="news-image">
                            <img src="' . $imagePath . '" alt="' . escape($row['nametext']) . '" onerror="this.src=\'image/standart.jpg\'">
                        </div>
                        <div class="news-content">
                            <div class="news-date">' . escape($row['date']) . '</div>
                            <h3 class="news-title">' . escape($row['nametext']) . '</h3>
                            <p>' . escape(mb_substr($row['text'], 0, 200)) . '...</p>
                            <a href="' . escape($row['link']) . '" class="btn" target="_blank" style="margin-top: 1rem;">Подробнее</a>
                        </div>
                    </div>';
                    }
                } else {
                    echo '<div class="no-news">
                    <div class="no-news-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>Новостей пока нет</h3>
                    <p>Следите за обновлениями, скоро здесь появятся свежие новости!</p>
                </div>';
                }

                mysqli_free_result($result);
                ?>
            </div>
        </div>
    </section>
    <section id="events" class="upcoming-events">
        <div class="container">
            <div class="section-title">
                <h2>Предстоящие концерты</h2>
            </div>
            <div class="events-grid">
                <?php
                $query = "SELECT * FROM order_forms WHERE approved = TRUE AND date_wish >= CURDATE() ORDER BY date_wish ASC";
                $result = mysqli_query($connect, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="event-card">
                        <div class="event-date-badge">
                            <span class="event-day">' . date('d', strtotime($row['date_wish'])) . '</span>
                            <span class="event-month">' . date('M', strtotime($row['date_wish'])) . '</span>
                        </div>
                        <div class="event-content">
                            <h3 class="event-title">' . escape($row['event_name']) . '</h3>
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="fas fa-wallet mobile-hidden"></i>
                                    <span>' . escape($row['budget']) . ' руб.</span>
                                </div>
                                ' . (!empty($row['comments']) ?
                            '<div class="event-detail">
                                    <i class="fas fa-comment mobile-hidden"></i>
                                    <span>' . escape($row['comments']) . '</span>
                                </div>' : '') . '
                            </div>
                            <div class="event-actions">
                                <a href="' . escape($siteInfo['link_vk'] ?? '#') . '" class="btn btn-outline">Подробнее</a>
                            </div>
                        </div>
                    </div>';
                    }
                } else {
                    echo '<div class="no-events">
                    <div class="no-events-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Предстоящих концертов нет</h3>
                    <p>Следите за нашими новостями, чтобы не пропустить новые мероприятия!</p>
                    <a onclick="scrollToInfo(\'#news\')" class="btn">Посмотреть новости</a>
                </div>';
                }
                mysqli_free_result($result);
                ?>
            </div>
        </div>
    </section>
    <section id="team" class="employees-section">

        <div class="container">
            <div class="section-title">
                <h2>Наша команда</h2>
            </div>
            <div class="employees-grid">
                <?php
                $query = "SELECT * FROM team";
                $result = mysqli_query($connect, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="employee-card">
                    <h3 class="employee-name">' . escape($row['name']) . '</h3>
                    <p class="employee-position">' . escape($row['position']) . '</p>
                    <a href="' . escape($row['vk_link']) . '" class="vk-link" target="_blank"><i class="fab fa-vk"></i> ВКонтакте</a>
                </div>';
                }
                mysqli_free_result($result);
                ?>
            </div>
        </div>
    </section>
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-title">
                <h2>Контакты</h2>
            </div>
            <div class="contact-info">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Адрес</h3>
                    <p><?= escape($siteInfo['address'] ?? 'Отсутствует') ?></p>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fab fa-vk"></i>
                    </div>
                    <h3>VK</h3>
                    <!-- <p><?= escape($siteInfo['phone'] ?? '+375 29 - 982 - 55 - 19') ?></p> -->
                    <!-- <a href="<?= escape($siteInfo['link_vk'] ?? '#') ?>" class="vk-link" target="_blank">
                        <i class="fab fa-vk"></i> Написать в VK
                    </a> -->
                    <p><?= escape($siteInfo['link_vk'] ?? 'Отсутствует.') ?></p>
                </div>
                <!-- <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p><?= escape($siteInfo['email'] ?? 'info@kayoprod.ru') ?></p>
                </div> -->
            </div>

        </div>
    </section>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; <?= date('Y') ?> Kayo Prod. Все права защищены.</p>
                <p>Разработка сайта: <a href="https://vk.com/kayoprods" target="_blank">Kayo Prod.</a> и <a
                        href="https://vk.com/vyacheslavbu" target="_blank">Вячеслав Булка</a></p>
            </div>
            <div class="bug-report-link">
                <p>Нашли баги? <a href="bug-report.php">Заполните форму</a></p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>

</html>