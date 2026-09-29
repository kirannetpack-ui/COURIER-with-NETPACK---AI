<?php

/**
 * Laravel Serverless Entry Point & Router for Vercel
 */

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Log loaded extensions on initial worker boot
static $loggedBoot = false;
if (!$loggedBoot) {
    error_log('[PHP] Booting PHP ' . PHP_VERSION . ' on Vercel');
    error_log('[PHP] Loaded extensions (' . count(get_loaded_extensions()) . '): ' . implode(', ', get_loaded_extensions()));
    $loggedBoot = true;
}

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);

// Built-in web server static asset pass-through
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Initialize required storage directories in writable /tmp
$dirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/testing',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Ensure SQLite database exists with pre-seeded data if available
$dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
$sourceDb = __DIR__ . '/database/database.sqlite';
if (!file_exists($dbPath) || filesize($dbPath) === 0) {
    if (file_exists($sourceDb) && filesize($sourceDb) > 0) {
        @copy($sourceDb, $dbPath);
    } else {
        @touch($dbPath);
    }
}

// Enforce critical environment variables for Vercel execution
$envOverrides = [
    'APP_STORAGE' => '/tmp/storage',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_DRIVER' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $dbPath,
    'APP_KEY' => getenv('APP_KEY') ?: 'base64:7genAr6vdKRc37ptrhKOuTO5+aDkJIL96kUUGqV11F8=',
    'APP_TIMEZONE' => 'Asia/Kathmandu',
    'SESSION_LIFETIME' => 120,
];

foreach ($envOverrides as $key => $val) {
    putenv("$key=$val");
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
}

// Normalize script name for Laravel routing
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';

// Forward to Laravel public/index.php
require __DIR__ . '/public/index.php';
