<?php
/* Portal principal: las dos carreras en una sola aplicación */
require_once __DIR__ . '/includes/auth.php';
$titulo = 'Inicio';
$pagina = 'inicio';
$tema   = 'portal';
$stats = [
    'tests'  => (int)q('SELECT COUNT(*) FROM pwa_test_resultados')->fetchColumn(),
    'fichas' => (int)q('SELECT COUNT(*) FROM pwa_aspirantes')->fetchColumn(),
    'mat'    => (int)q('SELECT COUNT(*) FROM pwa_materias')->fetchColumn(),
];
require __DIR__ . '/includes/header.php';
?>

<section class="hero hero-portal">
  <div class="hero-txt revelar">
    <span class="etiqueta">Orientación vocacional · BUAP 2026</span>
    <h1>Encuentra la carrera <em>hecha para ti.</em></h1>
    <p>Explora dos licenciaturas de la BUAP —Fisioterapia y Mercadotecnia y Medios Digitales—, descubre tu perfil con un test de <?= 60 ?> preguntas y solicita tu ficha en un solo lugar.</p>
    <div class="hero-btns">
      <a href="test.php" class="btn">🧭 Hacer el test vocacional</a>
      <a href="#carreras" class="btn btn-borde">Ver las carreras</a>
    </div>
    <ul class="hero-datos">
      <li><b>2</b><span>licenciaturas</span></li>
      <li><b><?= $stats['mat'] ?></b><span>materias en la BD</span></li>
      <li><b><?= $stats['tests'] ?></b><span>tests realizados</span></li>
    </ul>
  </div>
  <div class="hero-collage revelar" aria-hidden="true">
    <img class="col-a" src="assets/img/fisio/fisio1-sm.jpg" alt="">
    <img class="col-b" src="assets/img/mkt/hero.svg" alt="">
    <div class="flotante f1"><span>🩺</span><div><b>Fisioterapia</b><small>8 semestres</small></div></div>
    <div class="flotante f2"><span>📣</span><div><b>Marketing Digital</b><small>9 semestres</small></div></div>
  </div>
</section>

<section class="contenedor" id="carreras">
  <h2 class="titulo-sec">Elige una carrera para explorar</h2>
  <div class="grid-2 carreras-grid">
    <?php foreach (CARRERAS as $c): ?>
    <article class="carrera-card card-<?= $c['slug'] ?> revelar">
      <a href="carrera.php?c=<?= $c['slug'] ?>" class="carrera-foto"><img src="<?= e($c['portada_sm']) ?>" alt="Imagen de la carrera de <?= e($c['corto']) ?>" loading="lazy"></a>
      <div class="carrera-cuerpo">
        <div class="carrera-tit"><img src="<?= e($c['logo']) ?>" alt="Logotipo de <?= e($c['corto']) ?>" width="60" height="60"><div><h3><?= e($c['nombre']) ?></h3><small><?= e($c['facultad']) ?></small></div></div>
        <p><?= e($c['resumen']) ?></p>
        <ul class="carrera-datos">
          <li><b><?= $c['semestres'] ?></b><span>semestres</span></li>
          <li><b><?= $c['creditos'] ?></b><span>créditos</span></li>
          <li><b><?= count($c['sedes']) ?></b><span><?= $c['slug'] === 'fisioterapia' ? 'sedes' : 'modalidades' ?></span></li>
        </ul>
        <div class="hero-btns">
          <a href="carrera.php?c=<?= $c['slug'] ?>" class="btn btn-chico">Conocer la carrera</a>
          <a href="plan.php?c=<?= $c['slug'] ?>" class="btn btn-chico btn-borde">Plan de estudios</a>
          <a href="campo.php?c=<?= $c['slug'] ?>" class="btn btn-chico btn-borde">Campo laboral</a>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="contenedor">
  <div class="test-cta tarjeta revelar">
    <div class="test-cta-ico" aria-hidden="true">🧭</div>
    <div>
      <span class="etiqueta">60 preguntas · modelo RIASEC</span>
      <h2>¿No sabes cuál elegir?</h2>
      <p>El test vocacional mide tus intereses en 6 áreas (Realista, Investigador, Artístico, Social, Emprendedor y Convencional) y calcula tu afinidad con cada carrera. El resultado se guarda en la base de datos.</p>
    </div>
    <a href="test.php" class="btn">Empezar el test</a>
  </div>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Compara las carreras</h2>
  <div class="scroll-x tarjeta-tabla">
    <table class="tabla tabla-comparar">
      <thead><tr><th>Aspecto</th><th>🩺 Fisioterapia</th><th>📣 Mercadotecnia y Medios Digitales</th></tr></thead>
      <tbody>
        <tr><td>Facultad</td><td>Facultad de Medicina</td><td>Facultad de Ciencias de la Comunicación</td></tr>
        <tr><td>Duración</td><td>8 semestres (4 años)</td><td>9 semestres</td></tr>
        <tr><td>Créditos</td><td>342</td><td>234 (de referencia)</td></tr>
        <tr><td>Dónde</td><td>Puebla y Teziutlán</td><td>Ciudad Universitaria (escolarizada) y a distancia</td></tr>
        <tr><td>Te gustará si…</td><td>Te interesa el cuerpo humano, el deporte y ayudar a otros</td><td>Te gustan las redes, la creatividad, los datos y los negocios</td></tr>
        <tr><td>Áreas del test más afines</td><td>🤝 Social · 🛠️ Realista · 🔬 Investigador</td><td>🚀 Emprendedor · 🎨 Artístico · 🤝 Social · 📋 Convencional</td></tr>
        <tr><td>Dónde trabajar</td><td>Hospitales, centros de rehabilitación, clubes deportivos, consultorio propio</td><td>Agencias, empresas, medios, comercio electrónico, negocio propio</td></tr>
      </tbody>
    </table>
  </div>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Tu camino en 4 pasos</h2>
  <div class="pasos-portal">
    <a href="test.php" class="revelar"><span>1</span><b>Haz el test</b><small>Descubre tu perfil vocacional</small></a>
    <a href="#carreras" class="revelar"><span>2</span><b>Explora</b><small>Plan de estudios y campo laboral</small></a>
    <a href="registro.php#ficha" class="revelar"><span>3</span><b>Solicita tu ficha</b><small>Crea tu cuenta y regístrate</small></a>
    <a href="encuesta.php" class="revelar"><span>4</span><b>Opina</b><small>Responde la encuesta del sitio</small></a>
  </div>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Conoce cada carrera en 30 segundos</h2>
  <div class="grid-2">
    <?php foreach (CARRERAS as $c): ?>
    <div class="revelar">
      <div class="video-marco"><video controls preload="none" poster="<?= e($c['poster']) ?>" playsinline><source src="<?= e($c['video']) ?>" type="video/mp4"></video></div>
      <p class="enlaces-media"><a href="<?= e($c['video']) ?>" target="_blank" rel="noopener">▶ Video <?= e($c['corto']) ?></a><a href="<?= e($c['audio']) ?>" target="_blank" rel="noopener">🎧 Spot de radio</a></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="contenedor">
  <h2 class="titulo-sec">Preguntas frecuentes</h2>
  <div class="acordeon">
    <details open><summary>¿Necesito una cuenta para cada carrera?</summary><p>No. Con una sola cuenta puedes solicitar fichas para cualquiera de las dos carreras y guardar tus resultados del test vocacional.</p></details>
    <details><summary>¿El test vocacional decide por mí?</summary><p>No. Es una herramienta de orientación basada en tus intereses. Úsalo para reflexionar y platícalo con tu orientador(a) y tu familia.</p></details>
    <details><summary>¿Puedo usar el sitio sin internet?</summary><p>Sí. Puedes instalarlo como app y las páginas que ya visitaste se ven sin conexión. Para guardar fichas, encuestas o el test sí necesitas internet.</p></details>
    <details><summary>¿Este sitio es oficial de la BUAP?</summary><p>No, es un proyecto escolar del CBTIS 260. Para trámites reales consulta siempre la convocatoria oficial de admisión de la BUAP.</p></details>
  </div>
</section>

<section class="contenedor">
  <div class="cta revelar">
    <div>
      <h2>¿Listo(a) para dar el primer paso?</h2>
      <p>Ya hay <?= $stats['fichas'] ?> fichas registradas. Crea tu cuenta y solicita la tuya.</p>
    </div>
    <div class="hero-btns">
      <a href="<?= usuario() ? 'registro.php#ficha' : 'crear_cuenta.php' ?>" class="btn btn-oro">Comenzar</a>
      <a href="test.php" class="btn btn-claro">Hacer el test</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
