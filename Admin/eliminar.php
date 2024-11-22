<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../Admin/DB.php'; // Asegúrate de que la ruta sea correcta

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    if (isset($_POST['txtID']) && is_numeric($_POST['txtID'])) {
        $id_usuario = intval($_POST['txtID']); // Sanitizar el ID del usuario

        $db = getDB();

        // Iniciar una transacción
        $db->begin_transaction();

        // 1. Eliminar transacciones relacionadas con las tarjetas del usuario
        $stmt = $db->prepare("DELETE t FROM transacciones_diarias t 
                              INNER JOIN tarjetas ta ON t.tarjeta_id = ta.id 
                              WHERE ta.usuario_id = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        // 2. Eliminar las compras realizadas por el usuario
        $stmt = $db->prepare("DELETE FROM compras WHERE usuario_id = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        // 3. Eliminar las tarjetas asociadas al usuario
        $stmt = $db->prepare("DELETE FROM tarjetas WHERE usuario_id = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        // 4. Finalmente, eliminar el usuario
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Confirmar la transacción
            $db->commit();
            $stmt->close();
            header('Location: usuarios.php?success=Usuario eliminado correctamente.');
            exit;
        } else {
            // Si no se eliminó el usuario, hacer rollback
            $db->rollback();
            $stmt->close();
            header('Location: usuarios.php?error=No se encontró el usuario o no se pudo eliminar.');
            exit;
        }
    } else {
        header('Location: usuarios.php?error=ID de usuario no especificado o inválido.');
        exit;
    }
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback(); // Hacer rollback si ocurre algún error
    }
    die("Error al eliminar usuario: " . htmlspecialchars($e->getMessage()));
}
?>
