<?php
/**
 * index.php — Login Page
 * Uses plain text password comparison
 */

session_start();

if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit;
}

require_once __DIR__ . '/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
        ]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = $user['username'];
            header("Location: home.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Student Management System</title>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #ecbebe;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-wrapper { width: 100%; max-width: 420px; padding: 16px; }

    .login-card {
        background-color: #aac7ee;
        border-radius: 16px;
        padding: 44px 42px;
        box-shadow: 0 8px 32px rgba(0,0,0,.12);
        text-align: center;
    }

    .login-card .icon { font-size: 2.8rem; margin-bottom: 10px; }

    .login-card h2 {
        font-size: 1.25rem;
        color: #1a1a2e;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .login-card p { font-size: .88rem; color: #888; margin-bottom: 30px; }

    .form-group { margin-bottom: 18px; text-align: left; }

    .form-group label {
        display: block;
        font-size: .85rem;
        font-weight: 600;
        color: #444;
        margin-bottom: 6px;
    }

    .form-group input {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid #d0d5dd;
        border-radius: 8px;
        font-size: .95rem;
        color: #222;
        outline: none;
        background: #fafafa;
        transition: border .2s, box-shadow .2s;
    }

    .form-group input:focus {
        border-color: #e94560;
        box-shadow: 0 0 0 3px rgba(233,69,96,.12);
        background: #fff;
    }

    .error-box {
        background: #fff0f3;
        border: 1px solid #ffc2cc;
        border-radius: 8px;
        padding: 10px 14px;
        color: #c0143c;
        font-size: .87rem;
        font-weight: 600;
        margin-bottom: 18px;
        text-align: left;
    }

    .btn-login {
        width: 100%;
        padding: 12px;
        background: #1a1a2e;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s, transform .1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 6px;
    }

    .btn-login:hover  { background: #2e2e50; }
    .btn-login:active { transform: scale(.98); }

    .hint { margin-top: 22px; font-size: .78rem; color: #bbb; }
    .hint strong { color: #888; }
</style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Welcome to Student Management System</h2>
        <p>Enter your Credentials to Login!</p>

        <?php if (!empty($error)): ?>
            <div class="error-box">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       placeholder="Enter username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       placeholder="Enter password"
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Login</button>

        </form>
    </div>
</div>

</body>
</html>