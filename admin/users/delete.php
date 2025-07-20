<?php
session_start();
require_once '../../includes/config.php';

// Verificar si el usuario está logueado y tiene rol de administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_GET['id'];

// No permitir que un administrador se elimine a sí mismo (opcional pero recomendado)
if ($userId == $_SESSION['user_id']) { // Asumiendo que guardas el ID del usuario en la sesión
    // Puedes redirigir con un mensaje de error
    header('Location: index.php?error=cannot_delete_self');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);

header('Location: index.php');
exit;
?>