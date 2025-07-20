<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y tiene rol de administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.html');
    exit;
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener conteos para el dashboard
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStockThreshold = 10; // Define tu umbral de stock bajo
$lowStockProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE stock < ?");
$lowStockProducts->execute([$lowStockThreshold]);
$countLowStock = $lowStockProducts->fetchColumn();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bit-House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper" style="width: 250px;">
            <div class="sidebar-heading text-white p-4">Bit-House Admin</div>
            <div class="list-group list-group-flush">
                <a href="../index.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-home me-2"></i>Home</a>
                <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="#products-management" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-box me-2"></i>Gestión de Productos</a>
                <a href="add_product.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-plus-circle me-2"></i>Agregar Producto</a>
                <a href="./users/index.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-users me-2"></i>Gestión de Usuarios</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-shopping-cart me-2"></i>Gestión de Pedidos</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-cog me-2"></i>Configuración</a>
                <a href="../logout.php" class="list-group-item list-group-item-action bg-dark text-white mt-auto"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
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
                <h1 class="mt-4 mb-4">Dashboard de Administración</h1>

                <!-- Resumen General -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">Total Productos</div>
                            <div class="card-body">
                                <h5 class="card-title"><?= $totalProducts ?></h5>
                                <p class="card-text">Productos registrados en el sistema.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">Productos con Stock Bajo</div>
                            <div class="card-body">
                                <h5 class="card-title"><?= $countLowStock ?></h5>
                                <p class="card-text">Productos que necesitan ser reabastecidos.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Ventas del Día (Placeholder)</div>
                            <div class="card-body">
                                <h5 class="card-title">$0.00</h5>
                                <p class="card-text">Total de ventas realizadas hoy.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos (Placeholder) -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">Resumen de Ventas (Gráfico Placeholder)</div>
                            <div class="card-body">
                                <p>Aquí iría un gráfico de ventas. Considera usar librerías como Chart.js para visualizaciones modernas.</p>
                                <canvas id="myChart" width="400" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gestión de Productos (Tabla Existente) -->
                <h2 class="mb-4" id="products-management">Gestión de Productos</h2>
                <a href="add_product.php" class="btn btn-primary mb-4">Agregar Nuevo Producto</a>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><img src="../<?= htmlspecialchars($product['image']) ?>" alt="Imagen" style="max-width: 50px;"></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td>
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="delete_producto.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
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