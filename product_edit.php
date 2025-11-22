<?php
session_start();
if (!isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }
include "conexion.php";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: products.php'); exit; }
$stmt = $conexion->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
if (!$product) { header('Location: products.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '0');
    if ($name === '') $errors[] = 'Nombre obligatorio.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Precio numérico obligatorio.';
    if (!is_numeric($stock) || (int)$stock < 0) $errors[] = 'Stock debe ser >= 0.';
    if (empty($errors)) {
        $stmt = $conexion->prepare('UPDATE products SET name=?, price=?, stock=? WHERE id=?');
        $p = number_format((float)$price,2,'.','');
        $s = (int)$stock;
        $stmt->bind_param('sdii', $name, $p, $s, $id);
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
<head><meta charset="utf-8"><title>Editar producto</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<header class="topbar"><div class="container"><a href="products.php">← Volver</a></div></header>
<main class="container">
  <h2>Editar producto #<?= $product['id'] ?></h2>
  <?php if($errors): ?><div class="error"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" id="formProduct">
    <label>Nombre</label>
    <input name="name" value="<?=htmlspecialchars($product['name'])?>" required>
    <label>Precio</label>
    <input name="price" value="<?=htmlspecialchars($product['price'])?>" required>
    <label>Stock</label>
    <input name="stock" type="number" min="0" value="<?=htmlspecialchars($product['stock'])?>" required>
    <button class="btn" type="submit">Actualizar</button>
  </form>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>
