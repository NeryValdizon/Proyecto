<?php
session_start();
include "conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $stmt = $conexion->prepare("SELECT id, username, password, fullname FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Comparación SIN hash
        if ($pass === $row["password"]) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["user_name"] = $row["fullname"];
            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "Usuario o contraseña incorrecta.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg">

<div class="center-card">
  <h2>Iniciar sesión</h2>

  <?php if ($error): ?>
      <p class="error"><?= $error ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>Usuario</label>
    <input type="text" name="username" required>

    <label>Contraseña</label>
    <input type="password" name="password" required>

    <button class="btn">Entrar</button>
  </form>

  <p class="muted">Usuario demo: <b>admin</b> / Contraseña: <b>admin123</b></p>
</div>

</body>
</html>
