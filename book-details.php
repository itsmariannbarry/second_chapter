<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT b.*, u.name AS university_name, u.short_name, c.name AS category_name, CONCAT(s.first_name,' ',s.last_name) AS seller_name, s.email AS seller_email, s.phone AS seller_phone FROM books b LEFT JOIN universities u ON b.university_id=u.university_id LEFT JOIN categories c ON b.category_id=c.category_id JOIN users s ON b.seller_id=s.user_id WHERE b.book_id=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$book) { flash('warning', 'Book not found.'); redirect('books.php'); }
$pageTitle = $book['title'];
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    $message = trim($_POST['message'] ?? '');
    if ($message !== '' && (int)$_SESSION['user_id'] !== (int)$book['seller_id']) {
        $stmt = mysqli_prepare($conn, "INSERT INTO enquiries (book_id,buyer_id,seller_id,message) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'iiis', $id, $_SESSION['user_id'], $book['seller_id'], $message);
        mysqli_stmt_execute($stmt);
        $sent = true;
        flash('success', 'Your enquiry was sent. The seller can now contact you.');
    }
}
include 'includes/header.php';
?>
<section class="section">
  <div class="container book-detail">
    <div class="detail-cover">
      <?php if (!empty($book['image_path']) && file_exists(__DIR__ . '/' . $book['image_path'])): ?>
        <img src="<?= BASE_URL . clean($book['image_path']) ?>" alt="<?= clean($book['title']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:26px">
      <?php else: ?>
        <div class="book-cover"><span><?= clean($book['title']) ?><br><small><?= clean($book['short_name']) ?></small></span></div>
      <?php endif; ?>
    </div>
    <article class="detail-panel">
      <p class="eyebrow">Textbook Details</p>
      <h1><?= clean($book['title']) ?></h1>
      <p class="subtitle" style="font-size:28px;color:var(--sage)"><?= clean($book['author']) ?></p>
      <p><span class="pill"><?= clean($book['condition_grade']) ?></span> <span class="pill"><?= clean($book['category_name']) ?></span> <span class="pill"><?= clean($book['university_name'] ?? 'Any University') ?></span></p>
      <p class="price" style="font-size:42px"><?= money($book['price']) ?></p>
      <p><strong>Module:</strong> <?= clean($book['module_code'] ?: 'Not specified') ?> &nbsp; <strong>Edition:</strong> <?= clean($book['edition'] ?: 'Not specified') ?> &nbsp; <strong>ISBN:</strong> <?= clean($book['isbn'] ?: 'Not specified') ?></p>
      <p><strong>Collection area:</strong> <?= clean($book['city']) ?>, <?= clean($book['province']) ?></p>
      <hr style="border:0;border-top:1px solid var(--line);margin:24px 0">
      <h3>Description</h3><p><?= nl2br(clean($book['description'] ?: 'No description was added.')) ?></p>
      <h3>Seller</h3><p><?= clean($book['seller_name']) ?> <?php if(is_admin()): ?> · <?= clean($book['seller_email']) ?> · <?= clean($book['seller_phone']) ?><?php endif; ?></p>
      <?php if (is_logged_in() && (int)$_SESSION['user_id'] !== (int)$book['seller_id']): ?>
        <form method="post" class="paper-card" style="margin-top:20px">
          <label for="message"><strong>Send an enquiry</strong></label>
          <textarea id="message" name="message" required placeholder="Hi, is this book still available?" style="width:100%;margin:10px 0"></textarea>
          <button class="btn-primary" type="submit">Send Message</button>
        </form>
      <?php elseif (!is_logged_in()): ?>
        <a class="btn-primary" href="login.php">Log in to Contact Seller</a>
      <?php endif; ?>
    </article>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
