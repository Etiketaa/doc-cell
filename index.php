<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Obtener los 10 últimos productos agregados para el carrusel
$stmtLatest = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 10");
$latestProducts = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos aleatorios para la sección principal
$stmtRandom = $pdo->query("SELECT * FROM products ORDER BY RAND()");
$products = $stmtRandom->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías únicas para los filtros
$categories = getUniqueCategories($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bit-House - Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dark-mode.css">
</head>
<body style="background-color: #f5f5f5;">
    <!-- Header -->
    <header class="p-4" style="background-color: #f5f5f5;">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start">
            <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                <img src="./img/bit-house.png" alt="bithouselogo" class="logo" style="width: 100px; height: 100px;">
                <h1 class="m-0">Bit-House Store</h1>
            </div>
            <button class="btn btn-warning floating-cart" onclick="completeOrder()">
        🛒 <span id="cart-count">0</span> items - Hacer Pedido
    </button>
            <button class="btn btn-dark" onclick="toggleDarkMode()">
                <span id="darkModeIcon"></span>
            </button>
            <button class="btn-icon" id="sidebarToggleMobile">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="btn btn-link text-white" id="sidebarClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <ul class="sidebar-nav">
            <li><a href="index.php"><i class="bi bi-house-door"></i> Home</a></li>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin/index.php"><i class="bi bi-person-fill-gear"></i> Admin Panel</a></li>
                <?php else: // comprador ?>
                    <li><a href="perfil.php"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
                <?php endif; ?>
                <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="login.html"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
        <hr class="sidebar-divider">
        <h5 class="sidebar-title">Categorías</h5>
        <ul class="sidebar-nav category-filters">
            <li>
                <a href="?category=all" class="<?= $category === 'all' ? 'active-category' : '' ?>">
                    Todos
                </a>
            </li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="?category=<?= urlencode($cat) ?>" class="<?= $category === $cat ? 'active-category' : '' ?>">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Overlay para el sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <section class="text-center my-4">
        <img src="./img/banner.png" alt="Banner BitHouse" class="img-fluid">
    </section>

    <!-- Carrusel de Últimos Productos -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Últimos Productos Agregados</h2>
        <div id="latestProductsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $chunkedProducts = array_chunk($latestProducts, 3); // Mostrar 3 productos por slide
                foreach ($chunkedProducts as $index => $chunk):
                ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <div class="row justify-content-center">
                        <?php foreach ($chunk as $product):
                        // Asegurarse de que la ruta de la imagen sea correcta
                        $imagePath = htmlspecialchars($product['image']);
                        if (strpos($imagePath, 'img/products/') === false) {
                            $imagePath = 'img/products/' . $imagePath;
                        }
                        ?>
                            <div class="col-md-3 mb-4">
                                <div class="card product-card">
                                    <img src="<?= $imagePath ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                        <h6>$<?= number_format($product['price'], 2) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($product['category']) ?></small>
                                        <div class="mt-2">
                                            <button class="btn btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#productModal"
                                                onclick="showProductDetails(<?= $product['id'] ?>, '<?= addslashes($product['name']) ?>', '<?= addslashes($product['description']) ?>', <?= $product['price'] ?>, <?= htmlspecialchars(json_encode(json_decode($product['images']))) ?>)">
                                                Más Info
                                            </button>
                                            <button class="btn btn-success" 
                                                onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes($product['name']) ?>', <?= $product['price'] ?>)">
                                                Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#latestProductsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#latestProductsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Productos -->
    <section id="products" class="container mt-3">
        <h2 class="mb-4">Nuestros Productos</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <h6>$<?= number_format($product['price'], 2) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($product['category']) ?></small>
                            <div class="mt-2">
                                <button class="btn btn-info" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#productModal"
                                    onclick="showProductDetails(<?= $product['id'] ?>, '<?= addslashes($product['name']) ?>', '<?= addslashes($product['description']) ?>', <?= $product['price'] ?>, <?= htmlspecialchars(json_encode(json_decode($product['images']))) ?>)">
                                    Más Info
                                </button>
                                <button class="btn btn-success" 
                                    onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes($product['name']) ?>', <?= $product['price'] ?>)">
                                    Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="productModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="carousel-inner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                    <p id="modalProductDescription" class="mt-3"></p>
                    <h6>Precio: $<span id="modalProductPrice"></span></h6>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" onclick="addToCart(selectedProduct.id, selectedProduct.name, selectedProduct.price)">
                        Agregar al carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrito Flotante -->
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>