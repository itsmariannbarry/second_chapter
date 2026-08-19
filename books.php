<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Browse Books';

$q = trim($_GET['q'] ?? '');
$university = (int)($_GET['university'] ?? 0);
$category = (int)($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';

$where = ["b.status = 'approved'"];
$params = [];
$types = '';
if ($q !== '') {
    $where[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.module_code LIKE ? OR u.name LIKE ? OR u.short_name LIKE ?)";
    $like = '%' . $q . '%';
    for ($i=0; $i<6; $i++) { $params[] = $like; $types .= 's'; }
}
if ($university > 0) { $where[] = 'b.university_id = ?'; $params[] = $university; $types .= 'i'; }
if ($category > 0) { $where[] = 'b.category_id = ?'; $params[] = $category; $types .= 'i'; }
$order = $sort === 'price_low' ? 'b.price ASC' : ($sort === 'price_high' ? 'b.price DESC' : 'b.created_at DESC');

$sql = "SELECT b.*, u.short_name, c.name AS category_name FROM books b LEFT JOIN universities u ON b.university_id=u.university_id LEFT JOIN categories c ON b.category_id=c.category_id WHERE " . implode(' AND ', $where) . " ORDER BY $order";
$stmt = mysqli_prepare($conn, $sql);
if ($params) { mysqli_stmt_bind_param($stmt, $types, ...$params); }
mysqli_stmt_execute($stmt);
$books = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$universities = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM universities ORDER BY name"), MYSQLI_ASSOC);
$categories = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM categories ORDER BY name"), MYSQLI_ASSOC);
include 'includes/header.php';
?>
<section class="search-header">
  <div class="container">
    <p class="eyebrow">Chapter One</p>
    <h1>Browse the Library</h1>
    <p style="color:var(--muted)">Search by textbook title, ISBN, university, module code, category, or location.</p>
    <form class="search-filter" method="get">
      <input type="text" name="q" value="<?= clean($q) ?>" placeholder="Search textbooks, ISBN, university or module...">
      <select name="university"><option value="0">All Universities</option><?php foreach($universities as $u): ?><option value="<?= (int)$u['university_id'] ?>" <?= $university===(int)$u['university_id']?'selected':'' ?>><?= clean($u['short_name']) ?></option><?php endforeach; ?></select>
      <select name="category"><option value="0">All Categories</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['category_id'] ?>" <?= $category===(int)$c['category_id']?'selected':'' ?>><?= clean($c['name']) ?></option><?php endforeach; ?></select>
      <select name="sort"><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option><option value="price_low" <?= $sort==='price_low'?'selected':'' ?>>Lowest Price</option><option value="price_high" <?= $sort==='price_high'?'selected':'' ?>>Highest Price</option></select>
      <button class="btn-primary" type="submit">Filter</button>
    </form>
  </div>
</section>

<section class="section" style="padding-top:30px">
  <div class="container">
    <?php if (!$books): ?>
      <div class="paper-card text-center"><h3>No books found yet</h3><p>Try a different filter, or list the first book for this chapter.</p><a class="btn-secondary" href="add-book.php">Sell A Book</a></div>
    <?php else: ?>
      <div class="grid book-grid"><?php foreach($books as $book): include 'includes/book-card.php'; endforeach; ?></div>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
