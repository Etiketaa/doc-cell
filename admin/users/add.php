<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Verificar si el usuario está logueado y tiene rol de administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'user';

    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Formato de email inválido.";
    } else {
        // Verificar si el nombre de usuario o email ya existen
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = "El nombre de usuario o el email ya están registrados.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Error al crear el usuario.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario - Bit-House Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper" style="width: 250px;">
            <div class="sidebar-heading text-white p-4">Bit-House Admin</div>
            <div class="list-group list-group-flush">
                <a href="../index.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="../index.php#products-management" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-box me-2"></i>Gestión de Productos</a>
                <a href="../add_product.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-plus-circle me-2"></i>Agregar Producto</a>
                <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white active"><i class="fas fa-users me-2"></i>Gestión de Usuarios</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-shopping-cart me-2"></i>Gestión de Pedidos</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-cog me-2"></i>Configuración</a>
                <a href="../../logout.php" class="list-group-item list-group-item-action bg-dark text-white mt-auto"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper" class="flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button>
                <h2 class="ms-auto me-3 mb-0">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h2>
            </nav>

            <div class="container-fluid p-4">
                <h1 class="mt-4 mb-4">Agregar Nuevo Usuario</h1>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function () {
            el.classList.toggle("toggled");
        };
    </script>
</body>
</html>