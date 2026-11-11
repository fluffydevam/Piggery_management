<?php
// TEMPORARY DIAGNOSTIC ENDPOINT — removed after deployment debugging.
ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo 'php=' . PHP_VERSION . PHP_EOL;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

echo 'cache_dir_writable=' . (is_writable(__DIR__ . '/../bootstrap/cache') ? 'yes' : 'no') . PHP_EOL;
echo 'cache_files=' . implode(',', array_map('basename', glob(__DIR__ . '/../bootstrap/cache/*.php') ?: [])) . PHP_EOL;
echo 'config_cached=' . (is_file(__DIR__ . '/../bootstrap/cache/config.php') ? 'yes' : 'no') . PHP_EOL;
echo 'services_cached=' . (is_file(__DIR__ . '/../bootstrap/cache/services.php') ? 'yes' : 'no') . PHP_EOL;
echo 'packages_cached=' . (is_file(__DIR__ . '/../bootstrap/cache/packages.php') ? 'yes' : 'no') . PHP_EOL;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $kernel->bootstrap();
    echo 'kernel_bootstrap=ok' . PHP_EOL;
} catch (Throwable $e) {
    echo 'BOOTSTRAP_ERROR ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo 'view_bound=' . ($app->bound('view') ? 'yes' : 'no') . PHP_EOL;
$providers = array_keys($app->getLoadedProviders());
echo 'loaded_providers=' . count($providers) . PHP_EOL;
echo implode(PHP_EOL, array_slice($providers, 0, 60)) . PHP_EOL;

try {
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    echo 'handle_status=' . $response->getStatusCode() . PHP_EOL;
    echo '--- BODY START ---' . PHP_EOL;
    echo substr($response->getContent(), 0, 2000) . PHP_EOL;
} catch (Throwable $e) {
    echo 'HANDLE_ERROR ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}
