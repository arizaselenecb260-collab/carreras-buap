<?php
http_response_code(404);
$titulo = 'Página no encontrada';
$pagina = '404';
require __DIR__ . '/includes/header.php';
?>
<section class="contenedor offline">
  <h1>404 · Página no encontrada 🧭</h1>
  <p>No encontramos lo que buscabas. Te ayudamos a encontrar tu camino.</p>
  <div class="hero-btns centro">
    <a class="btn" href="index.php">Ir al inicio</a>
    <a class="btn btn-borde" href="test.php">Test vocacional</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
