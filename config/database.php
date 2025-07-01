<?php
// /config/database.php

/**
 * Database Configuration
 *
 * This file returns an array of database connection settings.
 * Keeping this separate from the main application logic is a good practice.
 */

return [
    'host' => 'postgresql://ali:P42L1AGw5lAPIsJI3ryuk7r2uMSChrST@dpg-d1hv2ejuibrs7380k6gg-a.oregon-postgres.render.com', 
    'port' => 5432,
    'dbname' => 'web_project_ll18', 
    'user' => 'ali',      
    'password' => getenv('DB_PASSWORD'), 
    'charset' => 'utf8mb4'
];

