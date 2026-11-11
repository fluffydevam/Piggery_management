<?php
// Forward Vercel requests to Laravel's normal front controller

// TEMPORARY DIAGNOSTIC MODE — reverted immediately after capturing the error.
ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../public/index.php';
