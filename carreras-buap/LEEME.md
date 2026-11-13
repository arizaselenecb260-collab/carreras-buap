# Carreras BUAP · PWA unificada (PHP + MySQL)

Proyecto Final **Construye Aplicaciones Web** · CBTIS 260 · Grupo “B”

Una sola aplicación con **dos carreras** y un **test vocacional de 60 preguntas**:

- 🩺 Licenciatura en Fisioterapia (Facultad de Medicina)
- 📣 Licenciatura en Mercadotecnia y Medios Digitales (Facultad de Ciencias de la Comunicación)
- 🧭 Test vocacional basado en el modelo RIASEC de Holland

Cada carrera tiene sus propios colores, y una sola cuenta sirve para todo.

## Páginas

| Archivo | Qué hace |
|---|---|
| `index.php` | Portal: las dos carreras, comparación, acceso al test, videos |
| `carrera.php?c=fisioterapia` / `?c=marketing` | Inicio de cada carrera (misión, visión, galería, video y spot) |
| `plan.php?c=…` | **Página 1 · Plan de estudios** (materias desde la BD, pestañas por semestre, buscador, gráfica, perfil de ingreso) |
| `campo.php?c=…` | **Página 2 · Perfil de egreso y campo laboral** (mapa corporal en Fisioterapia; embudo y calculadora de ROI en Marketing) |
| `registro.php` | **Página 3 · Registro e inscripciones** + **CRUD de fichas** (eliges la carrera) |
| `test.php` | **Test vocacional de 60 preguntas**, en 6 bloques, guarda avance si cierras la página |
| `test_resultado.php?id=…` | Resultado: gráfica de radar, afinidad con cada carrera y carreras afines |
| `encuesta.php` | Encuesta de satisfacción: 10 preguntas × 3 opciones, con resultados en vivo |
| `login.php` · `crear_cuenta.php` · `logout.php` | Inicio de sesión (contraseñas cifradas) |
| `materias.php` | **CRUD de materias** de cada carrera (solo administrador) |
| `panel.php` · `exportar.php` | Panel con estadísticas de fichas, tests y servidor; exporta fichas y tests a CSV |
| `includes/carreras.php` | Datos fijos de cada carrera (colores, sedes, áreas) |
| `includes/test.php` | Las 6 áreas RIASEC y el cálculo del resultado |
| `contenido/fisioterapia/` · `contenido/marketing/` | Textos propios de cada carrera |
| `database/carreras_buap.sql` | **Base de datos nueva** para importar en phpMyAdmin |

## Base de datos (prefijo `pwa_`)

| Tabla | Contenido |
|---|---|
| `pwa_carreras` | Las 2 carreras |
| `pwa_usuarios` | Cuentas (aspirantes y administrador) |
| `pwa_materias` | 48 materias de Fisioterapia + 49 de Marketing |
| `pwa_aspirantes` | Fichas de pre-registro (con la carrera elegida) |
| `pwa_encuestas` | Respuestas de la encuesta de satisfacción |
| `pwa_test_preguntas` | Las 60 preguntas del test (10 por área) |
| `pwa_test_resultados` | Cada test contestado: respuestas, % por área, perfil y afinidad con cada carrera |

El prefijo `pwa_` permite importar esta base en la **misma** base de datos que ya usas, sin tocar las tablas de los proyectos anteriores.

## ¿Cómo calcula el test?

1. Cada respuesta vale 2 (👍 Me interesa), 1 (🤔 Tal vez) o 0 (👎 No me interesa).
2. Se suma por área (R, I, A, S, E, C) y se convierte a porcentaje.
3. El **perfil** son las 2 áreas más altas (ej. «SR» = Social–Realista).
4. **Afinidad** con cada carrera = promedio ponderado de sus áreas:
   - Fisioterapia = 40 % Social + 35 % Realista + 25 % Investigador
   - Marketing = 35 % Emprendedor + 30 % Artístico + 20 % Social + 15 % Convencional

## Cuentas de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Aspirante | `aspirante@buap.mx` | `fisio2026` |
| Administrador | `admin@buap.mx` | `admin2026` |

## XAMPP (resumen)
1. Copia `carreras-buap` a `C:\xampp\htdocs\`.
2. phpMyAdmin → **Nueva** → `carreras_buap` → **Importar** `database/carreras_buap.sql`.
3. Abre `http://localhost/carreras-buap/`.

Pasos para la nube: `../3_Documentacion/GUIA_CLEVER_CLOUD.md`.
