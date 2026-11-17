<?php
// Forward Vercel requests to Laravel's normal front controller

// Never let PHP notices or deprecations leak into the response body. Once
// output has started, Laravel can no longer send headers or its error page.
ini_set('display_errors', '0');

require __DIR__ . '/../public/index.php';
