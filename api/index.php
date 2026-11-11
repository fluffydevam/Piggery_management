<?php

// Forward Vercel requests to Laravel's normal front controller.
// Never echo PHP errors in the response; they break the HTTP status/headers
// and leak credentials. Application errors are logged to stderr instead.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require __DIR__ . '/../public/index.php';
