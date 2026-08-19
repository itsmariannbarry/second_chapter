<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
$pageTitle = 'My Listings';
if (isset($_GET['mark_sold'])) {
    $book_id = (int)$_GET['mark_sold'];
    $stmt = mysqli_prepare($conn, "UPDATE books SET status='sold' WHERE book_id=? AND seller_id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $book_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    flash('success', 'Listing stamped as sold.');
    redirect('my-listings.php');
}
$stmt = mysqli_prepare($conn, "SELECT b.*, u.short_name, c.name AS category_name FROM books b LEFT JOIN universities u ON b.university_id=u.university_id LEFT JOIN categories c ON b.category_id=c.category_id WHERE b.seller_id=? ORDER BY b.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$books = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
include 'includes/header.php';
?>
<section class="dashboard-shell"><div class="container dashboard-grid">
  <aside class="sidebar-card"><a href="dashboard.php"><i class="bi bi-house-heart"></i> My Reading Room</a><a class="active" href="my-listings.php"><i class="bi bi-bookshelf"></i> My Listings</a><a href="add-book.php"><i class="bi bi-plus-circle"></i> Add Book</a><a href="profile.php"><i class="bi bi-person"></i> Profile</a></aside>
  <div>
    <div class="chapter" style="text-align:left;margin:0 0 30px"><span>My Bookshelf</span><h2>My Listings</h2></div>
    <?php if(!$books): ?><div class="paper-card"><h3>Your shelf is empty</h3><p>Add your first textbook listing.</p><a class="btn-secondary" href="add-book.php">Sell A Book</a></div><?php else: ?>
      <div class="grid book-grid"><?php foreach($books as $book): include 'includes/book-card.php'; endforeach; ?></div>
    <?php endif; ?>
  </div>
</div></section>
<?php include 'includes/footer.php'; ?>
