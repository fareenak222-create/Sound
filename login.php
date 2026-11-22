
<?php
$title = 'Log in';

require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('profile.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lock = $_SESSION['lock_until'] ?? 0;

    if (!csrf_ok()) {
        $error = 'Security token invalid. Please try again.';
    } elseif ($lock > time()) {
        $error = 'Too many failed attempts. Try again in '
            . ($lock - time()) . ' seconds.';
    } else {
        $login = trim((string) ($_POST['login'] ?? ''));
        $pw = (string) ($_POST['password'] ?? '');

        $u = one(
            'SELECT * FROM users WHERE user_id = ? OR email = ?',
            [$login, $login]
        );

        $okPw = false;

        if ($u && isset($u['password_hash'])) {
            $storedPassword = (string) $u['password_hash'];

            // Hashed password verify karo; warna legacy plain-text match.
            $info = password_get_info($storedPassword);

            if (($info['algo'] ?? null) !== null) {
                $okPw = password_verify($pw, $storedPassword);
            } else {
                $okPw = hash_equals($storedPassword, $pw);
            }
        }

        if ($u && $okPw && $u['status'] === 'active') {
            unset($_SESSION['fails'], $_SESSION['lock_until']);

            // Plain-text password ko hash mein convert karo.
            $storedPassword = (string) $u['password_hash'];
            $info = password_get_info($storedPassword);

            if (
                ($info['algo'] ?? null) === null ||
                password_needs_rehash($storedPassword, PASSWORD_DEFAULT)
            ) {
                $newHash = password_hash($pw, PASSWORD_DEFAULT);

                q(
                    'UPDATE users SET password_hash = ? WHERE id = ?',
                    [$newHash, $u['id']]
                );

                $u['password_hash'] = $newHash;
            }

            $next = $_SESSION['after_login'] ?? '';
            unset($_SESSION['after_login']);

            login_user($u);

            flash('success', 'Welcome back, ' . $u['full_name'] . '!');

            if (
                $next &&
                str_starts_with($next, BASE_URL . '/') &&
                !str_contains($next, '//')
            ) {
                header('Location: ' . $next);
                exit;
            }

            if ($u['role'] === 'admin') {
                redirect('admin/index.php');
            }

            redirect('index.php');

        } elseif ($u && $okPw) {
            $error = 'This account has been suspended. Please contact support.';

        } else {
            $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;

            if ($_SESSION['fails'] >= 5) {
                $_SESSION['lock_until'] = time() + 60;
                $_SESSION['fails'] = 0;
            }

            $error = 'Invalid User ID/email or password.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="auth-card auth-sm mx-auto">
    <h1 class="h3 mb-1">Welcome back</h1>
    <p class="text-muted">Log in to your account.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="vstack gap-3">
        <?= csrf_field() ?>

        <div>
            <label class="form-label">User ID or email</label>
            <input
                name="login"
                class="form-control"
                required
                autofocus
                autocomplete="username"
                value="<?= e($_POST['login'] ?? '') ?>"
            >
        </div>

        <div>
            <label class="form-label">Password</label>
            <input
                name="password"
                type="password"
                class="form-control"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="submit" class="btn btn-sound w-100">
            Log in
        </button>
    </form>

    <p class="text-center text-muted mt-3 mb-0">
        New here?
        <a href="<?= e(url('register.php')) ?>">Create an account</a>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
