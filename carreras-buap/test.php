<?php
/* =====================================================================
   TEST VOCACIONAL · 60 preguntas (se leen de pwa_test_preguntas)
   Al enviarlo se guarda en pwa_test_resultados.
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/test.php';

$preguntas = preguntas_test();
$total = count($preguntas);
$u = usuario();
$error = '';
$resp = [];
$nombre = $u['nombre'] ?? '';
$edad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $nombre = mb_substr(post('nombre'), 0, 100);
    $edad = post('edad');
    foreach ($preguntas as $n => $_) {
        $v = $_POST['r' . $n] ?? '';
        if ($v === '0' || $v === '1' || $v === '2') $resp[$n] = (int)$v;
    }
    if (mb_strlen($nombre) < 2) {
        $error = 'Escribe tu nombre para guardar tu resultado.';
    } elseif ($edad !== '' && (!ctype_digit($edad) || $edad < 12 || $edad > 80)) {
        $error = 'La edad debe estar entre 12 y 80 años (o déjala vacía).';
    } elseif (count($resp) < $total) {
        $error = 'Te faltan ' . ($total - count($resp)) . ' preguntas por responder.';
    } else {
        $res = calcular_test($resp, $preguntas);
        $cadena = '';
        foreach ($preguntas as $n => $_) $cadena .= $resp[$n];
        q('INSERT INTO pwa_test_resultados (usuario_id, nombre, edad, respuestas, r, i, a, s, e, c, perfil, afin_fisio, afin_mkt, recomendada, creado)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $u['id'] ?? null, $nombre, $edad === '' ? null : (int)$edad, $cadena,
            $res['pct']['R'], $res['pct']['I'], $res['pct']['A'], $res['pct']['S'], $res['pct']['E'], $res['pct']['C'],
            $res['perfil'], $res['afin'][1], $res['afin'][2], $res['recomendada'], ahora(),
        ]);
        $id = (int)db()->lastInsertId();
        $_SESSION['tests'][] = $id;
        flash('🎉 ¡Test terminado! Este es tu resultado.');
        redirigir('test_resultado.php?id=' . $id);
    }
}

// Historial: tests del usuario con sesión, o los hechos en esta visita
if ($u) {
    $historial = q('SELECT id, perfil, afin_fisio, afin_mkt, recomendada, creado FROM pwa_test_resultados WHERE usuario_id = ? ORDER BY id DESC LIMIT 10', [$u['id']])->fetchAll();
} elseif (!empty($_SESSION['tests'])) {
    $ids = array_map('intval', $_SESSION['tests']);
    $historial = q('SELECT id, perfil, afin_fisio, afin_mkt, recomendada, creado FROM pwa_test_resultados WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id DESC', $ids)->fetchAll();
} else {
    $historial = [];
}
$hechos = (int)q('SELECT COUNT(*) FROM pwa_test_resultados')->fetchColumn();
$porPagina = 10;
$paginas = array_chunk($preguntas, $porPagina, true);

$titulo = 'Test vocacional';
$pagina = 'test';
$tema   = 'portal';
$descripcion = 'Test vocacional de 60 preguntas basado en el modelo RIASEC: descubre tus intereses y tu afinidad con Fisioterapia y Marketing Digital.';
$banner = ['img' => 'assets/img/mkt/estrategia.svg', 'ilus' => true, 'etiqueta' => '🧭 ' . $total . ' preguntas · 8 minutos aprox.', 'titulo' => 'Test vocacional', 'sub' => 'Descubre tus intereses y qué tan compatible eres con cada carrera'];
require __DIR__ . '/includes/header.php';
?>

<section class="contenedor test-intro-grid">
  <div class="tarjeta revelar">
    <h2>¿Cómo funciona?</h2>
    <ol class="lista-num">
      <li>Lee cada actividad y responde con sinceridad: <b>👍 Me interesa</b>, <b>🤔 Tal vez</b> o <b>👎 No me interesa</b>.</li>
      <li>Son <b><?= $total ?> preguntas</b> en <?= count($paginas) ?> bloques de <?= $porPagina ?>. No hay respuestas buenas ni malas.</li>
      <li>Al terminar verás tu perfil en 6 áreas (modelo RIASEC de Holland) y tu afinidad con cada carrera.</li>
      <li>Tu resultado se guarda en la base de datos para que lo consultes después.</li>
    </ol>
  </div>
  <div class="tarjeta areas-mini revelar">
    <h3>Las 6 áreas que mide</h3>
    <ul>
      <?php foreach (AREAS_TEST as $a): ?>
        <li><span style="--c:<?= $a['color'] ?>"><?= $a['emoji'] ?></span><div><b><?= e($a['nombre']) ?></b><small><?= e($a['desc']) ?></small></div></li>
      <?php endforeach; ?>
    </ul>
    <p class="nota">👥 <?= $hechos ?> personas ya hicieron el test.</p>
  </div>
</section>

<section class="contenedor test-zona" id="hacer">
  <form method="post" class="test-form" id="testVocacional" novalidate data-total="<?= $total ?>">
    <?= csrf_campo() ?>
    <div class="enc-progreso" aria-hidden="true"><div><i id="tvBarra"></i></div><span id="tvTexto">0 / <?= $total ?></span></div>
    <?php if ($error): ?><div class="alerta error" role="alert"><?= e($error) ?></div><?php endif; ?>

    <div class="tv-pasos" id="tvPasos">
      <fieldset class="tv-paso tarjeta" data-paso="0">
        <legend><span class="nro">👋</span>Antes de empezar</legend>
        <div class="campos">
          <label class="campo">Tu nombre<input name="nombre" value="<?= e($nombre) ?>" required maxlength="100" autocomplete="given-name"></label>
          <label class="campo">Edad (opcional)<input type="number" name="edad" value="<?= e($edad) ?>" min="12" max="80" inputmode="numeric"></label>
        </div>
        <?php if (!$u): ?><p class="nota">💡 Si <a href="login.php?next=test.php">inicias sesión</a>, el resultado queda ligado a tu cuenta. Si no, igual se guarda y puedes verlo en esta visita.</p><?php endif; ?>
      </fieldset>

      <?php foreach ($paginas as $k => $grupo): ?>
      <fieldset class="tv-paso tarjeta" data-paso="<?= $k + 1 ?>">
        <legend><span class="nro"><?= $k + 1 ?></span>Bloque <?= $k + 1 ?> de <?= count($paginas) ?> · ¿Qué tanto te interesa…?</legend>
        <?php foreach ($grupo as $n => $p): ?>
        <div class="tv-preg" data-n="<?= $n ?>">
          <p><span class="tv-num"><?= $n ?></span><?= e($p['texto']) ?></p>
          <div class="tv-ops" role="radiogroup" aria-label="Pregunta <?= $n ?>">
            <?php foreach (OPCIONES_TEST as $val => [$emo, $txt]): ?>
              <label class="tv-op op-<?= $val ?>"><input type="radio" name="r<?= $n ?>" value="<?= $val ?>"<?= (isset($resp[$n]) && $resp[$n] === $val) ? ' checked' : '' ?>><span><?= $emo ?> <?= $txt ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </fieldset>
      <?php endforeach; ?>

      <fieldset class="tv-paso tarjeta final" data-paso="<?= count($paginas) + 1 ?>">
        <legend><span class="nro">✓</span>¡Listo!</legend>
        <p>Respondiste todas las preguntas. Presiona el botón para calcular y guardar tu resultado.</p>
        <button class="btn btn-bloque" type="submit">🧭 Ver mi resultado</button>
      </fieldset>
    </div>

    <div class="enc-nav" id="tvNav" hidden>
      <button type="button" class="btn btn-borde" id="tvAnterior">← Anterior</button>
      <div class="enc-puntos" id="tvPuntos"></div>
      <button type="button" class="btn" id="tvSiguiente">Siguiente →</button>
    </div>
  </form>
</section>

<section class="contenedor" id="historial">
  <h2 class="titulo-sec">Mis resultados guardados</h2>
  <?php if (!$historial): ?>
    <p class="vacio-msg tarjeta"><?= $u ? 'Aún no has hecho el test con tu cuenta.' : 'Aquí aparecerán los resultados que hagas en esta visita. Inicia sesión para guardarlos en tu cuenta.' ?></p>
  <?php else: ?>
  <div class="historial-tests">
    <?php foreach ($historial as $h): $rc = carrera_por_id((int)$h['recomendada']); ?>
      <a class="tarjeta hist" href="test_resultado.php?id=<?= (int)$h['id'] ?>">
        <span class="perfil-chip"><?= e($h['perfil']) ?></span>
        <div><b><?= e($rc['icono'] . ' ' . $rc['corto']) ?></b><small><?= e(date('d/m/Y H:i', strtotime($h['creado']))) ?> · Fisio <?= (int)$h['afin_fisio'] ?>% · Mkt <?= (int)$h['afin_mkt'] ?>%</small></div>
        <span aria-hidden="true">→</span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
