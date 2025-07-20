<?php
function getUniqueCategories($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function uploadImage($file, $uploadDir = '../img/products/') {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Genera un nombre único para la imagen
    $fileName = uniqid() . '-' . str_replace(' ', '-', strtolower($file['name']));
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Devuelve la ruta relativa desde la raíz del sitio
        return 'img/products/' . $fileName;
    }
    return null;
}
?>