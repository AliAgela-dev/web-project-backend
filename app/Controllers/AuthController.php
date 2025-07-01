<?php
// /app/Controllers/AuthController.php

namespace App\Controllers;

use App\Core\Request;
use App\Models\UserModel;
use App\Models\TokenBlocklistModel;

class AuthController
{
    private $userModel;
    private $blocklistModel;
    private $request;

    /**
     * The Request object is now "injected" into the controller.
     * @param Request $request The application's request object.
     */
    public function __construct(Request $request)
    {
        $this->userModel = new UserModel();
        $this->blocklistModel = new TokenBlocklistModel();
        $this->request = $request; // Use the injected request object
    }

    /**
     * Handles user login, verifies credentials, and returns a JWT.
     */
    public function login()
    {
        // We no longer create a new Request here.
        $data = $this->request->getBody();

        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required.']);
            return;
        }

        $user = $this->userModel->findByEmail($data['email']);

        if ($user && password_verify($data['password'], $user['password'])) {
            $secret = 'a-very-long-and-secure-secret-key-that-is-not-aa12aa12';
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            $payload = json_encode([
                'sub' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'], // Add the user's role to the token payload
                'iat' => time(),
                'exp' => time() + (60 * 60)
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode($payload);
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
            $base64UrlSignature = $this->base64UrlEncode($signature);
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            echo json_encode(['message' => 'Login successful', 'token' => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials.']);
        }
    }

    /**
     * Logs a user out by adding their token's signature to a blocklist.
     */
    public function logout()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided.']);
            return;
        }

        $token = $matches[1];
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid token format.']);
            return;
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        $signature = $parts[2];
        $expiry = $payload['exp'] ?? 0;

        if ($this->blocklistModel->revoke($signature, $expiry)) {
            echo json_encode(['message' => 'Logout successful. Token has been revoked.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Could not process logout.']);
        }
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
