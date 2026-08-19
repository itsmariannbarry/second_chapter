<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
$pageTitle = 'My Reading Room';
$userId = (int)$_SESSION['user_id'];
$myBooks = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM books WHERE seller_id=$userId"))['n'];
$pending = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM books WHERE seller_id=$userId AND status='pending'"))['n'];
$sold = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM books WHERE seller_id=$userId AND status='sold'"))['n'];
$enquiries = mysqli_fetch_all(mysqli_query($conn, "SELECT e.*, b.title, CONCAT(u.first_name,' ',u.last_name) AS buyer_name FROM enquiries e JOIN books b ON e.book_id=b.book_id JOIN users u ON e.buyer_id=u.user_id WHERE e.seller_id=$userId ORDER BY e.created_at DESC LIMIT 10"), MYSQLI_ASSOC);
include 'includes/header.php';
?>
<section class="dashboard-shell"><div class="container dashboard-grid">
  <aside class="sidebar-card"><a class="active" href="dashboard.php"><i class="bi bi-house-heart"></i> My Reading Room</a><a href="my-listings.php"><i class="bi bi-bookshelf"></i> My Listings</a><a href="add-book.php"><i class="bi bi-plus-circle"></i> Add Book</a><a href="profile.php"><i class="bi bi-person"></i> Profile</a><?php if(is_admin()): ?><a href="admin/dashboard.php"><i class="bi bi-shield-check"></i> Librarian Office</a><?php endif; ?></aside>
  <div>
    <div class="chapter" style="text-align:left;margin:0 0 30px"><span>My Reading Room</span><h2>Hello, <?= clean($_SESSION['first_name']) ?></h2><p>Notebook-style cards for your listings, messages and profile.</p></div>
    <div class="mini-stats"><div class="mini-stat"><strong><?= $myBooks ?></strong><span>My Listings</span></div><div class="mini-stat"><strong><?= $pending ?></strong><span>Pending Approval</span></div><div class="mini-stat"><strong><?= $sold ?></strong><span>Sold Books</span></div><div class="mini-stat"><strong><?= count($enquiries) ?></strong><span>Recent Enquiries</span></div></div>
    <div class="table-card"><table><thead><tr><th>Book</th><th>Buyer</th><th>Message</th><th>Date</th></tr></thead><tbody><?php if(!$enquiries): ?><tr><td colspan="4">No messages yet.</td></tr><?php endif; ?><?php foreach($enquiries as $e): ?><tr><td><?= clean($e['title']) ?></td><td><?= clean($e['buyer_name']) ?></td><td><?= clean($e['message']) ?></td><td><?= clean($e['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div>
  </div>
</div></section>
<?php include 'includes/footer.php'; ?>
