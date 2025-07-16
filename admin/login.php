<?php
require_once __DIR__ . '/auth.php';

if (currentUser()) {
    header('Location: /admin/');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
    $stmt->execute(['u' => $_POST['username']]);
    $user = $stmt->fetch();

    if ($user && $_POST['password'] === $user['password']) {
        $_SESSION['Пользователь'] = $user;
        header('Location: /admin/');
        exit;
    } else {
        $errors[] = 'Неверный логин или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #343a40;
        }
        
        .login-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .form-control {
            height: 45px;
            border-radius: 5px;
            padding: 10px 15px;
        }
        
        .btn-login {
            height: 45px;
            border-radius: 5px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .alert-danger {
            border-radius: 5px;
            padding: 10px 15px;
        }
        
        @media (max-width: 576px) {
            body {
                padding: 20px;
            }
            
            .login-card {
                padding: 20px;
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
            
            .login-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card login-card p-4 mx-auto">
                    <div class="login-header">
                        <h3 class="login-title">Админ-Панель</h3>
                        <h4 class="login-subtitle">Авторизация</h4>
                    </div>
                    
                    <?php foreach ($errors as $e): ?>
                        <div class="alert alert-danger mb-3"><?= htmlspecialchars($e) ?></div>
                    <?php endforeach ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <input type="text" name="username" class="form-control" placeholder="Логин" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                        </div>
                        <button class="btn btn-primary btn-login w-100">Войти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>