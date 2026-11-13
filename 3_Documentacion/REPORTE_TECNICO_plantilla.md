# Reporte técnico · Proyecto Final «Construye Aplicaciones Web»

**CBTIS 260 · Grupo “B”**
**Integrantes:** ______________________________________________
**Carreras:** Fisioterapia y Mercadotecnia y Medios Digitales · BUAP (PWA unificada con test vocacional)
**URL pública del sitio:** https://______________________________

---

## 1. Requerimientos y prototipo
- Carreras: Fisioterapia (Facultad de Medicina, 8 semestres, 342 créditos) y Mercadotecnia y Medios Digitales (Facultad de Ciencias de la Comunicación, 9 semestres, 234 créditos), más un test vocacional de 60 preguntas.
- Pantallas: Portal · Inicio de cada carrera · Plan de Estudios · Egreso y Campo Laboral · Registro e Inscripciones · Test vocacional y resultado · Encuesta · Inicio de sesión · Panel del administrador.
- *(Pegar aquí la foto del boceto en papel o digital.)*

## 2. Identidad visual
- Logotipos hechos en Paint: `assets/img/logos/fisio.png` y `assets/img/logos/mkt.png`; ícono de la app: `assets/img/logos/portal.svg`. *(Pegar imágenes.)*
- Paleta: portal azul `#0F3460`; Fisioterapia turquesa `#00A896`; Marketing naranja `#F26B21`; dorado `#F5B731`.
- Misión y visión: ver la página de Inicio.

## 3. Servidor local (XAMPP)
📸 Captura: XAMPP Control Panel con Apache y MySQL en verde.

## 4. Páginas, menús, listas, tablas y multimedia
| Requisito | Dónde se cumple |
|---|---|
| 3 páginas temáticas | `plan.php?c=…`, `campo.php?c=…`, `registro.php` (para cada carrera) |
| Menús desplegables | Barra de navegación (Plan de Estudios, Egreso y Campo Laboral, Registro e Inscripciones) |
| Listas ordenadas / desordenadas | Ruta de ingreso y requisitos (`<ol>`), perfil de ingreso y compromiso social (`<ul>`) |
| Tablas | Materias por semestre, áreas de formación, costos, campo laboral |
| Enlaces a sonido y video | Spot MP3 y video MP4 (Inicio, Plan de estudios y pie de página) |
| Imágenes con enlace a redes oficiales | Íconos de Facebook, Instagram, X, YouTube, TikTok y sitio BUAP en el pie |

📸 Capturas de cada página.

## 5. Formularios y autenticación
- Inicio de sesión y registro de cuenta (contraseñas cifradas con `password_hash`).
- Formulario de satisfacción: 10 preguntas × 3 opciones (`encuesta.php`).
- Apartado de campo laboral y compromiso social (INEGI Censo 2020 y ENDUTIH 2024).
- **Test vocacional de 60 preguntas** (modelo RIASEC) guardado en `pwa_test_resultados`.

📸 Capturas: login, ficha registrada, encuesta y resultados.

## 6. Base de datos y CRUD
Tablas: `pwa_carreras`, `pwa_usuarios`, `pwa_materias`, `pwa_aspirantes`, `pwa_encuestas`, `pwa_test_preguntas`, `pwa_test_resultados`.

| Operación | Captura |
|---|---|
| Insertar ficha / materia | 📸 |
| Consultar (lista con buscador) | 📸 |
| Actualizar | 📸 |
| Eliminar | 📸 |

## 7. Servidor en la nube
- Plataforma: Clever Cloud · Sistema: Linux · Servidor web: Apache · Intérprete: PHP · BD: MySQL/MariaDB.
- Seguridad: certificado SSL (HTTPS), contraseñas cifradas, consultas preparadas (anti inyección SQL), token CSRF, carpetas `includes/` y `database/` bloqueadas con `.htaccess`.

📸 Captura: app en Clever Cloud desplegada y su dominio (*Domain names*).
📸 Captura: el sitio con el candado 🔒 (HTTPS incluido en cleverapps.io).

## 8. Migración de la base de datos
📸 Captura: exportación en phpMyAdmin local (archivo `.sql`).
📸 Captura: importación en phpMyAdmin de la nube con las 4 tablas.

## 9. Prueba final
📸 Captura: **Panel de control → Servidor y base de datos** mostrando «Conectado», host remoto, dominio y HTTPS.
📸 Captura: sitio abierto desde el celular / app instalada.

## Conclusiones
_____________________________________________________________________
