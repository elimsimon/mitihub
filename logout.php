<?php
// Centralized logout for the application — used by admin, school, sponsor and public pages
require_once __DIR__ . '/app/auth.php';

// Set a short-lived flash cookie so the login page can display a message after session is destroyed.
// Use urlencode to be safe; cookie path set to project root so login page can read it.
setcookie('mitihub_flash', urlencode('You have been logged out'), 0, '/mitihub/');

// End session and redirect to public login page (index.php)
logout();
redirect(base_url('index.php'));
