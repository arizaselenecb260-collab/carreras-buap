<?php
/* =====================================================================
   DATOS FIJOS DE CADA CARRERA
   (Los datos que cambian —materias, fichas, encuestas, test— viven en
   la base de datos. Aquí solo va lo que define el diseño de cada una.)
   ===================================================================== */

const CARRERAS = [
    'fisioterapia' => [
        'id'        => 1,
        'slug'      => 'fisioterapia',
        'nombre'    => 'Licenciatura en Fisioterapia',
        'corto'     => 'Fisioterapia',
        'facultad'  => 'Facultad de Medicina',
        'lema'      => 'Devuelve el movimiento, transforma vidas.',
        'semestres' => 8,
        'creditos'  => 342,
        'sedes'     => ['Puebla (CU)', 'Teziutlán'],
        'etiqueta_sede' => 'Sede',
        'areas'     => ['Básica', 'Formativa', 'Profesional', 'Integración'],
        'logo'      => 'assets/img/logos/fisio-web.png',
        'logo_paint'=> 'assets/img/logos/fisio.png',
        'portada'   => 'assets/img/fisio/fisio1.jpg',
        'portada_sm'=> 'assets/img/fisio/fisio1-sm.jpg',
        'banner_plan'  => 'assets/img/fisio/fisio2.jpg',
        'banner_campo' => 'assets/img/fisio/fisio1.jpg',
        'video'     => 'assets/media/promo_fisioterapia.mp4',
        'audio'     => 'assets/media/spot_fisioterapia.mp3',
        'poster'    => 'assets/img/fisio/poster.jpg',
        'icono'     => '🩺',
        'clave_ej'  => 'FIS-101',
        'ilustrado' => false,
        'riasec'    => ['S' => 0.40, 'R' => 0.35, 'I' => 0.25],
        'resumen'   => 'Previene, evalúa y rehabilita el movimiento del cuerpo humano en hospitales, clubes deportivos y consultorios.',
    ],
    'marketing' => [
        'id'        => 2,
        'slug'      => 'marketing',
        'nombre'    => 'Licenciatura en Mercadotecnia y Medios Digitales',
        'corto'     => 'Marketing Digital',
        'facultad'  => 'Facultad de Ciencias de la Comunicación',
        'lema'      => 'Ideas que conectan, datos que venden.',
        'semestres' => 9,
        'creditos'  => 234,
        'sedes'     => ['Escolarizada (CU)', 'A distancia'],
        'etiqueta_sede' => 'Modalidad',
        'areas'     => ['Formación general', 'Comunicación', 'Mercadotecnia', 'Medios digitales', 'Integración'],
        'logo'      => 'assets/img/logos/mkt-web.png',
        'logo_paint'=> 'assets/img/logos/mkt.png',
        'portada'   => 'assets/img/mkt/hero.svg',
        'portada_sm'=> 'assets/img/mkt/hero.svg',
        'banner_plan'  => 'assets/img/mkt/estrategia.svg',
        'banner_campo' => 'assets/img/mkt/analitica.svg',
        'video'     => 'assets/media/promo_marketing.mp4',
        'audio'     => 'assets/media/spot_marketing.mp3',
        'poster'    => 'assets/img/mkt/poster.jpg',
        'icono'     => '📣',
        'clave_ej'  => 'LMDS-101',
        'ilustrado' => true,
        'riasec'    => ['E' => 0.35, 'A' => 0.30, 'S' => 0.20, 'C' => 0.15],
        'resumen'   => 'Crea marcas, contenidos y campañas digitales, y mide sus resultados con datos para hacer crecer a las organizaciones.',
    ],
];

/** Devuelve la carrera pedida en ?c= (o la primera si no es válida). */
function carrera_actual(?string $slug = null): array
{
    $slug = $slug ?? (string)($_GET['c'] ?? '');
    return CARRERAS[$slug] ?? CARRERAS['fisioterapia'];
}

function carrera_por_id(int $id): ?array
{
    foreach (CARRERAS as $c) {
        if ($c['id'] === $id) return $c;
    }
    return null;
}

/** Convierte "Medios digitales" en "medios-digitales" para usarlo como clase CSS. */
function slug_area(string $a): string
{
    return strtolower(strtr($a, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'a', ' ' => '-']));
}
