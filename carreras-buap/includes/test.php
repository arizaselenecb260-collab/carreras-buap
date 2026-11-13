<?php
/* =====================================================================
   TEST VOCACIONAL · Modelo RIASEC de John Holland
   Seis tipos de intereses: Realista, Investigador, Artístico, Social,
   Emprendedor y Convencional. Las 60 preguntas están en la tabla
   pwa_test_preguntas (10 por área). Cada respuesta vale:
     2 = Me interesa · 1 = Tal vez · 0 = No me interesa
   ===================================================================== */

const AREAS_TEST = [
    'R' => ['nombre' => 'Realista', 'emoji' => '🛠️', 'color' => '#00A896',
            'desc' => 'Te gusta la acción, el trabajo práctico y ver resultados concretos con tus manos y tu cuerpo.',
            'carreras' => ['Fisioterapia', 'Cultura Física', 'Ingeniería Mecatrónica', 'Arquitectura']],
    'I' => ['nombre' => 'Investigador', 'emoji' => '🔬', 'color' => '#3D7DD8',
            'desc' => 'Disfrutas entender cómo funcionan las cosas, analizar información y resolver problemas con lógica.',
            'carreras' => ['Medicina', 'Biología', 'Química', 'Ciencias de la Computación']],
    'A' => ['nombre' => 'Artístico', 'emoji' => '🎨', 'color' => '#E84A7F',
            'desc' => 'Eres creativo(a), te expresas con imágenes, palabras o sonidos y buscas ideas originales.',
            'carreras' => ['Mercadotecnia y Medios Digitales', 'Diseño Gráfico', 'Ciencias de la Comunicación', 'Artes Plásticas']],
    'S' => ['nombre' => 'Social', 'emoji' => '🤝', 'color' => '#F5B731',
            'desc' => 'Te motiva ayudar, enseñar y cuidar a otras personas; trabajas bien en equipo.',
            'carreras' => ['Fisioterapia', 'Psicología', 'Enfermería', 'Ciencias de la Educación']],
    'E' => ['nombre' => 'Emprendedor', 'emoji' => '🚀', 'color' => '#F26B21',
            'desc' => 'Te gusta liderar, convencer, vender ideas y tomar decisiones para alcanzar metas.',
            'carreras' => ['Mercadotecnia y Medios Digitales', 'Administración de Empresas', 'Negocios Internacionales', 'Derecho']],
    'C' => ['nombre' => 'Convencional', 'emoji' => '📋', 'color' => '#7A5AC8',
            'desc' => 'Eres ordenado(a) y preciso(a); te sientes cómodo(a) con datos, procedimientos y organización.',
            'carreras' => ['Contaduría Pública', 'Actuaría', 'Administración de Empresas', 'Ciencias de la Computación']],
];

const OPCIONES_TEST = [2 => ['👍', 'Me interesa'], 1 => ['🤔', 'Tal vez'], 0 => ['👎', 'No me interesa']];

/** Preguntas desde la base de datos: [numero => ['area' => 'R', 'texto' => ...]] */
function preguntas_test(): array
{
    $out = [];
    foreach (q('SELECT numero, area, texto FROM pwa_test_preguntas ORDER BY numero')->fetchAll() as $p) {
        $out[(int)$p['numero']] = $p;
    }
    return $out;
}

/**
 * Calcula el resultado.
 * $resp = [numero => 0|1|2]. Devuelve porcentajes por área, perfil y afinidades.
 */
function calcular_test(array $resp, array $preguntas): array
{
    $suma = array_fill_keys(array_keys(AREAS_TEST), 0);
    $max  = array_fill_keys(array_keys(AREAS_TEST), 0);
    foreach ($preguntas as $n => $p) {
        $suma[$p['area']] += (int)($resp[$n] ?? 0);
        $max[$p['area']]  += 2;
    }
    $pct = [];
    foreach ($suma as $a => $v) {
        $pct[$a] = $max[$a] ? (int)round($v * 100 / $max[$a]) : 0;
    }
    // Perfil = las dos áreas más altas (en empate se respeta el orden RIASEC)
    $orden = $pct;
    uksort($orden, fn($x, $y) => ($pct[$y] <=> $pct[$x]) ?: (strpos('RIASEC', $x) <=> strpos('RIASEC', $y)));
    $perfil = implode('', array_slice(array_keys($orden), 0, 2));

    $afin = [];
    foreach (CARRERAS as $c) {
        $v = 0;
        foreach ($c['riasec'] as $a => $peso) $v += $peso * $pct[$a];
        $afin[$c['id']] = (int)round($v);
    }
    arsort($afin);
    return ['pct' => $pct, 'perfil' => $perfil, 'afin' => $afin, 'recomendada' => (int)array_key_first($afin)];
}

/** Gráfica de radar (telaraña) en SVG con los 6 porcentajes. */
function radar_svg(array $pct): string
{
    $cx = 170; $cy = 165; $R = 115;
    $letras = array_keys(AREAS_TEST);
    $punto = function (int $k, float $r) use ($cx, $cy) {
        $ang = deg2rad(-90 + $k * 60);
        return [round($cx + $r * cos($ang), 1), round($cy + $r * sin($ang), 1)];
    };
    $svg = '<svg class="radar" viewBox="-75 -5 490 340" role="img" aria-label="Gráfica de radar de tus intereses">';
    foreach ([0.25, 0.5, 0.75, 1] as $f) {
        $pts = [];
        for ($k = 0; $k < 6; $k++) $pts[] = implode(',', $punto($k, $R * $f));
        $svg .= '<polygon class="radar-red" points="' . implode(' ', $pts) . '"/>';
    }
    for ($k = 0; $k < 6; $k++) {
        [$x, $y] = $punto($k, $R);
        $svg .= '<line class="radar-eje" x1="' . $cx . '" y1="' . $cy . '" x2="' . $x . '" y2="' . $y . '"/>';
    }
    $pts = [];
    foreach ($letras as $k => $a) $pts[] = implode(',', $punto($k, $R * max(4, $pct[$a]) / 100));
    $svg .= '<polygon class="radar-valor" points="' . implode(' ', $pts) . '"/>';
    foreach ($letras as $k => $a) {
        [$x, $y] = $punto($k, $R * max(4, $pct[$a]) / 100);
        $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="5" fill="' . AREAS_TEST[$a]['color'] . '"/>';
        [$lx, $ly] = $punto($k, $R + 30);
        $anchor = abs($lx - $cx) < 5 ? 'middle' : ($lx > $cx ? 'start' : 'end');
        $svg .= '<text class="radar-txt" x="' . $lx . '" y="' . ($ly + 4) . '" text-anchor="' . $anchor . '">'
              . AREAS_TEST[$a]['emoji'] . ' ' . AREAS_TEST[$a]['nombre'] . ' ' . $pct[$a] . '%</text>';
    }
    return $svg . '</svg>';
}

/** ¿Puede el usuario actual ver este resultado? */
function puede_ver_test(array $fila): bool
{
    if (es_admin()) return true;
    $u = usuario();
    if ($u && (int)$fila['usuario_id'] === $u['id']) return true;
    return in_array((int)$fila['id'], $_SESSION['tests'] ?? [], true);
}
