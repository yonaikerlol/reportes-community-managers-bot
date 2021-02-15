<?php declare(strict_types=1);

// Load composer autoload
require __DIR__ . "/../vendor/autoload.php";

// Use classes
use App\Bot;

// Initialize dotenv
if (file_exists(__DIR__ . "/../.env")) {
    // Check if a .env file is in the root directory
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
} elseif (file_exists(__DIR__ . "/.env")) {
    // Otherwise, in this directory
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
} else {
    die("Envioronment variables is not found. Verify your .env file\n");
}

// Load dotenv
$dotenv->load();

// Initialize the bot
$bot = new Bot();

// Set output path
$bot->setOutputPath(__DIR__ . "/../storage/data/");

// Run the bot
$bot->run();
