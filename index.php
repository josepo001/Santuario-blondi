<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css">
    <title>Login</title>
</head>
<body>
    <div class="background"></div> <!-- Fondo con imagen -->
    <div class="container">
        <form action="validar.php" method="post" class="login">
            <div class="header">
                <div>Santuario Blondi</div>
            </div>
            <input type="text" placeholder="Ingrese RUT" name="rut" required>
            <input type="password" placeholder="Ingrese su contraseña" name="contrasena" required>
            <input type="submit" value="Ingresar">
        </form>
    </div>
</body>
</html>
