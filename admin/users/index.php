<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Verificar si el usuario está logueado y tiene rol de administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Bit-House Admin</title>
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
                <h1 class="mt-4 mb-4">Gestión de Usuarios</h1>
                <a href="add.php" class="btn btn-primary mb-4">Agregar Nuevo Usuario</a>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= $user['created_at'] ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="delete.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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