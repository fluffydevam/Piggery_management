<?php
// TEMPORARY DIAGNOSTIC ENDPOINT — removed after deployment debugging.
ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo 'probe ok' . PHP_EOL;
echo 'php=' . PHP_VERSION . PHP_EOL;
echo 'app_key=' . (getenv('APP_KEY') ? 'set' : 'missing') . PHP_EOL;
echo 'app_env=' . (getenv('APP_ENV') ?: 'missing') . PHP_EOL;
echo 'db_connection=' . (getenv('DB_CONNECTION') ?: 'missing') . PHP_EOL;
echo 'db_host=' . (getenv('DB_HOST') ?: 'missing') . PHP_EOL;
echo 'ext_pgsql=' . (extension_loaded('pdo_pgsql') ? 'yes' : 'no') . PHP_EOL;
echo 'ext_intl=' . (extension_loaded('intl') ? 'yes' : 'no') . PHP_EOL;
echo 'vendor=' . (is_file(__DIR__ . '/../vendor/autoload.php') ? 'yes' : 'no') . PHP_EOL;
echo 'writable_tmp=' . (is_writable(sys_get_temp_dir()) ? 'yes' : 'no') . PHP_EOL;

try {
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    echo 'kernel_status=' . $response->getStatusCode() . PHP_EOL;
    echo 'kernel_body_start' . PHP_EOL;
    echo substr($response->getContent(), 0, 3000) . PHP_EOL;
} catch (Throwable $e) {
    echo 'CAUGHT ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
