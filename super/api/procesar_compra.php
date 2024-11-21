<?php
$conn = new mysqli('localhost', 'root', '', 'supermercado');

// Recorrer los productos seleccionados
foreach ($_POST['cantidad'] as $producto_id => $cantidad) {
    if ($cantidad > 0) {
        // Obtener producto
        $result = $conn->query("SELECT * FROM productos_super WHERE id = $producto_id");
        $producto = $result->fetch_assoc();

        // Calcular precio total
        $precio_total = $producto['precio'] * $cantidad;

        // Insertar en el historial de compras
        $conn->query("INSERT INTO historial_compras_super (producto_id, cantidad, precio_total) VALUES ($producto_id, $cantidad, $precio_total)");

        // Actualizar el stock
        $nuevo_stock = $producto['stock'] - $cantidad;
        $conn->query("UPDATE productos_super SET stock = $nuevo_stock WHERE id = $producto_id");
    }
}

// Redirigir a la tienda
header('Location: tienda.php');
