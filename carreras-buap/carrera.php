<?php
/* Página de inicio de cada carrera: carrera.php?c=fisioterapia  ó  carrera.php?c=marketing */
require_once __DIR__ . '/includes/auth.php';
$C = carrera_actual();
$titulo = $C['corto'];
$pagina = 'carrera';
$tema   = $C['slug'];
require __DIR__ . '/includes/header.php';
include __DIR__ . '/contenido/' . $C['slug'] . '/inicio.php';
require __DIR__ . '/includes/footer.php';
