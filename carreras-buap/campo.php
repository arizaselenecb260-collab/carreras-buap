<?php
/* Página 2 · Perfil de egreso y campo laboral de cada carrera */
require_once __DIR__ . '/includes/auth.php';
$C = carrera_actual();
$titulo = 'Egreso y Campo Laboral · ' . $C['corto'];
$pagina = 'campo';
$tema   = $C['slug'];
$banner = ['img' => $C['banner_campo'], 'ilus' => $C['ilustrado'], 'etiqueta' => $C['corto'] . ' · Página 2', 'titulo' => 'Perfil de Egreso y Campo Laboral', 'sub' => 'Lo que serás capaz de hacer y dónde podrás trabajar en Puebla'];
require __DIR__ . '/includes/header.php';
include __DIR__ . '/contenido/' . $C['slug'] . '/campo.php';
require __DIR__ . '/includes/footer.php';
