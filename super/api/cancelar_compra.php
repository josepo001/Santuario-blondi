<?php
session_start();

// Verificar si el carrito existe en la sesión y eliminarlo
if (isset($_SESSION['carrito'])) {
    unset($_SESSION['carrito']); // Vaciar el carrito
}

// Redirigir a tienda.php
header('Location: ../tienda.php');
exit;
?>
