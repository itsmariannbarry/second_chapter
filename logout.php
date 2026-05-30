<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
session_unset();
session_destroy();
session_start();
flash('success', 'You have logged out.');
redirect('index.php');
?>
