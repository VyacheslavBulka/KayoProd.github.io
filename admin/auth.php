<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

/**
 * Проверка авторизации
 */
function currentUser(): ?array
{
    return $_SESSION['Пользователь'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function requireRole(string ...$roles): void
{
    requireLogin();
    if (!in_array(currentUser()['role'], $roles, true)) {
        http_response_code(403);
        echo 'Доступ запрещён';
        exit;
    }
}
