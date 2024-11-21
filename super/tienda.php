<?php
// Incluir archivo de conexión a la base de datos
require_once '../Admin/DB.php';

// Conectar a la base de datos utilizando la función definida en DB.php
try {
    $conn = getDB();

    if (!$conn) {
        throw new Exception("Error al conectar con la base de datos.");
    }

    // Consultar productos disponibles
    $result = $conn->query("SELECT * FROM productos_super");

    if (!$result) {
        throw new Exception("Error al obtener los productos: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tienda Supermercado</title>
    <link rel="stylesheet" href="../css/tienda.css"> <!-- Asegúrate de que este archivo CSS existe -->
</head>
<body>
    <h1>Productos Disponibles</h1>
    <form method="POST" action="api/procesar_compra.php">
        <table border="1">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad Disponible</th>
                    <th>Comprar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($producto = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td>$<?= number_format($producto['precio'], 2) ?></td>
                        <td><?= htmlspecialchars($producto['stock']) ?></td>
                        <td>
                            <input type="number" name="cantidad[<?= $producto['id'] ?>]" min="1" max="<?= $producto['stock'] ?>" value="0">
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit">Procesar Compra</button>
    </form>
</body>
</html>
