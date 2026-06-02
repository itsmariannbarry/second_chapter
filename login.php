<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Login';
$errors    = [];

if (is_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (!$errors) {
        $stmt = mysqli_prepare($conn, "
            SELECT users.*, roles.role_name AS role
            FROM   users
            LEFT JOIN roles ON users.role_id = roles.role_id
            WHERE  users.email = ?
            LIMIT  1
        ");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $authenticated = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $authenticated = true;
            } elseif ($password === $user['password']) {
                /* Plain-text seed password — upgrade to bcrypt immediately */
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $upd  = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($upd, 'si', $hash, $user['user_id']);
                mysqli_stmt_execute($upd);
                $authenticated = true;
            }
        }

        if ($authenticated) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['email']      = $user['email'];          /* ← needed for is_admin() check */
            $_SESSION['role']       = strtolower((string)$user['role']);

            flash('success', 'Welcome back, ' . $user['first_name'] . '.');

            if (strtolower((string)$user['role']) === 'admin'
                && strtolower($user['email']) === strtolower(ADMIN_EMAIL)) {
                redirect('admin/dashboard.php');
            } else {
                redirect('dashboard.php');
            }
        } else {
            $errors[] = 'Incorrect email or password. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-page">
    <form class="auth-card" method="post" action="login.php">
        <p class="eyebrow">Welcome Back</p>
        <h1>Open Your Reading Room</h1>
        <p class="hint">Sign in to buy, sell and manage your textbooks.</p>

        <?php if ($errors): ?>
            <div class="error-list">
                <?php foreach ($errors as $e): ?><p><?= clean($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="field">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email"
                   value="<?= clean($_POST['email'] ?? '') ?>"
                   required autocomplete="email" placeholder="you@university.ac.za">
        </div>

        <div class="field" style="margin-top:14px">
            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password" placeholder="Your password">
        </div>

        <button class="btn-primary full-btn" type="submit" style="margin-top:28px">
            <i class="bi bi-box-arrow-in-right"></i> Login
        </button>

        <p class="form-link" style="margin-top:20px">
            New here? <a href="register.php">Create your free account</a>
        </p>
    </form>
</section>

<?php include 'includes/footer.php'; ?>
