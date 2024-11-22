<?php
require_once '../Admin/DB.php'; // Asegúrate de que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['txtID']); // Asegurar que el ID sea un entero
    $nombre = htmlspecialchars($_POST['txtNombre']);
    $apellido = htmlspecialchars($_POST['txtApellido']);
    $email = htmlspecialchars($_POST['txtEmail']);
    $password = $_POST['txtPassword'];
    $tipo_usuario = htmlspecialchars($_POST['txtTipoUsuario']);

    $db = getDB(); // Obtener conexión a la base de datos

    try {
        // Actualizar solo si la contraseña no está vacía
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, contrasena = ?, tipo_usuario = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $nombre, $apellido, $email, $hashedPassword, $tipo_usuario, $id);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, tipo_usuario = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $apellido, $email, $tipo_usuario, $id);
        }

        if ($stmt->execute()) {
            // Redirigir con mensaje de éxito
            header('Location: usuarios.php?success=Usuario actualizado correctamente');
            exit;
        } else {
            throw new Exception("Error al actualizar el usuario: " . $stmt->error);
        }
    } catch (Exception $e) {
        die("Error al procesar la actualización: " . $e->getMessage());
    }
} else {
    header('Location: usuarios.php?error=Acceso no autorizado');
    exit;
}
?>
