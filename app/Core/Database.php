<?php
// /app/Core/Database.php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Handles the database connection using PDO.
 * This class uses the Singleton pattern to ensure that there is only
 * one instance of the database connection throughout the application's lifecycle.
 */
class Database
{
    /**
     * @var PDO|null The single instance of the PDO connection.
     */
    private static $instance = null;

    /**
     * The private constructor prevents direct creation of a new instance.
     */
    private function __construct()
    {
    }

    /**
     * The private clone method prevents cloning of the instance.
     */
    private function __clone()
    {
    }

    /**
     * The main connection method.
     *
     * It checks if a connection instance already exists. If not, it creates
     * one using the settings from our config file.
     *
     * @return PDO The PDO database connection instance.
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            $config = require_once __DIR__ . '/../../config/database.php';

            // Data Source Name (DSN) for PostgreSQL
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";

            // Options for PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
                PDO::ATTR_EMULATE_PREPARES => false,                  // Use real prepared statements
            ];

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['password'], $options);
            } catch (PDOException $e) {
                // For a real application, you would log this error, not expose it publicly.
                // We're using die() here for simplicity during development.
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
