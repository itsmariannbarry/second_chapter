<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Login';
$errors = [];

if (is_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (!$errors) {
      $stmt = mysqli_prepare($conn, "
    SELECT users.*, roles.role_name AS role
    FROM users
    LEFT JOIN roles ON users.role_id = roles.role_id
    WHERE users.email = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role'] = strtolower($user['role']);

    flash('success', 'Welcome back to your reading room.');

    if (strtolower($user['role']) === 'admin') {
        redirect('admin/index.php');
    } else {
        redirect('dashboard.php');
    }
} else {
    $errors[] = 'Incorrect email or password.';
}
    }
}

include 'includes/header.php';
?>

<section class="auth-page">
    <form class="auth-card" method="post">
        <p class="eyebrow">Welcome Back</p>
        <h1>Open Your Reading Room</h1>
        <p class="hint">Please sign in to your account</p>

        <?php if ($errors): ?>
            <div class="error-list">
                <?php foreach ($errors as $e): ?>
                    <p><?= clean($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn-primary full-btn" type="submit">Login</button>

        <p class="form-link">New here? <a href="register.php">Create your account</a></p>
    </form>
</section>

<?php include 'includes/footer.php'; ?>