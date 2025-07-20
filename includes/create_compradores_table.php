<?php
require_once 'config.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS compradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        firebase_uid VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        nombre VARCHAR(255) DEFAULT NULL,
        apellido VARCHAR(255) DEFAULT NULL,
        direccion TEXT DEFAULT NULL,
        telefono VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );
    ";
    $pdo->exec($sql);
    echo "Tabla 'compradores' creada o ya existe.\n";
} catch (PDOException $e) {
    die("Error al crear la tabla 'compradores': " . $e->getMessage());
}
?>
