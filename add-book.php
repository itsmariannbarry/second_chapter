<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
$pageTitle = 'Sell A Book';
$errors = [];
$universities = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM universities ORDER BY name"), MYSQLI_ASSOC);
$categories = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM categories ORDER BY name"), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $module_code = trim($_POST['module_code'] ?? '');
    $edition = trim($_POST['edition'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $condition = $_POST['condition_grade'] ?? 'Good';
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $university_id = (int)($_POST['university_id'] ?? 0);
    if ($title === '' || $author === '' || $price <= 0 || $province === '' || $city === '' || $category_id <= 0) { $errors[] = 'Please complete title, author, category, price, province and city.'; }
    $imagePath = null;
    if (!empty($_FILES['book_image']['name'])) {
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $mime = mime_content_type($_FILES['book_image']['tmp_name']);
        if (!isset($allowed[$mime])) { $errors[] = 'Book image must be JPG, PNG or WEBP.'; }
        elseif ($_FILES['book_image']['size'] > 3000000) { $errors[] = 'Book image must be smaller than 3 MB.'; }
        else {
            $filename = 'uploads/book_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($_FILES['book_image']['tmp_name'], __DIR__ . '/' . $filename)) { $errors[] = 'Could not upload image.'; }
            else { $imagePath = $filename; }
        }
    }
    if (!$errors) {
        $uni = $university_id > 0 ? $university_id : null;
        $stmt = mysqli_prepare($conn, "INSERT INTO books (seller_id,category_id,university_id,title,author,module_code,edition,isbn,description,price,condition_grade,image_path,province,city,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
        mysqli_stmt_bind_param($stmt, 'iiissssssdssss', $_SESSION['user_id'], $category_id, $uni, $title, $author, $module_code, $edition, $isbn, $description, $price, $condition, $imagePath, $province, $city);
        mysqli_stmt_execute($stmt);
        flash('success', 'Your book slid onto the shelf and is waiting for admin approval.');
        redirect('my-listings.php');
    }
}
include 'includes/header.php';
?>
<section class="form-page">
  <form class="form-card wide" method="post" enctype="multipart/form-data">
    <p class="eyebrow">My Bookshelf</p><h1>Sell A Book</h1><p class="hint">Add your textbook details. New listings are reviewed by The Librarian's Office before appearing publicly.</p>
    <?php if($errors): ?><div class="error-list"><?php foreach($errors as $e): ?><p><?= clean($e) ?></p><?php endforeach; ?></div><?php endif; ?>
    <div class="upload-drop field full"><i class="bi bi-cloud-arrow-up"></i><p><strong>Drag your textbook image here</strong> or choose a file below.</p><input type="file" name="book_image" accept="image/*"></div>
    <div class="form-grid">
      <div class="field"><label>Book Title *</label><input name="title" value="<?= clean($_POST['title'] ?? '') ?>" required></div>
      <div class="field"><label>Author *</label><input name="author" value="<?= clean($_POST['author'] ?? '') ?>" required></div>
      <div class="field"><label>Module Code</label><input name="module_code" value="<?= clean($_POST['module_code'] ?? '') ?>" placeholder="e.g. ITECA3"></div>
      <div class="field"><label>Edition</label><input name="edition" value="<?= clean($_POST['edition'] ?? '') ?>"></div>
      <div class="field"><label>ISBN</label><input name="isbn" value="<?= clean($_POST['isbn'] ?? '') ?>"></div>
      <div class="field"><label>Price *</label><input type="number" step="0.01" min="1" name="price" value="<?= clean($_POST['price'] ?? '') ?>" required></div>
      <div class="field"><label>Category *</label><select name="category_id" required><option value="0">Select category</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['category_id'] ?>" <?= (($_POST['category_id'] ?? '')==$c['category_id'])?'selected':'' ?>><?= clean($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>University</label><select name="university_id"><option value="0">Any university</option><?php foreach($universities as $u): ?><option value="<?= (int)$u['university_id'] ?>" <?= (($_POST['university_id'] ?? '')==$u['university_id'])?'selected':'' ?>><?= clean($u['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Condition</label><select name="condition_grade"><?php foreach(['New','Like New','Good','Fair','Poor'] as $cond): ?><option <?= (($_POST['condition_grade'] ?? 'Good')===$cond)?'selected':'' ?>><?= $cond ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Province *</label><input name="province" value="<?= clean($_POST['province'] ?? '') ?>" required></div>
      <div class="field"><label>City *</label><input name="city" value="<?= clean($_POST['city'] ?? '') ?>" required></div>
      <div class="field full"><label>Description</label><textarea name="description" placeholder="Mention highlights, notes, missing pages, collection preferences..."> <?= clean($_POST['description'] ?? '') ?></textarea></div>
      <div class="field full"><button class="btn-secondary" type="submit">Slide Book Onto Shelf</button></div>
    </div>
  </form>
</section>
<?php include 'includes/footer.php'; ?>
