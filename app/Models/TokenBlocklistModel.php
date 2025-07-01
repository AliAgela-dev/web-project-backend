<?php
// /app/Models/TokenBlocklistModel.php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Manages the blocklist for revoked JWTs.
 */
class TokenBlocklistModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Adds a token's signature to the blocklist.
     *
     * @param string $signature The signature part of the JWT.
     * @param int $expiryTimestamp The 'exp' claim from the JWT payload.
     * @return bool True on success, false on failure.
     */
    public function revoke(string $signature, int $expiryTimestamp)
    {
        try {
            $sql = "INSERT INTO revoked_tokens (signature, expires_at) VALUES (:signature, to_timestamp(:expires_at))";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':signature', $signature);
            $stmt->bindParam(':expires_at', $expiryTimestamp);
            return $stmt->execute();
        } catch (\PDOException $e) {
            // In a real app, log this error.
            return false;
        }
    }

    /**
     * Checks if a token's signature is on the blocklist.
     *
     * @param string $signature The signature part of the JWT.
     * @return bool True if the token is revoked, false otherwise.
     */
    public function isRevoked(string $signature)
    {
        $stmt = $this->db->prepare("SELECT 1 FROM revoked_tokens WHERE signature = :signature");
        $stmt->execute(['signature' => $signature]);
        return $stmt->fetchColumn() !== false;
    }
}
