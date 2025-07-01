<?php
// /app/Models/UserModel.php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users where ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finds a single user by their email address.
     * @param string $email The user's email.
     * @return mixed The user data as an array or false if not found.
     */
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new user in the database.
     * @param array $data An associative array containing 'name', 'email', and 'password'.
     * @return bool True on success, false on failure.
     */
    public function create(array $data)
    {
        // Ensure all required fields are present
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }

        try {
            if (isset($data['role']) && $data['role'] === 'admin') {
                $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'admin')";
            } else {
                $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            }

            $stmt = $this->db->prepare($sql);

            // Hash the password for security before saving
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);

            return $stmt->execute();
        } catch (\PDOException $e) {
            // In a real app, you would log this error.
            return false;
        }
    }


    /**
     * Retrieves all users with the 'admin' role.
     * @return array List of admin users.
     */
    public function getAdmins()
    {
        $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE role = :role");
        $stmt->execute(['role' => 'admin']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a user by their ID.
     * @param int $id The user's ID.
     * @return bool True on success, false on failure.
     */
    public function deleteById(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
