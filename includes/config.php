<?php
$host = 'localhost';
$dbname = 'bit_house';
$username = 'root'; // Cambia según tu configuración
$password = '';     // Cambia según tu configuración

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>