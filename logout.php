<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$auth->logout();

// Redirect to homepage
header('Location: index.php');
exit();
?> 