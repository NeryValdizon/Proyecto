<?php
// products.php - lista de productos con búsqueda
session_start();
if (!isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }

include "conexion.php";

// Eliminar producto (si se solicita)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conexion->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: products.php");
    exit;
}

// Obtener término de búsqueda
$q = trim($_GET['q'] ?? '');

// Consulta preparada con LIKE para buscar por nombre o id (soporta buscar por número o texto)
if ($q !== '') {
    // Usamos LIKE en dos columnas: name y id (id LIKE permite buscar por número también)
    $search = "%{$q}%";
    $stmt = $conexion->prepare("SELECT * FROM products WHERE name LIKE ? OR id LIKE ? ORDER BY id DESC");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $res = $stmt->get_result();
    $total = $res->num_rows;
} else {
    // Sin búsqueda: mostrar todos
    $res = $conexion->query("SELECT * FROM products ORDER BY id DESC");
    $total = $res->num_rows;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Productos</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <div class="container">
    <a href="dashboard.php">← Volver</a>
    <nav>
      <button href="product_add.php" class="btn">Nuevo producto</button>
    </nav>
  </div>
</header>

<main class="container">
  <h2>Productos</h2>

  <!-- Buscador -->
  <form id="searchForm" method="get" action="products.php" style="margin-bottom:12px; display:flex; gap:8px; align-items:center;">
    <input type="search" name="q" placeholder="Buscar por nombre o id..." value="<?=htmlspecialchars($q)?>" style="flex:1; padding:8px; border-radius:6px; border:1px solid #ddd;">
    <?php if($q !== ''): ?>
      <a class="btn" href="products.php" style="padding:8px 10px; text-decoration:none;">Limpiar</a>
    <?php endif; ?>
    <button type="submit" class="btn">Buscar</button>
  </form>

  <p class="muted"><?= $total ?> resultado(s)<?= $q !== '' ? " para \"".htmlspecialchars($q)."\"" : "" ?></p>

  <table class="table">
    <thead>
      <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
    </thead>
    <tbody>
      <?php if ($res && $res->num_rows > 0): ?>
        <?php while($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>$<?= number_format($row['price'],2) ?></td>
            <td><?= $row['stock'] ?></td>
            <td>
              <a href="product_edit.php?id=<?= $row['id'] ?>">Editar</a> |
              <a href="products.php?delete=<?= $row['id'] ?>" onclick="return confirm('Eliminar producto?')">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5">No se encontraron productos.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</main>

<script src="assets/js/app.js"></script>
</body>
</html>
