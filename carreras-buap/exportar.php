<?php
/* Exporta todas las fichas a un archivo CSV que abre Excel (solo administrador). */
require_once __DIR__ . '/includes/auth.php';
requiere_login('admin');

$tipo = ($_GET['tipo'] ?? '') === 'test' ? 'test' : 'fichas';
if ($tipo === 'test') {
    $filas = q('SELECT id, nombre, edad, r, i, a, s, e, c, perfil, afin_fisio, afin_mkt, recomendada, creado FROM pwa_test_resultados ORDER BY id')->fetchAll();
    foreach ($filas as &$f) { $f['recomendada'] = carrera_por_id((int)$f['recomendada'])['corto'] ?? ''; }
    unset($f);
    $titulos = ['Folio', 'Nombre', 'Edad', 'Realista %', 'Investigador %', 'Artístico %', 'Social %', 'Emprendedor %', 'Convencional %', 'Perfil', 'Afinidad Fisioterapia %', 'Afinidad Marketing %', 'Carrera recomendada', 'Fecha'];
} else {
    $filas = q('SELECT a.id, c.nombre AS carrera, a.nombre, a.curp, a.correo, a.telefono, a.fecha_nac, a.bachillerato, a.promedio, a.sede, a.turno, a.medio, a.estado, a.creado FROM pwa_aspirantes a JOIN pwa_carreras c ON c.id = a.carrera_id ORDER BY a.id')->fetchAll();
    $titulos = ['Folio', 'Carrera', 'Nombre', 'CURP', 'Correo', 'Teléfono', 'Fecha de nacimiento', 'Bachillerato', 'Promedio', 'Sede/Modalidad', 'Turno', 'Medio', 'Estado', 'Registrada'];
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $tipo . '_carreras_buap_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete los acentos
fputcsv($out, $titulos);
foreach ($filas as $f) {
    fputcsv($out, array_values($f));
}
fclose($out);
