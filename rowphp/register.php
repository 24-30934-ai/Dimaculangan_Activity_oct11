<?php
require_once __DIR__ . '/accounts.php';

$firstName = '';
$lastName = '';
$email = '';
$errors = [];
$created = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstNameInput = $_POST['firstName'] ?? '';
    $lastNameInput = $_POST['lastName'] ?? '';
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $confirmPasswordInput = $_POST['confirmPassword'] ?? '';

    $firstName = is_string($firstNameInput) ? trim($firstNameInput) : '';
    $lastName = is_string($lastNameInput) ? trim($lastNameInput) : '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';
    $confirmPassword = is_string($confirmPasswordInput) ? $confirmPasswordInput : '';

    $namePattern = '/\A[\p{L}][\p{L}\p{M} .\'-]{0,49}\z/uD';
    if (preg_match($namePattern, $firstName) !== 1) {
        $errors[] = 'First name must start with a letter and contain only letters, spaces, periods, apostrophes, or hyphens (up to 50 characters).';
    }
    if (preg_match($namePattern, $lastName) !== 1) {
        $errors[] = 'Last name must start with a letter and contain only letters, spaces, periods, apostrophes, or hyphens (up to 50 characters).';
    }
    if (
        preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/D', $email) !== 1
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false
    ) {
        $errors[] = 'Enter a valid email address.';
    }
    if (preg_match('/\A(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,128}\z/D', $password) !== 1) {
        $errors[] = 'Password must be 6–128 characters and include a letter, a number, and a symbol.';
    }
    if ($confirmPassword !== $password) {
        $errors[] = 'Passwords do not match.';
    }

    if ($errors === []) {
        try {
            $created = create_account($firstName, $lastName, $email, $password);
            if (!$created) {
                $errors[] = 'An account with this email address already exists.';
            }
        } catch (RuntimeException $exception) {
            error_log($exception->getMessage());
            $errors[] = 'The account could not be saved. Check that the local PHP server can write to its storage folder.';
        } catch (JsonException $exception) {
            error_log($exception->getMessage());
            $errors[] = 'The account could not be saved because the account data is invalid.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up | Create your account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-shell">
        <div class="auth-layout">
            <aside class="meme-panel meme-left" aria-label="Meme">
                <img src="https://media.tenor.com/lFtDRlWN0HEAAAAM/gerry-fakhri-meme.gif" alt="Funny reaction meme">
            </aside>

            <section class="auth-card" aria-labelledby="register-title">
                <a href="Landingpage.php" aria-label="Filipino Meme's home"><span class="brand-mark">RR</span></a>
                <p class="eyebrow">Join us</p>
                <h1 id="register-title">Create your account</h1>
                <p class="subtitle">It only takes a moment to get started.</p>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif ($created): ?>
                    <div class="alert alert-success" role="status">
                        Account created successfully. You can now <a href="login.php">log in</a>.
                    </div>
                <?php endif; ?>

                <form action="register.php" method="post">
                    <div class="row g-3 mb-1">
                        <div class="col-sm-6">
                            <label for="first-name" class="form-label">First name</label>
                            <input type="text" class="form-control" id="first-name" name="firstName" value="<?= htmlspecialchars($firstName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" autocomplete="given-name" maxlength="50" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="last-name" class="form-label">Last name</label>
                            <input type="text" class="form-control" id="last-name" name="lastName" value="<?= htmlspecialchars($lastName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" autocomplete="family-name" maxlength="50" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" autocomplete="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" maxlength="128" pattern="(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,128}" title="Use 6–128 characters, including a letter, number, and symbol." autocomplete="new-password" required>
                        <div class="form-text">Use 6–128 characters, including a letter, a number, and a symbol.</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm-password" class="form-label">Retype password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirmPassword" maxlength="128" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 submit-button">Create Account</button>
                </form>

                <p class="account-prompt">Already have an account? <a href="login.php">Log in</a></p>
            </section>

            <aside class="meme-panel meme-right" aria-label="Meme">
                <img src="https://media.tenor.com/iY6Ci8VbCU0AAAAM/totoy-abnoy-evil-laugh.gif" alt="Funny evil laugh meme">
            </aside>
        </div>
    </main>
</body>
</html>
