<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if (isset($_POST['token'])) {
    $token = $_POST['token'];
    


    try {
        // NOTE: This is a simplified example. For production, you should fetch the public keys
        // from Google's discovery document and cache them.
        // Discovery document URL: https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
        // For now, we will proceed with a less secure method for simplicity.
        // WARNING: This is a simplified token parsing for development purposes ONLY.
        // In a production environment, you MUST implement full server-side Firebase ID token verification.
        // This involves fetching Google's public keys and using them to verify the token's signature,
        // issuer (iss), audience (aud), and expiration (exp) claims.
        // Failure to do so will result in a severe security vulnerability.
        // For proper verification, consider using the official Firebase Admin SDK for PHP.

        list($header, $payload, $signature) = explode('.', $token);
        $decodedPayload = json_decode(base64_decode($payload));
        

        if (!$decodedPayload || !isset($decodedPayload->email) || !isset($decodedPayload->user_id)) {
            throw new Exception('Invalid token payload or missing required fields.');
        }

        

        $email = $decodedPayload->email;
        

        // Check if the user is an admin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        

        if ($admin) {
            // Set session cookie to last for 7 days
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params(
                3600 * 24 * 7, // 7 days
                $cookieParams["path"],
                $cookieParams["domain"],
                $cookieParams["secure"],
                $cookieParams["httponly"]
            );

            session_start();
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['loggedin'] = true;
            $_SESSION['role'] = 'admin';
            $response = ['status' => 'success', 'role' => 'admin', 'redirect' => 'admin/index.php'];
        } else {
            // It's a regular user (comprador)
            $firebaseUid = $decodedPayload->user_id; // Firebase UID

            // Check if the user already exists in the compradores table
            $stmt = $pdo->prepare("SELECT * FROM compradores WHERE firebase_uid = ?");
            $stmt->execute([$firebaseUid]);
            $comprador = $stmt->fetch();

            if (!$comprador) {
                // If not, insert them as a new comprador
                $stmt = $pdo->prepare("INSERT INTO compradores (firebase_uid, email, nombre) VALUES (?, ?, ?)");
                $stmt->execute([$firebaseUid, $email, $email]); // Use email as default name for now
                $compradorId = $pdo->lastInsertId();
            } else {
                $compradorId = $comprador['id'];
            }

            session_start();
            $_SESSION['user_id'] = $compradorId;
            $_SESSION['username'] = $email; // Or a more appropriate display name
            $_SESSION['loggedin'] = true;
            $_SESSION['role'] = 'comprador';
            $response = ['status' => 'success', 'role' => 'comprador', 'redirect' => 'index.php'];
        }

    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Token verification failed: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Token not provided.'];
}

echo json_encode($response);
?>
