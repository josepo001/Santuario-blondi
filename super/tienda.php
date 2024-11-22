<?php
session_start();
require_once '../Admin/DB.php';

$db = new Database();
$productos = $db->query("SELECT * FROM productos_super")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda</title>
    <link rel="stylesheet" href="../css/tienda.css">
</head>
<body>
    <header>
        <h1>Tienda</h1>
        <div id="cart-icon">
            🛒 <span id="cart-count">0</span>
        </div>
    </header>

    <div class="container">
        <!-- Lista de productos -->
        <div class="products">
            <h2>Productos disponibles</h2>
            <ul id="product-list">
                <?php foreach ($productos as $producto): ?>
                    <li>
                        <div>
                            <strong><?php echo $producto['nombre']; ?></strong>
                            <p>Precio: $<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                        </div>
                        <button class="add-to-cart" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo $producto['nombre']; ?>" data-precio="<?php echo $producto['precio']; ?>">Añadir al carrito</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Carrito emergente -->
    <div id="cart-popup" class="hidden">
        <h2>Carrito de compras</h2>
        <ul id="cart-list"></ul>
        <div id="cart-summary">
            <p><strong>Sub-Total:</strong> <span id="subtotal">$0</span></p>
            <p><strong>Total:</strong> <span id="total">$0</span></p>
        </div>
        <a href="pagar.php" id="checkout-button" style="display: none;">Ir a pagar</a>
        <button id="close-cart">Cerrar</button>
    </div>



    <script src="js/super.js"></script>
</body>
</html>
