<?php
/* Página 1 · Plan de estudios (las materias se leen de la base de datos) */
require_once __DIR__ . '/includes/auth.php';
$C = carrera_actual();
$titulo = 'Plan de Estudios · ' . $C['corto'];
$pagina = 'plan';
$tema   = $C['slug'];
$banner = ['img' => $C['banner_plan'], 'ilus' => $C['ilustrado'], 'etiqueta' => $C['corto'] . ' · Página 1', 'titulo' => 'Plan de Estudios', 'sub' => $C['semestres'] . ' semestres · ' . $C['creditos'] . ' créditos · ' . $C['facultad']];
require __DIR__ . '/includes/header.php';

// CONSULTA (Read) de la base de datos, solo de esta carrera
$materias = q('SELECT semestre, clave, nombre, area, creditos FROM pwa_materias WHERE carrera_id = ? ORDER BY semestre, clave', [$C['id']])->fetchAll();
$porSem = [];
foreach ($materias as $m) {
    $porSem[(int)$m['semestre']][] = $m;
}
$areas = q('SELECT area, COUNT(*) AS n, SUM(creditos) AS cr FROM pwa_materias WHERE carrera_id = ? GROUP BY area ORDER BY cr DESC', [$C['id']])->fetchAll();
$totalCr = array_sum(array_column($materias, 'creditos'));
$maxSem = $porSem ? max(array_map(fn($l) => array_sum(array_column($l, 'creditos')), $porSem)) : 1;
?>

<section class="contenedor" id="semestres">
  <div class="cabecera-sec">
    <h2 class="titulo-sec">Materias por semestre</h2>
    <?php if (es_admin()): ?><a href="materias.php?carrera=<?= $C['id'] ?>" class="btn btn-chico">🛠️ Administrar materias</a><?php endif; ?>
  </div>
  <p class="lead">Datos cargados en vivo desde la base de datos: <b><?= count($materias) ?> materias</b> y <b><?= (int)$totalCr ?> créditos</b>. Usa las pestañas, el buscador o filtra por área.</p>

  <div class="herramientas">
    <label class="buscador"><span aria-hidden="true">🔍</span><input type="search" id="buscaMateria" placeholder="Buscar materia o clave…" aria-label="Buscar materia"></label>
    <div class="filtros" id="filtrosArea" role="group" aria-label="Filtrar por área">
      <button class="chip-filtro activo" data-area="todas">Todas</button>
      <?php foreach ($areas as $a): ?>
        <button class="chip-filtro" data-area="<?= e(slug_area($a['area'])) ?>"><?= e($a['area']) ?></button>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="tabs" id="tabs" role="tablist">
    <?php for ($s = 1; $s <= $C['semestres']; $s++): ?>
      <button class="tab<?= $s === 1 ? ' activo' : '' ?>" role="tab" data-sem="<?= $s ?>" aria-selected="<?= $s === 1 ? 'true' : 'false' ?>"><?= $s ?>.º</button>
    <?php endfor; ?>
  </div>

  <div id="paneles">
    <?php for ($s = 1; $s <= $C['semestres']; $s++): $lista = $porSem[$s] ?? []; $suma = array_sum(array_column($lista, 'creditos')); ?>
    <div class="panel-sem<?= $s === 1 ? ' visible' : '' ?>" data-sem="<?= $s ?>" role="tabpanel">
      <h3 class="sem-titulo"><?= $s ?>.º semestre</h3>
      <div class="scroll-x tarjeta-tabla">
        <table class="tabla">
          <thead><tr><th>Clave</th><th>Materia</th><th>Área</th><th class="num">Créditos</th></tr></thead>
          <tbody>
          <?php if (!$lista): ?>
            <tr class="vacio"><td colspan="4">Sin materias registradas.</td></tr>
          <?php else: foreach ($lista as $m): ?>
            <tr data-area="<?= e(slug_area($m['area'])) ?>">
              <td><code><?= e($m['clave']) ?></code></td>
              <td><?= e($m['nombre']) ?></td>
              <td><span class="chip area-<?= e(slug_area($m['area'])) ?>"><?= e($m['area']) ?></span></td>
              <td class="num"><?= (int)$m['creditos'] ?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
          <?php if ($lista): ?><tfoot><tr><td colspan="3">Total del semestre</td><td class="num"><?= (int)$suma ?></td></tr></tfoot><?php endif; ?>
        </table>
      </div>
    </div>
    <?php endfor; ?>
  </div>
  <p class="nota">* <?= $C['slug'] === 'fisioterapia' ? 'Materias y claves de referencia para este proyecto escolar. El mapa curricular oficial se consulta en la Facultad de Medicina BUAP.' : 'Materias basadas en el mapa curricular del plan 2016 escolarizado; algunas claves y su distribución por semestre son de referencia. Consulta el mapa oficial en la Facultad de Ciencias de la Comunicación BUAP.' ?></p>
</section>

<section class="contenedor dos-col">
  <div class="tarjeta revelar">
    <h3>Créditos por semestre</h3>
    <div class="grafica" role="img" aria-label="Gráfica de créditos por semestre">
      <?php for ($s = 1; $s <= $C['semestres']; $s++): $suma = array_sum(array_column($porSem[$s] ?? [], 'creditos')); ?>
        <div class="g-col"><i style="--h:<?= round($suma / $maxSem * 100) ?>%"><b><?= (int)$suma ?></b></i><span><?= $s ?>.º</span></div>
      <?php endfor; ?>
    </div>
  </div>
  <div class="tarjeta revelar">
    <h3>Áreas de formación</h3>
    <table class="tabla tabla-simple">
      <thead><tr><th>Área</th><th class="num">Materias</th><th class="num">Créditos</th><th class="num">%</th></tr></thead>
      <tbody>
        <?php foreach ($areas as $a): ?>
        <tr><td><span class="chip area-<?= e(slug_area($a['area'])) ?>"><?= e($a['area']) ?></span></td><td class="num"><?= (int)$a['n'] ?></td><td class="num"><?= (int)$a['cr'] ?></td><td class="num"><?= $totalCr ? round($a['cr'] * 100 / $totalCr) : 0 ?>%</td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/contenido/' . $C['slug'] . '/plan_extra.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
