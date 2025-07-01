<?php
// /app/Middleware/AuthMiddleware.php

namespace App\Middleware;

use App\Models\TokenBlocklistModel;

class AuthMiddleware
{
    private $blocklistModel;

    public function __construct()
    {
        $this->blocklistModel = new TokenBlocklistModel();
    }

    /**
     * Handles the authentication check and returns the user payload on success.
     *
     * @return object The decoded user payload from the token.
     */
    public function handle()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->unauthorized('A valid token is required.');
        }

        $token = $matches[1];
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            $this->unauthorized('Invalid token format.');
        }

        list($header, $payload, $signature) = $parts;

        $secret = 'a-very-long-and-secure-secret-key-that-is-not-aa12aa12';
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));

        if (!hash_equals($expectedSignature, $signature)) {
            $this->unauthorized('Invalid token signature.');
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload));

        if (isset($decodedPayload->exp) && $decodedPayload->exp < time()) {
            $this->unauthorized('Token has expired.');
        }

        if ($this->blocklistModel->isRevoked($signature)) {
            $this->unauthorized('Token has been revoked.');
        }

        // --- SUCCESS ---
        // Instead of returning true, we return the user's data.
        return $decodedPayload;
    }

    /**
     * Sends a 401 Unauthorized response and terminates the script.
     * @param string $message The error message to send.
     */
    private function unauthorized($message)
    {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => $message]);
        exit(); // Stop script execution
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
