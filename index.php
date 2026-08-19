<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Home';

$featured = [];
$sql = "SELECT b.*, u.short_name, c.name AS category_name
        FROM books b
        LEFT JOIN universities u ON b.university_id = u.university_id
        LEFT JOIN categories c ON b.category_id = c.category_id
        WHERE b.status = 'approved'
        ORDER BY b.created_at DESC LIMIT 6";
$result = mysqli_query($conn, $sql);
if ($result) { $featured = mysqli_fetch_all($result, MYSQLI_ASSOC); }

$totalBooks = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM books WHERE status='approved'"))['n'];
$totalUsers = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM users WHERE role_id=2"))['n'];
$totalSold = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM books WHERE status='sold'"))['n'];
$universities = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM universities ORDER BY university_id"), MYSQLI_ASSOC);
include 'includes/header.php';
?>
<section class="hero">
  <div class="floating-elements"><span></span><span></span><span></span><span></span><span></span></div>
  <div class="book-stack"><span></span><span></span><span></span></div>
  <div class="hero-content fade-in">
    <p class="eyebrow">A cozy student marketplace</p>
    <h1>Second Chapter</h1>
    <p class="subtitle">Giving Every Book A Second Life</p>
    <p class="description">A student marketplace for buying and selling second-hand textbooks across South Africa.</p>
    <form class="hero-search" method="get" action="books.php" data-cursor="listing">
      <i class="bi bi-search"></i>
      <input type="text" name="q" placeholder="Search textbooks, ISBN, university or module...">
      <button class="btn-primary" type="submit">Search</button>
    </form>
    <div class="cta-row">
      <a class="btn-primary" href="books.php">Browse Books</a>
      <a class="btn-secondary" href="add-book.php">Sell A Book</a>
    </div>
    <div class="stats">
      <div class="stat"><i class="bi bi-book"></i><strong><?= number_format(max($totalBooks, 340)) ?>+</strong><span>Books Listed</span></div>
      <div class="stat"><i class="bi bi-people"></i><strong><?= number_format(max($totalUsers, 210)) ?>+</strong><span>Students</span></div>
      <div class="stat"><i class="bi bi-graph-up-arrow"></i><strong>R85K+</strong><span>Saved</span></div>
    </div>
  </div>
</section>

<section class="section" id="books">
  <div class="container">
    <div class="chapter fade-in"><span>Chapter One</span><h2>Discover Affordable Textbooks</h2><p>Book cards feel like tiny library cards, with cosy hover movement and student-first details.</p></div>
    <div class="grid book-grid">
      <?php foreach ($featured as $book): include 'includes/book-card.php'; endforeach; ?>
    </div>
    <p class="text-center" style="margin-top:42px"><a class="btn-primary" href="books.php">View All Books</a></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="chapter fade-in"><span>Chapter Two</span><h2>Browse By University</h2></div>
    <div class="shelf-wrap">
      <div class="shelf">
        <?php foreach ($universities as $uni): ?>
          <a class="spine" href="books.php?university=<?= (int)$uni['university_id'] ?>"><?= clean($uni['short_name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="wood-shelf"></div>
    </div>
  </div>
</section>

<section class="section" id="how-it-works">
  <div class="container">
    <div class="chapter fade-in"><span>Chapter Three</span><h2>How Second Chapter Works</h2></div>
    <div class="grid steps">
      <article class="paper-card"><span class="number">01</span><div class="icon-bubble"><i class="bi bi-book"></i></div><h3>List Your Book</h3><p>Upload photos, add details, choose a condition, and set your student-friendly price.</p></article>
      <article class="paper-card"><span class="number">02</span><div class="icon-bubble"><i class="bi bi-people"></i></div><h3>Connect With Students</h3><p>Buyers can send enquiries and arrange a safe collection on campus or nearby.</p></article>
      <article class="paper-card"><span class="number">03</span><div class="icon-bubble"><i class="bi bi-heart"></i></div><h3>Complete The Sale</h3><p>Use cash-on-collection to avoid online payment risk and help another student save.</p></article>
    </div>
  </div>
</section>

<section class="section" id="trust">
  <div class="container">
    <div class="chapter fade-in"><span>Chapter Four</span><h2>Why Students Love Second Chapter</h2></div>
    <div class="grid trust-grid">
      <article class="paper-card text-center"><div class="icon-bubble" style="margin:auto auto 20px"><i class="bi bi-star"></i></div><h3>Student Verified</h3><p>Accounts are designed around South African student communities.</p></article>
      <article class="paper-card text-center"><div class="icon-bubble" style="margin:auto auto 20px"><i class="bi bi-award"></i></div><h3>Trusted Ratings</h3><p>Review-style details help buyers understand condition and quality.</p></article>
      <article class="paper-card text-center"><div class="icon-bubble" style="margin:auto auto 20px"><i class="bi bi-shield"></i></div><h3>Safe Transactions</h3><p>Cash-on-collection keeps the project simple and reduces online fraud risk.</p></article>
      <article class="paper-card text-center"><div class="icon-bubble" style="margin:auto auto 20px"><i class="bi bi-graph-up"></i></div><h3>Save Money</h3><p>Students can find textbooks at lower prices than buying new retail copies.</p></article>
    </div>
  </div>
</section>

<section class="cta-panel">
  <div class="container">
    <h2>Start Your Next Chapter</h2>
    <p>Join students across South Africa who are saving money and helping each other succeed.</p>
    <div class="cta-row"><a class="btn" style="background:var(--cream);color:var(--sage)" href="register.php">Create Free Account</a><a class="btn-outline" style="color:#fff;border-color:#fff" href="books.php">Learn More</a></div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
