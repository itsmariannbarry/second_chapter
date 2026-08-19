<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
flash('warning', 'Editing can be added later. For this prototype, create a new listing or mark items sold from My Listings.');
redirect('my-listings.php');
?>
