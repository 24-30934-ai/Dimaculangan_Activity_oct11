<?php
require_once __DIR__ . '/accounts.php';

session_start();

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    if (
        preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/D', $email) !== 1
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false
    ) {
        $errors[] = 'Enter a valid email address.';
    }
    if (preg_match('/\A[^\r\n]{1,128}\z/D', $password) !== 1) {
        $errors[] = 'Password is required and must be 128 characters or fewer.';
    }

    if ($errors === []) {
        try {
            if (account_login_is_valid($email, $password)) {
                session_regenerate_id(true);
                $_SESSION['user_email'] = $email;
                header('Location: Landingpage.php');
                exit;
            } else {
                $errors[] = 'The email or password is incorrect. If you do not have an account, sign up first.';
            }
        } catch (RuntimeException $exception) {
            error_log($exception->getMessage());
            $errors[] = 'Login is unavailable because the account file could not be read.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | Welcome back</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-shell">
        <div class="auth-layout">
            <aside class="meme-panel meme-left" aria-label="Meme">
                <img src="https://media.tenor.com/lFtDRlWN0HEAAAAM/gerry-fakhri-meme.gif" alt="Funny reaction meme">
            </aside>

            <section class="auth-card" aria-labelledby="login-title">
                <a href="Landingpage.php" aria-label="Filipino Meme's home"><span class="brand-mark">RR</span></a>
                <p class="eyebrow">Welcome back</p>
                <h1 id="login-title">Log in to your account</h1>
                <p class="subtitle">Enter your details to continue.</p>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" autocomplete="email" required>
                    </div>

                    <div class="mb-2">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" maxlength="128" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a class="small-link" href="#">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 submit-button">Log in</button>
                </form>

                <p class="account-prompt">Don't have an account yet? <a href="register.php">Sign up</a></p>
            </section>

            <aside class="meme-panel meme-right" aria-label="Meme">
                <img src="https://media.tenor.com/iY6Ci8VbCU0AAAAM/totoy-abnoy-evil-laugh.gif" alt="Funny evil laugh meme">
            </aside>
        </div>
    </main>
</body>
</html>
