<?php
session_start();
require_once '../includes/config.php';

// Verificar si el usuario está logueado y tiene rol de administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$productId = $_GET['id'];

// Obtener información del producto para eliminar las imágenes
$stmt = $pdo->prepare("SELECT image, images FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

 if ($product) {
// Eliminar imagen principal
     if ($product['image'] && file_exists("../{$product['image']}")) {
        unlink("../{$product['image']}");
    }
    
    // Eliminar imágenes adicionales
     $additionalImages = json_decode($product['images'], true) ?: [];
    foreach ($additionalImages as $img) {
        if ($img && file_exists("../$img")) {
           // unlink("../$img");
        }
     }
    
    // Eliminar de la base de datos
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute($productId);
}

header('Location: index.php');
exit;
?>