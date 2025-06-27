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
    'port' => 3306,
    'dbname' => 'my_api_db', // The name of your database
    'user' => 'root',      // Your database username
    'password' => '',          // Your database password
    'charset' => 'utf8mb4'
];

