<?php
// /app/Controllers/UserController.php

namespace App\Controllers;

use App\Core\Request;
use App\Models\UserModel;
use App\Core\Database;
use PDO;

class UserController
{
    private $userModel;
    private $request;

    /**
     * The Request object is now "injected" into the controller.
     * @param Request $request The application's request object.
     */
    public function __construct(Request $request)
    {
        $this->userModel = new UserModel();
        $this->request = $request; // Use the injected request object
    }

    /**
     * Handles the request to get all users.
     */
    public function getAll()
    {

        $user = $this->request->user;
        // Search the database for the user ID from $user->sub
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }
        if (!isset($userRecord['role']) || $userRecord['role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        $users = $this->userModel->findAll();
        echo json_encode($users);
    }

    /**
     * Handles the request to create a new user (registration).
     */
    public function create()
    {
        // We no longer create a new Request here. We use the one from the constructor.
        $data = $this->request->getBody();

        // --- Step 1: Input Validation ---
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Name, email, and password are required.']);
            return;
        }

        // --- Step 2: Check for Existing User ---
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser) {
            http_response_code(409); // 409 Conflict
            echo json_encode(['error' => 'A user with this email address already exists.']);
            return;
        }

        // --- Step 3: Attempt to Create the User ---
        if ($this->userModel->create($data)) {
            http_response_code(201); // Created
            echo json_encode(['message' => 'User created successfully.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to create user due to a server error.']);
        }
    }

    public function createAdmin()
    {

        $user = $this->request->user;
        // Search the database for the user ID from $user->sub
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }
        if (!isset($userRecord['role']) || $userRecord['role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        $data = $this->request->getBody();

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name, email, and password are required for the new user.']);
            return;
        }

        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(['error' => 'A user with this email address already exists.']);
            return;
        }

        // An admin can create another admin or a regular user.
        // Default to 'user' if no role is specified in the request body.
        $data['role'] = $data['role'] ?? 'user';
        echo json_encode($data['role']);
        if (!in_array($data['role'], ['user', 'admin'])) {
            http_response_code(400);
            echo json_encode(['error' => "Invalid role specified. Must be 'user' or 'admin'."]);
            return;
        }

        if ($this->userModel->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => "User with role '{$data['role']}' created successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user.']);
        }
    }



    public function getAdmins()
    {
        $user = $this->request->user;
        // Search the database for the user ID from $user->sub
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }
        if (!isset($userRecord['role']) || $userRecord['role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        $admins = $this->userModel->getAdmins();
        echo json_encode($admins);
    }

    public function deleteUser($id)
    {
        $user = $this->request->user;
        // Search the database for the user ID from $user->sub
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }
        if (!isset($userRecord['role']) || $userRecord['role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        // Prevent an admin from deleting themselves
        if ($user->sub == $id) {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot delete your own account.']);
            return;
        }

        if ($this->userModel->deleteById($userId)) {
            echo json_encode(['message' => 'User deleted successfully.']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found or could not be deleted.']);
        }
    }

    public function editAdmin($id)
    {
        $user = $this->request->user;
        $userId = $user->sub;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userRecord || $userRecord['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to perform this action.']);
            return;
        }

        $data = $this->request->getBody();
        if ($this->userModel->updateAdmin($id, $data)) {
            echo json_encode(['message' => 'Admin updated successfully.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to update admin or no fields provided.']);
        }
    }
    /**
     * Allows an admin to edit another admin's data.
     * @param int $id The admin's user ID.
     */

}
