<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Activer le debug
$app['config']->set('app.debug', true);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/multiservices', 'GET');

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n\n";
    if ($response->getStatusCode() >= 400) {
        echo "Content:\n";
        echo $response->getContent();
    } else {
        echo "SUCCESS!\n";
    }
} catch (Exception $e) {
    echo "ERREUR FATALE:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}
