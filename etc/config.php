<?php

$app = require __DIR__ . '/app.php';
$auth = require __DIR__ . '/auth.php';
$cache = require __DIR__ . '/cache.php';
$cors = require __DIR__ . '/cors.php';
$csrf = require __DIR__ . '/csrf.php';
$database = require __DIR__ . '/database.php';
$events = require __DIR__ . '/events.php';
$filesystems = require __DIR__ . '/filesystems.php';
$headers = require __DIR__ . '/headers.php';
$jwt = require __DIR__ . '/jwt.php';
$mail = require __DIR__ . '/mail.php';
$maintenance = require __DIR__ . '/maintenance.php';
$queue = require __DIR__ . '/queue.php';
$session = require __DIR__ . '/session.php';
$view = require __DIR__ . '/view.php';

// merge into one big array
return array_merge_recursive(
    $app,
    ['auth' => $auth],
    $cache,
    ['session' => $session],
    $cors,
    $csrf,
    $database,
    $events,
    $filesystems,
    $headers,
    $jwt,
    $mail,
    $maintenance,
    $queue,
    $view
);
