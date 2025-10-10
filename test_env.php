<?php
// Load Composer autoloader
require_once __DIR__.'/vendor/autoload.php';

// Load environment variables using vlucas/phpdotenv (Laravel's underlying env loader)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "DB_DATABASE from \$_ENV: " . ($_ENV['DB_DATABASE'] ?? 'NOT_FOUND') . "\n";
echo "DB_DATABASE from \$_SERVER: " . ($_SERVER['DB_DATABASE'] ?? 'NOT_FOUND') . "\n";
echo "getenv('DB_DATABASE'): " . getenv('DB_DATABASE') . "\n";

// Also check if there's some cached value
echo "Environment loaded successfully\n";