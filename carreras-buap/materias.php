<?php
/* =====================================================================
   CATÁLOGO DE MATERIAS · CRUD (solo administrador)
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
requiere_login('admin');

$errores = [];
// Carrera con la que se trabaja (?carrera=1 ó 2)
$CR = carrera_por_id((int)($_GET['carrera'] ?? $_POST['carrera_id'] ?? 1)) ?? CARRERAS['fisioterapia'];
$m = ['id' => 0, 'carrera_id' => $CR['id'], 'semestre' => 1, 'clave' => '', 'nombre' => '', 'area' => $CR['areas'][0], 'creditos' => 4];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $accion = post('accion');

    if ($accion === 'eliminar') {
        $fila = q('SELECT nombre FROM pwa_materias WHERE id = ? AND carrera_id = ?', [(int)post('id'), $CR['id']])->fetch();
        q('DELETE FROM pwa_materias WHERE id = ? AND carrera_id = ?', [(int)post('id'), $CR['id']]);
        flash('🗑️ Materia «' . ($fila['nombre'] ?? '') . '» eliminada.', 'aviso');
        redirigir('materias.php?carrera=' . $CR['id']);
    }

    if ($accion === 'crear' || $accion === 'actualizar') {
        $m = [
            'id'       => (int)post('id'),
            'carrera_id' => $CR['id'],
            'semestre' => (int)post('semestre'),
            'clave'    => strtoupper(post('clave')),
            'nombre'   => post('nombre'),
            'area'     => post('area'),
            'creditos' => (int)post('creditos'),
        ];
        if ($m['semestre'] < 1 || $m['semestre'] > $CR['semestres']) $errores['semestre'] = 'Semestre de 1 a ' . $CR['semestres'] . '.';
        if (!preg_match('/^[A-Z]{2,5}-\d{3}$/', $m['clave'])) $errores['clave'] = 'Formato: ' . $CR['clave_ej'];
        if (mb_strlen($m['nombre']) < 3) $errores['nombre'] = 'Escribe el nombre de la materia.';
        if (!in_array($m['area'], $CR['areas'], true)) $errores['area'] = 'Elige un área.';
        if ($m['creditos'] < 1 || $m['creditos'] > 15) $errores['creditos'] = 'Créditos de 1 a 15.';
        $dup = q('SELECT id FROM pwa_materias WHERE carrera_id = ? AND clave = ? AND id <> ?', [$CR['id'], $m['clave'], $m['id']])->fetch();
        if ($dup) $errores['clave'] = 'Ya existe una materia con esa clave.';

        if (!$errores) {
            if ($accion === 'crear') {
                q('INSERT INTO pwa_materias (carrera_id, semestre, clave, nombre, area, creditos) VALUES (?, ?, ?, ?, ?, ?)', [$CR['id'], $m['semestre'], $m['clave'], $m['nombre'], $m['area'], $m['creditos']]);
                flash('✅ Materia «' . $m['nombre'] . '» agregada.');
            } else {
                q('UPDATE pwa_materias SET semestre=?, clave=?, nombre=?, area=?, creditos=? WHERE id=? AND carrera_id=?', [$m['semestre'], $m['clave'], $m['nombre'], $m['area'], $m['creditos'], $m['id'], $CR['id']]);
                flash('✏️ Materia «' . $m['nombre'] . '» actualizada.');
            }
            redirigir('materias.php?carrera=' . $CR['id'] . '&sem=' . $m['semestre']);
        }
    }
}

if (isset($_GET['editar']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $fila = q('SELECT * FROM pwa_materias WHERE id = ? AND carrera_id = ?', [(int)$_GET['editar'], $CR['id']])->fetch();
    if ($fila) $m = $fila;
}

$sem = (int)($_GET['sem'] ?? 0);
$buscar = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT * FROM pwa_materias WHERE carrera_id = ?';
$p = [$CR['id']];
if ($sem >= 1 && $sem <= $CR['semestres']) { $sql .= ' AND semestre = ?'; $p[] = $sem; }
if ($buscar !== '') { $sql .= ' AND (nombre LIKE ? OR clave LIKE ?)'; array_push($p, "%$buscar%", "%$buscar%"); }
$lista = q($sql . ' ORDER BY semestre, clave', $p)->fetchAll();
$total = (int)q('SELECT COUNT(*) FROM pwa_materias WHERE carrera_id = ?', [$CR['id']])->fetchColumn();

$editando = (int)$m['id'] > 0;
$err = fn($c) => isset($errores[$c]) ? '<small class="error-campo">' . e($errores[$c]) . '</small>' : '';

$titulo = 'Administrar materias';
$pagina = 'materias';
$tema   = $CR['slug'];
require __DIR__ . '/includes/header.php';
?>

<section class="contenedor encabezado-admin">
  <div>
    <span class="etiqueta">Panel del administrador</span>
    <h1>Catálogo de materias</h1>
    <p>Insertar, consultar, actualizar y eliminar materias. <b><?= e($CR['corto']) ?></b> tiene <b><?= $total ?></b> materias en la base de datos.</p>
    <div class="pestanas-carrera">
      <?php foreach (CARRERAS as $c): ?><a href="materias.php?carrera=<?= $c['id'] ?>" class="chip-filtro<?= $c['id'] === $CR['id'] ? ' activo' : '' ?>"><?= $c['icono'] ?> <?= e($c['corto']) ?></a><?php endforeach; ?>
    </div>
  </div>
  <a href="plan.php?c=<?= $CR['slug'] ?>" class="btn btn-borde">Ver plan público →</a>
</section>

<section class="contenedor crud">
  <form method="post" class="formulario tarjeta" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
    <input type="hidden" name="carrera_id" value="<?= $CR['id'] ?>">
    <h3><?= $editando ? '✏️ Editar materia' : '➕ Nueva materia' ?></h3>
    <?php if ($errores): ?><div class="alerta error">Revisa los campos marcados.</div><?php endif; ?>
    <div class="campos">
      <label class="campo">Clave<input name="clave" value="<?= e($m['clave']) ?>" required placeholder="<?= e($CR['clave_ej']) ?>" class="mayus" maxlength="9"><?= $err('clave') ?></label>
      <label class="campo">Semestre
        <select name="semestre"><?php for ($s = 1; $s <= $CR['semestres']; $s++): ?><option value="<?= $s ?>"<?= (int)$m['semestre'] === $s ? ' selected' : '' ?>><?= $s ?>.º semestre</option><?php endfor; ?></select><?= $err('semestre') ?>
      </label>
      <label class="campo ancho">Nombre de la materia<input name="nombre" value="<?= e($m['nombre']) ?>" required maxlength="120"><?= $err('nombre') ?></label>
      <label class="campo">Área
        <select name="area"><?php foreach ($CR['areas'] as $a): ?><option<?= $m['area'] === $a ? ' selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?></select><?= $err('area') ?>
      </label>
      <label class="campo">Créditos<input type="number" name="creditos" value="<?= (int)$m['creditos'] ?>" min="1" max="15" required><?= $err('creditos') ?></label>
    </div>
    <div class="form-btns">
      <button class="btn"><?= $editando ? 'Guardar cambios' : 'Agregar materia' ?></button>
      <?php if ($editando): ?><a href="materias.php?carrera=<?= $CR['id'] ?>" class="btn btn-borde">Cancelar</a><?php endif; ?>
    </div>
  </form>

  <div class="tarjeta lista-fichas">
    <form method="get" class="filtro-form">
      <input type="hidden" name="carrera" value="<?= $CR['id'] ?>">
      <input type="search" name="q" value="<?= e($buscar) ?>" placeholder="Buscar materia o clave…" aria-label="Buscar materia">
      <select name="sem" aria-label="Filtrar por semestre"><option value="0">Todos los semestres</option><?php for ($s = 1; $s <= $CR['semestres']; $s++): ?><option value="<?= $s ?>"<?= $sem === $s ? ' selected' : '' ?>><?= $s ?>.º</option><?php endfor; ?></select>
      <button class="btn btn-chico">Filtrar</button>
    </form>
    <div class="scroll-x">
      <table class="tabla tabla-compacta">
        <thead><tr><th>Sem.</th><th>Clave</th><th>Materia</th><th>Área</th><th class="num">Cr.</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if (!$lista): ?>
          <tr class="vacio"><td colspan="6">No hay materias con ese filtro.</td></tr>
        <?php else: foreach ($lista as $fila): ?>
          <tr>
            <td><?= (int)$fila['semestre'] ?>.º</td>
            <td><code><?= e($fila['clave']) ?></code></td>
            <td><?= e($fila['nombre']) ?></td>
            <td><span class="chip area-<?= e(slug_area($fila['area'])) ?>"><?= e($fila['area']) ?></span></td>
            <td class="num"><?= (int)$fila['creditos'] ?></td>
            <td class="acciones">
              <a class="btn btn-chico" href="materias.php?carrera=<?= $CR['id'] ?>&amp;editar=<?= (int)$fila['id'] ?>">Editar</a>
              <form method="post" data-confirmar="¿Eliminar la materia «<?= e($fila['nombre']) ?>»?">
                <?= csrf_campo() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$fila['id'] ?>"><input type="hidden" name="carrera_id" value="<?= $CR['id'] ?>">
                <button class="btn btn-chico btn-peligro">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
