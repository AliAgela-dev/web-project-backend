<?php
// /config/database.php

/**
 * Database Configuration
 *
 * This file returns an array of database connection settings.
 * Keeping this separate from the main application logic is a good practice.
 */

return [
    'host' => '127.0.0.1', // or 'localhost'
    'port' => 5432,
    'dbname' => 'web_project', // The name of your database
    'user' => 'ali',      // Your database username
    'password' => getenv('DB_PASSWORD'), // Get database password from environment variable
    'charset' => 'utf8mb4'
];

