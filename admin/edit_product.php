<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

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
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    $imagePath = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Eliminar imagen anterior si existe
        if ($imagePath && file_exists("../$imagePath")) {
            unlink("../$imagePath");
        }
        $imagePath = uploadImage($_FILES['image']);
    }
    
    // Procesar imágenes adicionales
    $additionalImages = json_decode($product['images'], true) ?: [];
    if (isset($_FILES['additional_images'])) {
        foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['additional_images']['name'][$key],
                    'type' => $_FILES['additional_images']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['additional_images']['error'][$key],
                    'size' => $_FILES['additional_images']['size'][$key]
                ];
                $uploadedPath = uploadImage($file);
                if ($uploadedPath) {
                    $additionalImages[] = $uploadedPath;
                }
            }
        }
    }
    
    // Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, images = ?, category = ? WHERE id = ?");
    $stmt->execute([
        $name,
        $price,
        $description,
        $imagePath,
        json_encode($additionalImages),
        $category,
        $productId
    ]);
    
    header('Location: index.php');
    exit;
}

$categories = getUniqueCategories($pdo);
$additionalImages = json_decode($product['images'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Bit-House</title>
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
                <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="index.php#products-management" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-box me-2"></i>Gestión de Productos</a>
                <a href="add_product.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-plus-circle me-2"></i>Agregar Producto</a>
                <a href="edit_product.php?id=<?= $productId ?>" class="list-group-item list-group-item-action bg-dark text-white active"><i class="fas fa-edit me-2"></i>Editar Producto</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-users me-2"></i>Gestión de Usuarios</a>
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
                <h1 class="mt-4 mb-4">Editar Producto</h1>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($product['category']) ?>" list="categories" required>
                        <datalist id="categories">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Imagen Principal</label>
                        <?php if ($product['image']): ?>
                            <div class="mb-2">
                                <img src="../<?= htmlspecialchars($product['image']) ?>" alt="Imagen actual" style="max-width: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image">
                    </div>
                    
                    <div class="mb-3">
                        <label for="additional_images" class="form-label">Imágenes Adicionales</label>
                        <?php if (!empty($additionalImages)): ?>
                            <div class="mb-2">
                                <?php foreach ($additionalImages as $img): ?>
                                    <img src="../<?= htmlspecialchars($img) ?>" alt="Imagen adicional" style="max-width: 100px;" class="me-2">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="additional_images" name="additional_images[]" multiple>
                        <small class="text-muted">Selecciona nuevas imágenes para agregar a las existentes</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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