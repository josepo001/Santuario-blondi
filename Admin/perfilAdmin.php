<?php
session_start();
require_once '../Admin/DB.php'; // Asegúrate de que esta función conecte correctamente a la base de datos

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB(); // Obtener conexión a la base de datos

    // Verificar que el usuario esté en sesión
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']); // Usando bind_param para mayor seguridad
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc(); // Obtener los datos del usuario

    if ($user === null) {
        // Manejo de error: usuario no encontrado
        $_SESSION['mensaje'] = "Usuario no encontrado.";
        header('Location: index.php'); // Redirige a la página de inicio
        exit;
    }

    // Si el formulario se envió con método POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validación básica
        if (empty($nombre) || empty($apellido) || empty($email)) {
            $_SESSION['mensaje'] = "Todos los campos son obligatorios, excepto la contraseña.";
            header('Location: perfilAdmin.php');
            exit;
        }

        // Actualizar usuario
        if (!empty($password)) {
            // Si el usuario quiere actualizar la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, contrasena = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $apellido, $email, $password_hash, $_SESSION['user_id']);
        } else {
            // Si no se actualiza la contraseña
            $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $apellido, $email, $_SESSION['user_id']);
        }

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Perfil actualizado correctamente.";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar el perfil.";
        }

        header('Location: perfilAdmin.php');
        exit;
    }
} catch (Exception $e) {
    // Manejo de errores
    $_SESSION['mensaje'] = "Error en el sistema: " . htmlspecialchars($e->getMessage());
    header('Location: index.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="../css/perfil.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
     <!-- Header -->
     <header class="header">
        <div class="header-content">
            <div class="logo">
                <h2>Santuario Blondi</h2>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="usuarios.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas</a></li>
                    <li><a href="historial.php"><i class="fas fa-history"></i> Historial</a></li>
                    <li><a href="perfilAdmin.php"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                    <li><a href="reporte.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                    <li><a href="../cerrar-sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    
                </ul>
            </nav>            
        </div>
    </header>
    
    <div >
        <h1>Mi Perfil</h1>
    </div>
    
    <div class="main-content">
        
        
        <div class="profile-container">
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="mensaje">
                    <?php 
                    echo $_SESSION['mensaje'];
                    unset($_SESSION['mensaje']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form class="profile-form" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" 
                           value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                    <input type="password" id="password" name="password">
                </div>
                
                <button type="submit" class="btn-actualizar">Actualizar Perfil</button>
            </form>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        }
    </script>
</body>
</html>
