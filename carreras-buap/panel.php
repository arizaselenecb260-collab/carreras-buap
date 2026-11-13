<?php
/* =====================================================================
   PANEL DEL ADMINISTRADOR
   Resumen de la base de datos + datos del servidor (útil para las
   capturas del reporte técnico: muestra la conexión a la BD remota).
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
requiere_login('admin');

$c = [
    'usuarios'   => (int)q('SELECT COUNT(*) FROM pwa_usuarios')->fetchColumn(),
    'aspirantes' => (int)q('SELECT COUNT(*) FROM pwa_aspirantes')->fetchColumn(),
    'materias'   => (int)q('SELECT COUNT(*) FROM pwa_materias')->fetchColumn(),
    'encuestas'  => (int)q('SELECT COUNT(*) FROM pwa_encuestas')->fetchColumn(),
    'tests'      => (int)q('SELECT COUNT(*) FROM pwa_test_resultados')->fetchColumn(),
];
$porCarrera = q('SELECT carrera_id, COUNT(*) AS n FROM pwa_aspirantes GROUP BY carrera_id')->fetchAll(PDO::FETCH_KEY_PAIR);
$recs = q('SELECT recomendada, COUNT(*) AS n FROM pwa_test_resultados GROUP BY recomendada')->fetchAll(PDO::FETCH_KEY_PAIR);
$perfiles = q('SELECT perfil, COUNT(*) AS n FROM pwa_test_resultados GROUP BY perfil ORDER BY n DESC LIMIT 5')->fetchAll();
$ultTests = q('SELECT id, nombre, perfil, recomendada, creado FROM pwa_test_resultados ORDER BY id DESC LIMIT 6')->fetchAll();
$estados = q('SELECT estado, COUNT(*) AS n FROM pwa_aspirantes GROUP BY estado')->fetchAll(PDO::FETCH_KEY_PAIR);
$ultimas = q('SELECT id, carrera_id, nombre, sede, estado, creado FROM pwa_aspirantes ORDER BY id DESC LIMIT 6')->fetchAll();

$version = db()->getAttribute(PDO::ATTR_SERVER_VERSION);
$motor = DB_DRIVER === 'sqlite' ? 'SQLite (modo prueba)' : (stripos($version, 'maria') !== false ? 'MariaDB' : 'MySQL');

$titulo = 'Panel de control';
$pagina = 'panel';
$tema   = 'portal';
require __DIR__ . '/includes/header.php';
?>

<section class="contenedor encabezado-admin">
  <div>
    <span class="etiqueta">Panel del administrador</span>
    <h1>Hola, <?= e(primer_nombre(usuario()['nombre'])) ?> 👋</h1>
    <p>Resumen en tiempo real de la base de datos del sitio.</p>
  </div>
  <div class="hero-btns">
    <a href="exportar.php" class="btn btn-borde">⬇ Fichas (CSV)</a>
    <a href="exportar.php?tipo=test" class="btn btn-borde">⬇ Tests (CSV)</a>
    <a href="materias.php" class="btn">🛠️ CRUD de materias</a>
  </div>
</section>

<section class="contenedor">
  <div class="kpis">
    <a href="registro.php#ficha" class="kpi"><span>📝</span><b class="contador" data-meta="<?= $c['aspirantes'] ?>"><?= $c['aspirantes'] ?></b><small>Fichas de aspirantes</small></a>
    <a href="materias.php" class="kpi"><span>📚</span><b class="contador" data-meta="<?= $c['materias'] ?>"><?= $c['materias'] ?></b><small>Materias</small></a>
    <a href="encuesta.php#resultados" class="kpi"><span>⭐</span><b class="contador" data-meta="<?= $c['encuestas'] ?>"><?= $c['encuestas'] ?></b><small>Encuestas</small></a>
    <a href="#tests" class="kpi"><span>🧭</span><b class="contador" data-meta="<?= $c['tests'] ?>"><?= $c['tests'] ?></b><small>Tests vocacionales</small></a>
    <div class="kpi"><span>👥</span><b class="contador" data-meta="<?= $c['usuarios'] ?>"><?= $c['usuarios'] ?></b><small>Usuarios</small></div>
  </div>
</section>

<section class="contenedor grid-3 panel-grid">
  <div class="tarjeta">
    <h3>Fichas por estado</h3>
    <?php foreach (['Pendiente', 'En revisión', 'Aceptada'] as $s): $n = (int)($estados[$s] ?? 0); $pct = $c['aspirantes'] ? round($n * 100 / $c['aspirantes']) : 0; ?>
      <div class="barra-res"><span><?= e($s) ?></span><div><i style="--w:<?= $pct ?>%"></i></div><b><?= $n ?></b></div>
    <?php endforeach; ?>
    <h3 class="mt">Fichas por carrera</h3>
    <?php foreach (CARRERAS as $cr): $n = (int)($porCarrera[$cr['id']] ?? 0); $pct = $c['aspirantes'] ? round($n * 100 / $c['aspirantes']) : 0; ?>
      <div class="barra-res"><span><?= $cr['icono'] ?> <?= e($cr['corto']) ?></span><div><i style="--w:<?= $pct ?>%"></i></div><b><?= $n ?></b></div>
    <?php endforeach; ?>
  </div>

  <div class="tarjeta">
    <h3>Últimas fichas</h3>
    <ul class="lista-ultimas">
      <?php if (!$ultimas): ?><li>Sin fichas todavía.</li><?php endif; ?>
      <?php foreach ($ultimas as $f): ?>
        <li><a href="registro.php?editar=<?= (int)$f['id'] ?>#ficha"><b>#<?= (int)$f['id'] ?> <?= e($f['nombre']) ?></b><small><?= e((carrera_por_id((int)$f['carrera_id'])['corto'] ?? '') . ' · ' . $f['sede']) ?> · <?= e(date('d/m/Y H:i', strtotime($f['creado']))) ?></small></a><span class="estado estado-<?= e(strtolower(strtr($f['estado'], [' ' => '-', 'ó' => 'o']))) ?>"><?= e($f['estado']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="tarjeta servidor">
    <h3>🖥️ Servidor y base de datos</h3>
    <dl>
      <dt>Estado</dt><dd><span class="punto-ok"></span> Conectado</dd>
      <dt>Motor</dt><dd><?= e($motor) ?> <?= e($version) ?></dd>
      <dt>Host de BD</dt><dd><code><?= e(DB_DRIVER === 'sqlite' ? 'archivo local' : DB_HOST) ?></code></dd>
      <dt>Base de datos</dt><dd><code><?= e(DB_DRIVER === 'sqlite' ? basename(DB_NAME) : DB_NAME) ?></code></dd>
      <dt>Servidor web</dt><dd><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'desconocido') ?></dd>
      <dt>PHP</dt><dd><?= e(PHP_VERSION) ?></dd>
      <dt>Dominio</dt><dd><code><?= e($_SERVER['HTTP_HOST'] ?? '') ?></code></dd>
      <dt>Protocolo</dt><dd><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? '🔒 HTTPS' : 'HTTP' ?></dd>
      <dt>Hora del servidor</dt><dd><?= e(date('d/m/Y H:i:s')) ?></dd>
    </dl>
    <p class="nota">Toma captura de este recuadro para el reporte técnico.</p>
  </div>
</section>

<section class="contenedor grid-2" id="tests">
  <div class="tarjeta">
    <h3>🧭 Carrera recomendada por el test</h3>
    <?php foreach (CARRERAS as $cr): $n = (int)($recs[$cr['id']] ?? 0); $pct = $c['tests'] ? round($n * 100 / $c['tests']) : 0; ?>
      <div class="barra-res"><span><?= $cr['icono'] ?> <?= e($cr['corto']) ?></span><div><i style="--w:<?= $pct ?>%"></i></div><b><?= $n ?></b></div>
    <?php endforeach; ?>
    <h3 class="mt">Perfiles más comunes</h3>
    <?php if (!$perfiles): ?><p class="vacio-msg">Sin tests todavía.</p><?php endif; ?>
    <?php foreach ($perfiles as $p): $pct = $c['tests'] ? round($p['n'] * 100 / $c['tests']) : 0; ?>
      <div class="barra-res"><span><?= e($p['perfil']) ?></span><div><i style="--w:<?= $pct ?>%"></i></div><b><?= (int)$p['n'] ?></b></div>
    <?php endforeach; ?>
  </div>
  <div class="tarjeta">
    <h3>Últimos tests</h3>
    <ul class="lista-ultimas">
      <?php if (!$ultTests): ?><li>Sin tests todavía.</li><?php endif; ?>
      <?php foreach ($ultTests as $t): $rc = carrera_por_id((int)$t['recomendada']); ?>
        <li><a href="test_resultado.php?id=<?= (int)$t['id'] ?>"><b>#<?= (int)$t['id'] ?> <?= e($t['nombre']) ?></b><small><?= e(date('d/m/Y H:i', strtotime($t['creado']))) ?></small></a><span class="perfil-chip"><?= e($t['perfil']) ?></span><span class="chip chip-<?= e($rc['slug']) ?>"><?= $rc['icono'] ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
