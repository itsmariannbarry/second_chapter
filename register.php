<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Register';
$errors = [];
$universities = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM universities ORDER BY name"), MYSQLI_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $university_id = (int)($_POST['university_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($first === '' || $last === '' || $email === '' || $password === '') { $errors[] = 'Please complete all required fields.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email address.'; }
    if (strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($password !== $confirm) { $errors[] = 'Passwords do not match.'; }
    $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email=?");
    mysqli_stmt_bind_param($check, 's', $email);
    mysqli_stmt_execute($check);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($check))) { $errors[] = 'An account with this email already exists.'; }
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uni = $university_id > 0 ? $university_id : null;
        $stmt = mysqli_prepare($conn, "INSERT INTO users (role_id,first_name,last_name,email,password,phone,university_id) VALUES (2,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'sssssi', $first, $last, $email, $hash, $phone, $uni);
        mysqli_stmt_execute($stmt);
        flash('success', 'Registration successful. Please log in.');
        redirect('login.php');
    }
}
include 'includes/header.php';
?>
<section class="form-page">
  <form class="form-card" method="post">
    <p class="eyebrow">Join the chapter</p><h1>Create Account</h1><p class="hint">Buy and sell second-hand textbooks in a calmer, student-focused space.</p>
    <?php if($errors): ?><div class="error-list"><?php foreach($errors as $e): ?><p><?= clean($e) ?></p><?php endforeach; ?></div><?php endif; ?>
    <div class="form-grid">
      <div class="field"><label>First Name *</label><input name="first_name" value="<?= clean($_POST['first_name'] ?? '') ?>" required></div>
      <div class="field"><label>Last Name *</label><input name="last_name" value="<?= clean($_POST['last_name'] ?? '') ?>" required></div>
      <div class="field full"><label>Email *</label><input type="email" name="email" value="<?= clean($_POST['email'] ?? '') ?>" required></div>
      <div class="field"><label>Phone</label><input name="phone" value="<?= clean($_POST['phone'] ?? '') ?>"></div>
      <div class="field"><label>University</label><select name="university_id"><option value="0">Select university</option><?php foreach($universities as $u): ?><option value="<?= (int)$u['university_id'] ?>" <?= (($_POST['university_id'] ?? '') == $u['university_id']) ? 'selected' : '' ?>><?= clean($u['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Password *</label><input type="password" name="password" required></div>
      <div class="field"><label>Confirm Password *</label><input type="password" name="confirm_password" required></div>
      <div class="field full"><button class="btn-primary" type="submit">Create My Account</button></div>
    </div>
  </form>
</section>
<?php include 'includes/footer.php'; ?>
