<?php
/* Resultado del test vocacional (lee pwa_test_resultados) */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/test.php';

$fila = q('SELECT * FROM pwa_test_resultados WHERE id = ?', [(int)($_GET['id'] ?? 0)])->fetch();
if (!$fila || !puede_ver_test($fila)) {
    flash('No encontramos ese resultado o no tienes permiso para verlo.', 'error');
    redirigir('test.php');
}

$pct = ['R' => (int)$fila['r'], 'I' => (int)$fila['i'], 'A' => (int)$fila['a'], 'S' => (int)$fila['s'], 'E' => (int)$fila['e'], 'C' => (int)$fila['c']];
$afin = [1 => (int)$fila['afin_fisio'], 2 => (int)$fila['afin_mkt']];
arsort($afin);
$top = str_split($fila['perfil']);
$rec = carrera_por_id((int)$fila['recomendada']);
$prom = q('SELECT AVG(afin_fisio) AS f, AVG(afin_mkt) AS m, COUNT(*) AS n FROM pwa_test_resultados')->fetch();

$frase = function (int $v): string {
    if ($v >= 75) return 'Afinidad muy alta';
    if ($v >= 55) return 'Afinidad alta';
    if ($v >= 35) return 'Afinidad media';
    return 'Afinidad baja';
};

$titulo = 'Mi resultado del test vocacional';
$pagina = 'test';
$tema   = $rec['slug'];
require __DIR__ . '/includes/header.php';
?>

<section class="contenedor">
  <div class="res-cabecera tarjeta revelar">
    <div>
      <span class="etiqueta">Resultado #<?= (int)$fila['id'] ?> · <?= e(date('d/m/Y H:i', strtotime($fila['creado']))) ?></span>
      <h1>Hola, <?= e(primer_nombre($fila['nombre'])) ?> 👋</h1>
      <p class="lead">Tu perfil es <b><?= e(AREAS_TEST[$top[0]]['nombre']) ?> – <?= e(AREAS_TEST[$top[1]]['nombre']) ?></b>. La carrera del portal que más se parece a tus intereses es:</p>
      <div class="rec-carrera">
        <img src="<?= e($rec['logo']) ?>" alt="" width="72" height="72">
        <div><b><?= e($rec['nombre']) ?></b><small><?= e($rec['facultad']) ?> · <?= $afin[$rec['id']] ?>% de afinidad</small></div>
      </div>
      <div class="hero-btns">
        <a href="carrera.php?c=<?= $rec['slug'] ?>" class="btn">Conocer la carrera</a>
        <a href="registro.php?c=<?= $rec['slug'] ?>#ficha" class="btn btn-borde">Solicitar ficha</a>
      </div>
    </div>
    <?= radar_svg($pct) ?>
  </div>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Tu afinidad con cada carrera</h2>
  <div class="grid-2">
    <?php foreach ($afin as $id => $v): $c = carrera_por_id($id); ?>
    <div class="tarjeta afin-card afin-<?= $c['slug'] ?><?= $id === $rec['id'] ? ' ganadora' : '' ?> revelar">
      <div class="afin-top">
        <img src="<?= e($c['logo']) ?>" alt="" width="56" height="56">
        <div><b><?= e($c['corto']) ?></b><small><?= $frase($v) ?></small></div>
        <div class="dona" style="--p:<?= $v ?>"><b><?= $v ?>%</b></div>
      </div>
      <p><?= e($c['resumen']) ?></p>
      <p class="nota">Se calcula con las áreas: <?php $partes = []; foreach ($c['riasec'] as $a => $w) $partes[] = AREAS_TEST[$a]['nombre'] . ' (' . round($w * 100) . '%)'; echo e(implode(', ', $partes)); ?>.</p>
      <a href="carrera.php?c=<?= $c['slug'] ?>" class="btn btn-chico btn-borde">Ver <?= e($c['corto']) ?> →</a>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="nota">Promedio de las <?= (int)$prom['n'] ?> personas que han hecho el test: Fisioterapia <?= round($prom['f']) ?>% · Marketing Digital <?= round($prom['m']) ?>%.</p>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Tus 6 áreas de interés</h2>
  <div class="tarjeta">
    <?php $orden = $pct; arsort($orden); foreach ($orden as $a => $v): ?>
      <div class="area-res">
        <span class="area-ico" style="--c:<?= AREAS_TEST[$a]['color'] ?>"><?= AREAS_TEST[$a]['emoji'] ?></span>
        <div class="area-txt"><b><?= e(AREAS_TEST[$a]['nombre']) ?></b><small><?= e(AREAS_TEST[$a]['desc']) ?></small></div>
        <div class="barra-res solo"><div><i style="--w:<?= $v ?>%;background:<?= AREAS_TEST[$a]['color'] ?>"></i></div><b><?= $v ?>%</b></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Otras carreras que van con tu perfil</h2>
  <div class="grid-2">
    <?php foreach ($top as $a): ?>
    <div class="tarjeta revelar">
      <h3><?= AREAS_TEST[$a]['emoji'] ?> Perfil <?= e(AREAS_TEST[$a]['nombre']) ?></h3>
      <p><?= e(AREAS_TEST[$a]['desc']) ?></p>
      <div class="chips-grandes"><?php foreach (AREAS_TEST[$a]['carreras'] as $car): ?><span><?= e($car) ?></span><?php endforeach; ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="nota">Ejemplos de carreras afines como orientación. Este test es una herramienta de apoyo, no un diagnóstico; platícalo con tu orientador(a) vocacional.</p>
  <div class="hero-btns centro mt">
    <button type="button" class="btn btn-borde" onclick="window.print()">🖨️ Imprimir / guardar PDF</button>
    <a href="test.php" class="btn btn-borde">🔁 Hacer el test otra vez</a>
    <?php if (!usuario()): ?><a href="crear_cuenta.php" class="btn">Crear cuenta para guardarlo</a><?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
