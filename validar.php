<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'Admin/DB.php'; // Asegúrate de que esta función conecte correctamente a la base de datos

// Si la solicitud es POST, intentamos iniciar sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rut = trim($_POST['rut'] ?? ''); // Capturamos el RUT y eliminamos espacios
    $contrasena = $_POST['contrasena'] ?? ''; // Capturamos la contraseña (ajustado a "contrasena")

    try {
        // Obtener la conexión a la base de datos
        $db = getDB();
        
        // Preparamos una consulta para encontrar al usuario según el RUT
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE rut = ?");
        $stmt->bind_param("s", $rut); // Vinculamos el parámetro
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Obtenemos el resultado como un array asociativo

        // Si el usuario existe y la contraseña es correcta (comparación sin encriptación)
        if ($user && $contrasena === $user['contrasena']) {
            // Guardar la información relevante en la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['tipo_usuario'];
            $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];

            // Redirigir al usuario según su tipo
            switch ($user['tipo_usuario']) {
                case 'admin':
                    header('Location: Admin/usuarios.php'); // Redirige al área de administración
                    break;
                case 'empleado':
                    header('Location: Trabajador/homeEm.php'); // Redirige al área de empleados
                    break;
                default:
                    $_SESSION['error'] = "Tipo de usuario no reconocido.";
                    header('Location: index.php');
            }
            exit;
        } else {
            // Si las credenciales son inválidas
            $_SESSION['error'] = "RUT o contraseña inválidos";
            header('Location: login.php');
            exit;
        }
    } catch (Exception $e) {
        // Manejo de errores de la base de datos
        $_SESSION['error'] = "Error en el sistema: " . $e->getMessage();
        header('Location: adios.php');
        exit;
    }
} else {
    // Si la solicitud no es POST, redirigimos al formulario de inicio de sesión
    header('Location: index.php');
    exit;
}
?>
