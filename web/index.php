<?php

declare(strict_types=1);

/**
 * My Custom PHP Framework
 *
 * This file is the single point of entry for all HTTP requests.
 */

use Strux\Foundation\Bootstrap;

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

try {
    $app = Bootstrap::create(ROOT_PATH);
    $app->run();
} catch (\Throwable $e) {
    http_response_code(500);
    error_log("FATAL: Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo "An error occurred: " . htmlspecialchars($e->getMessage());
}