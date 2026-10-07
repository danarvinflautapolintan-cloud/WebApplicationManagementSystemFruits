<?php
// login.php
require_once 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrutasPH - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: #F5F3EE;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .login-box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 380px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .login-box h1 {
            font-size: 22px;
            font-weight: 800;
            color: #2D5A27;
            margin: 0;
        }
        .login-box .subtitle {
            color: #888;
            margin: 4px 0 0;
            font-size: 14px;
        }
        .error {
            background: #FEE2E2;
            color: #991B1B;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            display: block;
            margin-bottom: 4px;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1.5px solid #E0DDD8;
            font-size: 14px;
            margin-bottom: 14px;
            box-sizing: border-box;
            outline: none;
        }
        input:focus {
            border-color: #4F7942;
        }
        button {
            width: 100%;
            background: #4F7942;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }
        button:hover {
            background: #3d6133;
        }
        .text-center { text-align: center; }
        .emoji { font-size: 48px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="text-center">
            <div class="emoji">🍎</div>
            <h1>FrutasPH</h1>
            <p class="subtitle">Philippine Fruits Inventory</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            
            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>