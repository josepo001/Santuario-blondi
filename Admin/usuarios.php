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
    // Obtener conexión a la base de datos
    $db = getDB();
    if (!$db) {
        die("Error de conexión a la base de datos.");
    }

    // Obtener información del usuario logueado
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    die("Error al obtener información del usuario: " . $e->getMessage());
}

// Para la lista de usuarios
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Modifica la consulta SQL para incluir la búsqueda y filtrar por 'admin' y 'empleado'
    $sql = "SELECT * FROM usuarios WHERE 
            (nombre LIKE ? OR 
            apellido LIKE ? OR 
            email LIKE ? OR 
            id LIKE ?) AND 
            (tipo_usuario = 'admin' OR tipo_usuario = 'empleado')";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $db->error);
    }

    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $noResultMessage = "No se encontraron usuarios.";
    }
} catch (Exception $e) {
    die("Error al obtener la lista de usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor Usuarios</title>
    <link rel="stylesheet" href="../css/usuarios.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function confirmarEliminar(id) {
            if (confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
                document.getElementById('eliminarForm' + id).submit();
            }
        }
    </script>
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

    <main>
        <h1 class="page-title">Gestión de Usuarios</h1>

        <!-- Botón de Agregar Usuario -->
        <div class="button-container">
            <a class="btn_agregar" href="Registrar.php">AGREGAR USUARIO</a>
        </div>


        <!-- Formulario de Búsqueda -->
        <div class="search-container">
            <form method="GET" action="" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Buscar" value="<?= htmlspecialchars($search) ?>" required>
                <button id="searchButton">Buscar</button>
                <button type="button" id="clearButton">Restablecer Búsqueda</button>
            </form>
        </div>

        <script>
            document.getElementById('clearButton').addEventListener('click', function(event) {
                event.preventDefault();
                const searchForm = document.getElementById('searchForm');
                const inputField = document.getElementById('searchInput');
                inputField.value = '';
                searchForm.submit();
            });
        </script>

        <!-- Tabla de Usuarios -->
        <div class="home_content">
            <div class="container table-responsive">
                <table class="table table-light table-bordered border-secondary table-rounded">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>RUT</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Fecha Registro</th>            
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($mostrar = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($mostrar['id']) ?></td> 
                                    <td><?= htmlspecialchars($mostrar['rut']) ?></td>
                                    <td><?= htmlspecialchars($mostrar['nombre']) ?></td>
                                    <td><?= htmlspecialchars($mostrar['apellido']) ?></td>
                                    <td><?= htmlspecialchars($mostrar['email']) ?></td>
                                    <td><?= htmlspecialchars($mostrar['tipo_usuario']) ?></td>
                                    <td><?= htmlspecialchars($mostrar['creado_en']) ?></td>
                                    <td>
                                        <a class="btn btn-success btn-sm" href="editar.php?id=<?= htmlspecialchars($mostrar['id']) ?>">Editar</a>
                                    </td>
                                    <td>
                                        <form id="eliminarForm<?= $mostrar['id'] ?>" action="eliminar.php" method="post">
                                            <input type="hidden" value="<?= htmlspecialchars($mostrar['id']) ?>" name="txtID">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar(<?= htmlspecialchars($mostrar['id']) ?>)">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">No se encontraron usuarios.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
