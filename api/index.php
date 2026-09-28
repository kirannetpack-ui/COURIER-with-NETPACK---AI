<?php

/**
 * Vercel Serverless Entry Point for Laravel
 */

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

// Ensure database file exists in /tmp when using sqlite
if (getenv('DB_CONNECTION') === 'sqlite' || !getenv('DB_CONNECTION')) {
    $dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
    if (!file_exists($dbPath)) {
        @touch($dbPath);
    }
}

// Normalize script name for Laravel routing on Vercel
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// Forward to normal Laravel public/index.php
require __DIR__ . '/../public/index.php';
