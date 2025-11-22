<?php
session_start();
if (!isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }
include "conexion.php";
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '0');
    if ($name === '') $errors[] = 'Nombre obligatorio.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Precio numérico obligatorio.';
    if (!is_numeric($stock) || (int)$stock < 0) $errors[] = 'Stock debe ser >= 0.';
    if (empty($errors)) {
        $stmt = $conexion->prepare("INSERT INTO products (name, price, stock, created_at) VALUES (?, ?, ?, NOW())");
        $p = number_format((float)$price,2,'.','');
        $s = (int)$stock;
        $stmt->bind_param('sdi', $name, $p, $s);
        if ($stmt->execute()) {
            header('Location: products.php');
            exit;
        } else {
            $errors[] = 'Error al guardar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Añadir producto</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<header class="topbar"><div class="container"><a href="products.php">← Volver</a></div></header>
<main class="container">
  <h2>Añadir producto</h2>
  <?php if($errors): ?><div class="error"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" id="formProduct">
    <label>Nombre</label>
    <input name="name" required>
    <label>Precio</label>
    <input name="price" required>
    <label>Stock</label>
    <input name="stock" type="number" min="0" value="0" required>
    <button class="btn" type="submit">Guardar</button>
  </form>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>
