<?php
// TEMPORARY DIAGNOSTIC ENDPOINT — removed after deployment debugging.
ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error !== null) {
        echo 'SHUTDOWN type=' . $error['type'] . ' message=' . $error['message']
            . ' file=' . $error['file'] . ':' . $error['line'] . PHP_EOL;
    }
});

echo 'probe ok' . PHP_EOL;
echo 'php=' . PHP_VERSION . PHP_EOL;
echo 'memory_limit=' . ini_get('memory_limit') . ' max_execution=' . ini_get('max_execution_time') . PHP_EOL;
echo 'opcache=' . (function_exists('opcache_get_status') ? 'yes' : 'no') . PHP_EOL;
echo 'app_key=' . (getenv('APP_KEY') ? 'set' : 'missing') . PHP_EOL;
echo 'app_env=' . (getenv('APP_ENV') ?: 'missing') . PHP_EOL;
echo 'db_connection=' . (getenv('DB_CONNECTION') ?: 'missing') . PHP_EOL;
echo 'db_host=' . (getenv('DB_HOST') ?: 'missing') . PHP_EOL;
echo 'ext_pgsql=' . (extension_loaded('pdo_pgsql') ? 'yes' : 'no') . PHP_EOL;
echo 'ext_intl=' . (extension_loaded('intl') ? 'yes' : 'no') . PHP_EOL;
echo 'vendor=' . (is_file(__DIR__ . '/../vendor/autoload.php') ? 'yes' : 'no') . PHP_EOL;
echo 'writable_tmp=' . (is_writable(sys_get_temp_dir()) ? 'yes' : 'no') . PHP_EOL;

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo 'autoload=ok' . PHP_EOL;

    $app = require __DIR__ . '/../bootstrap/app.php';
    echo 'bootstrap=ok class=' . get_class($app) . PHP_EOL;

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo 'kernel=ok class=' . get_class($kernel) . PHP_EOL;

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    echo 'handle=ok status=' . $response->getStatusCode() . PHP_EOL;
    echo '--- BODY START ---' . PHP_EOL;
    echo substr($response->getContent(), 0, 5000) . PHP_EOL;
    echo '--- BODY END ---' . PHP_EOL;
} catch (Throwable $e) {
    echo 'CAUGHT ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
