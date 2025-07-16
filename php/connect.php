<?php
$db_host = '127.0.0.1';
$db_name = 'kayoprod';
$db_user = 'root';
$db_pass = 'root';

$connect = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
    die('Ошибка подключения к базе данных: ' . mysqli_connect_error());
}
mysqli_set_charset($connect, 'utf8');

function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    $data = stripslashes($data); // Убираем лишние слеши
    return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
