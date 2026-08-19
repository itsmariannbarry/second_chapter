<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$pageTitle = 'Profile';
$userId = (int)$_SESSION['user_id'];
$errors = [];

/* Get logged-in user safely */
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

/* If session points to an old/deleted user, log out cleanly */
if (!$user) {
    session_destroy();
    session_start();
    flash('warning', 'Your session expired after the database reset. Please log in again.');
    redirect('login.php');
}

/* Get universities safely */
$universities = [];
$uniResult = mysqli_query($conn, "SELECT * FROM universities ORDER BY name");
if ($uniResult) {
    $universities = mysqli_fetch_all($uniResult, MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $university_id = (int)($_POST['university_id'] ?? 0);

    if ($first === '' || $last === '') {
        $errors[] = 'First and last name are required.';
    }

    if (!$errors) {
        $uni = $university_id > 0 ? $university_id : null;

        $stmt = mysqli_prepare($conn, "UPDATE users SET first_name = ?, last_name = ?, phone = ?, university_id = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, 'sssii', $first, $last, $phone, $uni, $userId);
        mysqli_stmt_execute($stmt);

        $_SESSION['first_name'] = $first;
        $_SESSION['last_name'] = $last;

        flash('success', 'Profile updated.');
        redirect('profile.php');
    }
}

include 'includes/header.php';
?>

<section class="form-page">
    <form class="form-card" method="post">
        <p class="eyebrow">Account Settings</p>
        <h1>Profile</h1>
        <p class="hint">Keep your student contact details up to date.</p>

        <?php if ($errors): ?>
            <div class="error-list">
                <?php foreach ($errors as $e): ?>
                    <p><?= clean($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-grid">
            <div class="field">
                <label>First Name</label>
                <input name="first_name" value="<?= clean($user['first_name'] ?? '') ?>">
            </div>

            <div class="field">
                <label>Last Name</label>
                <input name="last_name" value="<?= clean($user['last_name'] ?? '') ?>">
            </div>

            <div class="field full">
                <label>Email</label>
                <input value="<?= clean($user['email'] ?? '') ?>" disabled>
            </div>

            <div class="field">
                <label>Phone</label>
                <input name="phone" value="<?= clean($user['phone'] ?? '') ?>">
            </div>

            <div class="field">
                <label>University</label>
                <select name="university_id">
                    <option value="0">Select university</option>
                    <?php foreach ($universities as $u): ?>
                        <option value="<?= (int)$u['university_id'] ?>" <?= ((int)($user['university_id'] ?? 0) === (int)$u['university_id']) ? 'selected' : '' ?>>
                            <?= clean($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field full">
                <button class="btn-primary" type="submit">Save Profile</button>
            </div>
        </div>
    </form>
</section>

<?php include 'includes/footer.php'; ?>
