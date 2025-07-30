<?php
require_once 'admin_auth.php';

$auth = new AdminAuth();

// Logout the user
$result = $auth->logout();

// Set flash message for next page
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to login page
header('Location: login.php');
exit();
?> 