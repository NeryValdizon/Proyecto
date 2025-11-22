<?php
// dashboard.php - Mejorado
session_start();
if (!isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }
include "conexion.php";

// Manejo de Quick Add (form desde el dashboard)
$quick_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add'])) {
    $name  = trim($_POST['q_name'] ?? '');
    $price = trim($_POST['q_price'] ?? '');
    $stock = trim($_POST['q_stock'] ?? '0');

    $errors = [];
    if ($name === '') $errors[] = "Nombre requerido.";
    if ($price === '' || !is_numeric($price)) $errors[] = "Precio numérico requerido.";
    if (!is_numeric($stock) || (int)$stock < 0) $errors[] = "Stock >= 0 requerido.";

    if (empty($errors)) {
        $stmt = $conexion->prepare("INSERT INTO products (name, price, stock, created_at) VALUES (?, ?, ?, NOW())");
        $p = number_format((float)$price, 2, '.', '');
        $s = (int)$stock;
        $stmt->bind_param('sdi', $name, $p, $s);
        if ($stmt->execute()) {
            $quick_msg = "Producto '{$name}' añadido correctamente.";
            // limpiar POST para que el form quede vacío
            header("Location: dashboard.php?quick_ok=1");
            exit;
        } else {
            $errors[] = "Error al guardar el producto.";
        }
    }
}

// mensajes posteriores al redirect
if (isset($_GET['quick_ok'])) {
    $quick_msg = "Producto añadido correctamente.";
}

// Estadísticas
$row = $conexion->query("SELECT COUNT(*) as total_products FROM products")->fetch_assoc();
$total_products = $row['total_products'] ?? 0;

$row = $conexion->query("SELECT SUM(stock) as total_stock FROM products")->fetch_assoc();
$total_stock = $row['total_stock'] ?? 0;

$row = $conexion->query("SELECT COUNT(*) as low_stock FROM products WHERE stock <= 5")->fetch_assoc();
$low_stock = $row['low_stock'] ?? 0;

// Productos recientes (últimos 6)
$res_recent = $conexion->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");

// Path local del PDF subido por el usuario (ruta en tu entorno)
$pdf_path = '/mnt/data/Proyecto Final 2025 .pdf';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Tienda</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Micro-ajustes específicos del dashboard */
    .stats { display:flex; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
    .stat-card { flex:1; min-width:160px; padding:14px; border-radius:10px; background:#fff; box-shadow:0 6px 18px rgba(10,20,50,0.04); }
    .stat-card h3 { margin:0 0 6px; font-size:14px; color:#6b7280; }
    .stat-card p { margin:0; font-size:20px; font-weight:700; color:#111827; }
    .quick-add { margin-top:10px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
    .recent-list { margin-top:12px; }
    .shortcut { display:inline-block; margin-right:8px; padding:8px 10px; border-radius:8px; background:#f3f6ff; color:var(--primary); text-decoration:none; }
    .pdf-link { display:inline-block; margin-top:10px; font-size:14px; color:#374151; text-decoration:underline; }
  </style>
</head>
<body>
<header class="topbar">
  <div class="container">
    <h1>Tienda - Panel</h1>
    <nav>
      <a href="products.php">Productos</a>
      <a href="product_add.php">Añadir</a>
      <a href="products.php" class="shortcut">Ver lista</a>
      <a href="logout.php" style="color:#ef4444">Cerrar sesión</a>
    </nav>
  </div>
</header>

<main class="container">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
    <div>
      <h2>Bienvenido, <?=htmlspecialchars($_SESSION['user_name'])?></h2>
      <p class="muted">Panel de control rápido — administra tu tienda.</p>
    </div>

    <div style="text-align:right;">
      <a class="btn" href="products.php">Ir a Productos</a>
    </div>
  </div>

  <!-- Estadísticas -->
  <div class="stats" role="region" aria-label="Estadísticas rápidas">
    <div class="stat-card">
      <h3>Total de productos</h3>
      <p><?=number_format($total_products)?></p>
    </div>
    <div class="stat-card">
      <h3>Stock total</h3>
      <p><?=number_format($total_stock)?></p>
    </div>
    <div class="stat-card">
      <h3>Productos con stock bajo (≤5)</h3>
      <p style="color:<?= $low_stock>0 ? '#b91c1c' : '#059669' ?>"><?=number_format($low_stock)?></p>
    </div>
  </div>

  <!-- Quick Add -->
  <section aria-labelledby="quickAddTitle">
    <h3 id="quickAddTitle">Añadir producto rápido</h3>

    <?php if(!empty($quick_msg)): ?>
      <div class="error" style="background:#ecfdf5;color:#064e3b;padding:8px;border-radius:6px;margin-bottom:10px;"><?=htmlspecialchars($quick_msg)?></div>
    <?php endif; ?>

    <form method="post" class="quick-add" id="quickAddForm" style="align-items:center;">
      <input name="q_name" placeholder="Nombre del producto" style="padding:8px;border-radius:6px;border:1px solid #e6e9ef;min-width:220px" required>
      <input name="q_price" placeholder="Precio" style="padding:8px;border-radius:6px;border:1px solid #e6e9ef;width:120px" required>
      <input name="q_stock" type="number" min="0" placeholder="Stock" style="padding:8px;border-radius:6px;border:1px solid #e6e9ef;width:100px" value="0" required>
      <button type="submit" name="quick_add" class="btn">Añadir</button>
      <a class="btn" href="product_add.php" style="background:#10b981">Form. completo</a>
    </form>
  </section>

  <!-- Productos recientes -->
  <section class="recent-list" aria-labelledby="recentTitle">
    <h3 id="recentTitle">Productos recientes</h3>
    <table class="table" style="margin-top:8px">
      <thead><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Creado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php if($res_recent && $res_recent->num_rows > 0): ?>
          <?php while($r = $res_recent->fetch_assoc()): ?>
            <tr>
              <td><?=$r['id']?></td>
              <td><?=htmlspecialchars($r['name'])?></td>
              <td>$<?=number_format($r['price'],2)?></td>
              <td><?=intval($r['stock'])?></td>
              <td><?=htmlspecialchars($r['created_at'])?></td>
              <td><a href="product_edit.php?id=<?=$r['id']?>">Editar</a> | <a href="products.php?delete=<?=$r['id']?>" onclick="return confirm('Eliminar producto?')">Eliminar</a></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6">No hay productos recientes.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

  <!-- Atajos y recursos -->
  <section style="margin-top:18px">
    <h3>Atajos y recursos</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
      <a class="shortcut" href="products.php">Ver todos los productos</a>
      <a class="shortcut" href="product_add.php">Añadir producto (form)</a>
      <a class="shortcut" href="logout.php" style="background:#fff0f0;color:#ef4444">Cerrar sesión</a>
    </div>
  </section>

</main>

<script>
  // Pequeña mejora: confirmar al salir si el quick-add tiene contenido
  (function(){
    var quickForm = document.getElementById('quickAddForm');
    if (!quickForm) return;
    var inputs = quickForm.querySelectorAll('input');
    window.addEventListener('beforeunload', function(e){
      var dirty = false;
      inputs.forEach(function(i){ if (i.value && i.value.toString().trim() !== '' && i.name !== 'q_stock') dirty = true; });
      if (dirty) {
        // show confirmation only if user tries to reload/close with content
        e.returnValue = "Tienes datos sin guardar en el formulario rápido.";
        return e.returnValue;
      }
    });
  })();
</script>

</body>
</html>
