-- =====================================================================
--  Base de datos: carreras_buap  (PWA unificada)
--  Proyecto Final "Construye Aplicaciones Web" · CBTIS 260 · Grupo B
--  Carreras: Fisioterapia + Mercadotecnia y Medios Digitales + Test vocacional
--
--  Todas las tablas empiezan con  pwa_  para que convivan con las tablas de
--  los proyectos anteriores en la misma base de datos (no borra nada de ellos).
--
--  Tablas:
--    pwa_carreras · pwa_usuarios · pwa_materias · pwa_aspirantes
--    pwa_encuestas · pwa_test_preguntas (60) · pwa_test_resultados
--
--  CÓMO IMPORTAR
--  · XAMPP: phpMyAdmin → "Nueva" → base "carreras_buap" (utf8mb4_unicode_ci) → Importar este archivo.
--  · Clever Cloud: phpMyAdmin de tu add-on → selecciona tu base → Importar este archivo.
--
--  Cuentas de prueba:
--    admin@buap.mx      / admin2026   (administrador)
--    aspirante@buap.mx  / fisio2026   (aspirante)
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pwa_test_resultados;
DROP TABLE IF EXISTS pwa_test_preguntas;
DROP TABLE IF EXISTS pwa_encuestas;
DROP TABLE IF EXISTS pwa_aspirantes;
DROP TABLE IF EXISTS pwa_materias;
DROP TABLE IF EXISTS pwa_usuarios;
DROP TABLE IF EXISTS pwa_carreras;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. CARRERAS
CREATE TABLE pwa_carreras (
  id         TINYINT UNSIGNED NOT NULL,
  slug       VARCHAR(20)  NOT NULL,
  nombre     VARCHAR(120) NOT NULL,
  facultad   VARCHAR(80)  NOT NULL,
  semestres  TINYINT UNSIGNED NOT NULL,
  creditos   SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pwa_carreras_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USUARIOS (una sola cuenta sirve para las dos carreras)
CREATE TABLE pwa_usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(100) NOT NULL,
  correo        VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol           VARCHAR(15)  NOT NULL DEFAULT 'aspirante',
  creado        DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pwa_usuarios_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. MATERIAS (plan de estudios de cada carrera)
CREATE TABLE pwa_materias (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  carrera_id  TINYINT UNSIGNED NOT NULL,
  semestre    TINYINT UNSIGNED NOT NULL,
  clave       VARCHAR(12)  NOT NULL,
  nombre      VARCHAR(120) NOT NULL,
  area        VARCHAR(20)  NOT NULL,
  creditos    TINYINT UNSIGNED NOT NULL DEFAULT 4,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pwa_materias_clave (carrera_id, clave),
  KEY idx_pwa_materias_sem (carrera_id, semestre),
  CONSTRAINT fk_pwa_materias_carrera FOREIGN KEY (carrera_id) REFERENCES pwa_carreras (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ASPIRANTES (fichas de pre-registro · CRUD)
CREATE TABLE pwa_aspirantes (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id    INT UNSIGNED NOT NULL,
  carrera_id    TINYINT UNSIGNED NOT NULL,
  nombre        VARCHAR(100) NOT NULL,
  curp          CHAR(18)     NOT NULL,
  correo        VARCHAR(120) NOT NULL,
  telefono      VARCHAR(15)  NOT NULL,
  fecha_nac     DATE         NOT NULL,
  bachillerato  VARCHAR(120) NOT NULL,
  promedio      DECIMAL(4,2) NOT NULL,
  sede          VARCHAR(30)  NOT NULL,
  turno         VARCHAR(12)  NOT NULL DEFAULT 'Matutino',
  medio         VARCHAR(30)  NOT NULL DEFAULT 'Redes sociales',
  estado        VARCHAR(15)  NOT NULL DEFAULT 'Pendiente',
  creado        DATETIME     NOT NULL,
  actualizado   DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_pwa_aspirantes_usuario (usuario_id),
  KEY idx_pwa_aspirantes_carrera (carrera_id),
  CONSTRAINT fk_pwa_aspirantes_usuario FOREIGN KEY (usuario_id) REFERENCES pwa_usuarios (id) ON DELETE CASCADE,
  CONSTRAINT fk_pwa_aspirantes_carrera FOREIGN KEY (carrera_id) REFERENCES pwa_carreras (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ENCUESTAS de satisfacción (10 preguntas × 3 opciones)
CREATE TABLE pwa_encuestas (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  carrera_id  TINYINT UNSIGNED NULL,
  nombre      VARCHAR(100) NOT NULL DEFAULT 'Anónimo',
  p1 TINYINT UNSIGNED NOT NULL, p2 TINYINT UNSIGNED NOT NULL, p3 TINYINT UNSIGNED NOT NULL,
  p4 TINYINT UNSIGNED NOT NULL, p5 TINYINT UNSIGNED NOT NULL, p6 TINYINT UNSIGNED NOT NULL,
  p7 TINYINT UNSIGNED NOT NULL, p8 TINYINT UNSIGNED NOT NULL, p9 TINYINT UNSIGNED NOT NULL,
  p10 TINYINT UNSIGNED NOT NULL,
  comentario  VARCHAR(500) NULL,
  creado      DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. PREGUNTAS DEL TEST VOCACIONAL (modelo RIASEC de Holland, 10 por área)
--    area: R Realista · I Investigador · A Artístico · S Social · E Emprendedor · C Convencional
CREATE TABLE pwa_test_preguntas (
  numero  TINYINT UNSIGNED NOT NULL,
  area    CHAR(1)      NOT NULL,
  texto   VARCHAR(200) NOT NULL,
  PRIMARY KEY (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. RESULTADOS DEL TEST
--    respuestas: 60 dígitos (2 = me interesa, 1 = tal vez, 0 = no me interesa)
--    r..c: porcentaje (0-100) en cada área · afin_*: afinidad con cada carrera (0-100)
CREATE TABLE pwa_test_resultados (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id    INT UNSIGNED NULL,
  nombre        VARCHAR(100) NOT NULL,
  edad          TINYINT UNSIGNED NULL,
  respuestas    CHAR(60)     NOT NULL,
  r TINYINT UNSIGNED NOT NULL, i TINYINT UNSIGNED NOT NULL, a TINYINT UNSIGNED NOT NULL,
  s TINYINT UNSIGNED NOT NULL, e TINYINT UNSIGNED NOT NULL, c TINYINT UNSIGNED NOT NULL,
  perfil        CHAR(2)      NOT NULL,
  afin_fisio    TINYINT UNSIGNED NOT NULL,
  afin_mkt      TINYINT UNSIGNED NOT NULL,
  recomendada   TINYINT UNSIGNED NOT NULL,
  creado        DATETIME     NOT NULL,
  PRIMARY KEY (id),
  KEY idx_pwa_test_usuario (usuario_id),
  CONSTRAINT fk_pwa_test_usuario FOREIGN KEY (usuario_id) REFERENCES pwa_usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  DATOS INICIALES
-- =====================================================================
INSERT INTO pwa_carreras (id, slug, nombre, facultad, semestres, creditos) VALUES
(1, 'fisioterapia', 'Licenciatura en Fisioterapia', 'Facultad de Medicina', 8, 342),
(2, 'marketing', 'Licenciatura en Mercadotecnia y Medios Digitales', 'Facultad de Ciencias de la Comunicación', 9, 234);

INSERT INTO pwa_usuarios (nombre, correo, password_hash, rol, creado) VALUES
('Coordinación de Admisiones', 'admin@buap.mx', '$2y$10$a0b79Xpz1oR6D7Q3L0szR.TVkHUnlSG9e4JE6H8YUufIKj5P8mO2G', 'admin', '2026-11-10 09:00:00'),
('Valeria Hernández López', 'aspirante@buap.mx', '$2y$10$ADTEUBo6WojafUq3E5hlR.cm2vV5LYfB7DkknybN1GMZ2rn/GTkym', 'aspirante', '2026-11-11 10:30:00');

-- Fisioterapia: 48 materias · 342 créditos
INSERT INTO pwa_materias (carrera_id, semestre, clave, nombre, area, creditos) VALUES
(1, 1, 'FIS-101', 'Anatomía humana I', 'Básica', 8),
(1, 1, 'FIS-102', 'Fisiología humana', 'Básica', 8),
(1, 1, 'FIS-103', 'Bioquímica', 'Básica', 7),
(1, 1, 'FIS-104', 'Biología celular e histología', 'Básica', 7),
(1, 1, 'FIS-105', 'Introducción a la fisioterapia', 'Formativa', 6),
(1, 1, 'FIS-106', 'Formación humana y social', 'Formativa', 6),
(1, 2, 'FIS-201', 'Anatomía humana II (aparato locomotor)', 'Básica', 8),
(1, 2, 'FIS-202', 'Fisiología del ejercicio', 'Básica', 8),
(1, 2, 'FIS-203', 'Biomecánica', 'Básica', 8),
(1, 2, 'FIS-204', 'Física aplicada a la fisioterapia', 'Básica', 6),
(1, 2, 'FIS-205', 'Psicología de la salud', 'Formativa', 6),
(1, 2, 'FIS-206', 'Lengua extranjera I', 'Formativa', 4),
(1, 3, 'FIS-301', 'Kinesiología', 'Formativa', 8),
(1, 3, 'FIS-302', 'Agentes físicos I (termo y electroterapia)', 'Formativa', 8),
(1, 3, 'FIS-303', 'Valoración fisioterapéutica', 'Formativa', 8),
(1, 3, 'FIS-304', 'Patología general', 'Básica', 7),
(1, 3, 'FIS-305', 'Bioestadística', 'Básica', 6),
(1, 3, 'FIS-306', 'Lengua extranjera II', 'Formativa', 4),
(1, 4, 'FIS-401', 'Terapia manual', 'Profesional', 8),
(1, 4, 'FIS-402', 'Ejercicio terapéutico', 'Profesional', 8),
(1, 4, 'FIS-403', 'Agentes físicos II', 'Formativa', 7),
(1, 4, 'FIS-404', 'Farmacología aplicada', 'Básica', 6),
(1, 4, 'FIS-405', 'Metodología de la investigación', 'Formativa', 6),
(1, 4, 'FIS-406', 'Desarrollo de habilidades digitales', 'Formativa', 5),
(1, 5, 'FIS-501', 'Fisioterapia en ortopedia y traumatología', 'Profesional', 9),
(1, 5, 'FIS-502', 'Fisioterapia cardiorrespiratoria', 'Profesional', 8),
(1, 5, 'FIS-503', 'Imagenología para fisioterapeutas', 'Formativa', 6),
(1, 5, 'FIS-504', 'Ergonomía y salud ocupacional', 'Profesional', 7),
(1, 5, 'FIS-505', 'Bioética y deontología', 'Formativa', 5),
(1, 5, 'FIS-506', 'Prácticas clínicas I', 'Integración', 8),
(1, 6, 'FIS-601', 'Fisioterapia neurológica', 'Profesional', 9),
(1, 6, 'FIS-602', 'Fisioterapia deportiva', 'Profesional', 8),
(1, 6, 'FIS-603', 'Hidroterapia', 'Formativa', 7),
(1, 6, 'FIS-604', 'Fisioterapia pediátrica', 'Profesional', 8),
(1, 6, 'FIS-605', 'Salud pública y epidemiología', 'Formativa', 6),
(1, 6, 'FIS-606', 'Prácticas clínicas II', 'Integración', 8),
(1, 7, 'FIS-701', 'Fisioterapia geriátrica', 'Profesional', 8),
(1, 7, 'FIS-702', 'Fisioterapia en ginecología y obstetricia', 'Profesional', 7),
(1, 7, 'FIS-703', 'Prótesis, órtesis y ayudas técnicas', 'Profesional', 6),
(1, 7, 'FIS-704', 'Telerehabilitación', 'Profesional', 6),
(1, 7, 'FIS-705', 'Administración de servicios de fisioterapia', 'Formativa', 7),
(1, 7, 'FIS-706', 'Prácticas clínicas III', 'Integración', 9),
(1, 8, 'FIS-801', 'Práctica clínica integral', 'Integración', 12),
(1, 8, 'FIS-802', 'Seminario de tesis', 'Integración', 8),
(1, 8, 'FIS-803', 'Fisioterapia comunitaria', 'Profesional', 7),
(1, 8, 'FIS-804', 'Optativa disciplinaria', 'Profesional', 7),
(1, 8, 'FIS-805', 'Emprendimiento en salud', 'Formativa', 5),
(1, 8, 'FIS-806', 'Práctica profesional', 'Integración', 8);

-- Mercadotecnia y Medios Digitales: 49 materias · 234 créditos
INSERT INTO pwa_materias (carrera_id, semestre, clave, nombre, area, creditos) VALUES
(2, 1, 'LMDS-001', 'Teorías de la Comunicación', 'Comunicación', 6),
(2, 1, 'LMDS-002', 'Introducción a la Administración', 'Mercadotecnia', 4),
(2, 1, 'LMDS-003', 'Fundamentos de Mercadotecnia', 'Mercadotecnia', 4),
(2, 1, 'LMDS-004', 'Introducción a Medios Digitales', 'Medios digitales', 4),
(2, 1, 'FGUS-001', 'Formación Humana y Social', 'Formación general', 4),
(2, 1, 'FGUS-004', 'Lengua Extranjera I', 'Formación general', 4),
(2, 2, 'LMDS-005', 'Teoría de Medios', 'Comunicación', 4),
(2, 2, 'LMDS-006', 'Fundamentos de Contabilidad y Finanzas', 'Mercadotecnia', 4),
(2, 2, 'LMDS-007', 'Redes Sociales Digitales', 'Medios digitales', 4),
(2, 2, 'LMDS-008', 'Comportamiento del Consumidor', 'Mercadotecnia', 4),
(2, 2, 'FGUS-002', 'Desarrollo de Habilidades del Pensamiento Complejo', 'Formación general', 4),
(2, 2, 'FGUS-005', 'Lengua Extranjera II', 'Formación general', 4),
(2, 3, 'LMDS-010', 'Comunicación Visual', 'Comunicación', 4),
(2, 3, 'LMDS-011', 'Estadística', 'Formación general', 4),
(2, 3, 'LMDS-012', 'Comunicación Verbal', 'Comunicación', 4),
(2, 3, 'FGUS-003', 'Desarrollo de Habilidades en el Uso de la Tecnología', 'Formación general', 4),
(2, 3, 'LMDS-013', 'Posicionamiento Web y Publicidad', 'Medios digitales', 4),
(2, 3, 'FGUS-006', 'Lengua Extranjera III', 'Formación general', 4),
(2, 4, 'LMDS-016', 'Investigación de Mercados', 'Mercadotecnia', 6),
(2, 4, 'LMDS-017', 'Negocios Digitales', 'Medios digitales', 4),
(2, 4, 'FGUS-007', 'Lengua Extranjera IV', 'Formación general', 4),
(2, 4, 'LMDS-014', 'Diseño Web y Experiencia de Usuario', 'Medios digitales', 4),
(2, 4, 'LMDS-015', 'Producción Audiovisual', 'Comunicación', 4),
(2, 4, 'LMDS-254', 'Introducción a la Normatividad', 'Comunicación', 4),
(2, 5, 'LMDS-250', 'Gestión de la Imagen', 'Comunicación', 6),
(2, 5, 'LMDS-253', 'Administración Estratégica', 'Mercadotecnia', 4),
(2, 5, 'LMDS-256', 'Dirección de Mercadotecnia', 'Mercadotecnia', 6),
(2, 5, 'LMDS-019', 'Tendencias e Innovación Digital', 'Medios digitales', 4),
(2, 5, 'LMDS-260', 'Responsabilidad Social', 'Integración', 4),
(2, 5, 'LMDS-018', 'Analítica Web', 'Medios digitales', 4),
(2, 6, 'LMDS-257', 'Ámbitos de la Mercadotecnia', 'Mercadotecnia', 6),
(2, 6, 'LMDS-252', 'Comunicación Integral de Mercadotecnia', 'Mercadotecnia', 6),
(2, 6, 'LMDS-251', 'Diseño y Producción Publicitaria', 'Comunicación', 6),
(2, 6, 'LMDS-020', 'Comercio Electrónico', 'Medios digitales', 4),
(2, 6, 'OPC-001', 'Optativa Complementaria I', 'Integración', 4),
(2, 7, 'LMDS-259', 'Creación de Campañas Digitales', 'Medios digitales', 7),
(2, 7, 'LMDS-261', 'Globalización y Digitalidad I', 'Integración', 6),
(2, 7, 'LMDS-255', 'Normatividad Digital', 'Comunicación', 4),
(2, 7, 'OPD-001', 'Optativa Disciplinaria I', 'Integración', 4),
(2, 7, 'OPC-002', 'Optativa Complementaria II', 'Integración', 4),
(2, 8, 'LMDS-258', 'Mercadotecnia Digital', 'Mercadotecnia', 7),
(2, 8, 'LMDS-262', 'Globalización y Digitalidad II', 'Integración', 6),
(2, 8, 'LMDS-263', 'Inglés Técnico', 'Formación general', 3),
(2, 8, 'OPD-002', 'Optativa Disciplinaria II', 'Integración', 4),
(2, 8, 'LMDS-021', 'Emprendimiento Digital', 'Mercadotecnia', 4),
(2, 9, 'ISMM-200', 'Proyecto Recepcional en Mercadotecnia Digital I', 'Integración', 10),
(2, 9, 'ISMM-201', 'Proyecto Recepcional en Mercadotecnia Digital II', 'Integración', 10),
(2, 9, 'OPD-003', 'Optativa Disciplinaria III', 'Integración', 4),
(2, 9, 'PP-001', 'Práctica Profesional', 'Integración', 7);

INSERT INTO pwa_aspirantes (usuario_id, carrera_id, nombre, curp, correo, telefono, fecha_nac, bachillerato, promedio, sede, turno, medio, estado, creado) VALUES
(2, 1, 'Valeria Hernández López', 'HELV080315MPLRPLA4', 'aspirante@buap.mx', '2221234567', '2008-03-15', 'CBTIS 260', 9.10, 'Puebla (CU)', 'Matutino', 'Redes sociales', 'En revisión', '2026-11-12 11:15:00');

INSERT INTO pwa_encuestas (carrera_id, nombre, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, comentario, creado) VALUES
(1, 'Luis', 1, 1, 1, 2, 1, 1, 1, 2, 1, 1, 'Me gustó mucho el video.', '2026-11-12 12:00:00'),
(2, 'Sofía', 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 'La calculadora de ROI está muy padre.', '2026-11-12 13:00:00'),
(NULL, 'Anónimo', 2, 1, 2, 1, 1, 1, 2, 1, 2, 1, NULL, '2026-11-13 08:15:00');

-- 60 preguntas del test vocacional
INSERT INTO pwa_test_preguntas (numero, area, texto) VALUES
(1, 'R', 'Armar o reparar cosas con mis manos.'),
(2, 'I', 'Investigar por qué suceden las cosas.'),
(3, 'A', 'Dibujar, diseñar o hacer manualidades creativas.'),
(4, 'S', 'Ayudar a las personas cuando tienen un problema.'),
(5, 'E', 'Vender productos o convencer a otros de una idea.'),
(6, 'C', 'Organizar información en tablas u hojas de cálculo.'),
(7, 'I', 'Estudiar biología, química o anatomía.'),
(8, 'A', 'Tomar fotografías o grabar y editar videos.'),
(9, 'S', 'Explicarle a un compañero un tema que no entiende.'),
(10, 'E', 'Liderar un equipo o un proyecto escolar.'),
(11, 'C', 'Llevar el control de gastos, inventarios o registros.'),
(12, 'R', 'Hacer deporte o actividad física con regularidad.'),
(13, 'A', 'Escribir historias, guiones o frases ingeniosas.'),
(14, 'S', 'Cuidar a niños, adultos mayores o personas enfermas.'),
(15, 'E', 'Tener mi propio negocio algún día.'),
(16, 'C', 'Seguir procedimientos ordenados y bien definidos.'),
(17, 'R', 'Usar herramientas, aparatos o equipo especializado.'),
(18, 'I', 'Ver documentales o leer artículos de ciencia por gusto.'),
(19, 'S', 'Escuchar con paciencia a otras personas.'),
(20, 'E', 'Negociar para conseguir un buen trato.'),
(21, 'C', 'Revisar documentos para encontrar errores.'),
(22, 'R', 'Trabajar en movimiento y no sentado todo el día.'),
(23, 'I', 'Resolver acertijos de lógica o problemas de matemáticas.'),
(24, 'A', 'Inventar ideas originales para un proyecto.'),
(25, 'E', 'Presentar una propuesta frente a un grupo.'),
(26, 'C', 'Planear mi semana con agenda o calendario.'),
(27, 'R', 'Entender cómo se mueve el cuerpo al correr, saltar o levantar algo.'),
(28, 'I', 'Analizar datos o gráficas para sacar conclusiones.'),
(29, 'A', 'Elegir colores, tipografías y estilos para que algo se vea bien.'),
(30, 'S', 'Participar en voluntariados o causas sociales.'),
(31, 'C', 'Archivar y clasificar cosas para encontrarlas rápido.'),
(32, 'R', 'Ayudar a alguien a estirarse o a moverse mejor después de una lesión.'),
(33, 'I', 'Hacer experimentos para comprobar si una idea es cierta.'),
(34, 'A', 'Actuar, cantar, bailar o presentarme frente a un público.'),
(35, 'S', 'Trabajar en equipo más que yo solo.'),
(36, 'E', 'Organizar eventos, rifas o campañas.'),
(37, 'R', 'Seguir instrucciones técnicas paso a paso para lograr un resultado concreto.'),
(38, 'I', 'Saber cómo funcionan por dentro los músculos, los huesos y los nervios.'),
(39, 'A', 'Crear contenido para redes sociales.'),
(40, 'S', 'Motivar a alguien para que logre su meta.'),
(41, 'E', 'Ponerme metas ambiciosas y competir para lograrlas.'),
(42, 'C', 'Trabajar con números exactos y fechas límite.'),
(43, 'I', 'Buscar información en varias fuentes antes de tomar una decisión.'),
(44, 'A', 'Mejorar cómo se ve un espacio, un producto o una presentación.'),
(45, 'S', 'Trabajar en un hospital, una clínica o un centro comunitario.'),
(46, 'E', 'Estar al tanto de las tendencias y de lo que quiere la gente.'),
(47, 'C', 'Usar programas de oficina como Word, Excel o formularios.'),
(48, 'R', 'Practicar una técnica hasta dominarla (un deporte, un instrumento, un oficio).'),
(49, 'A', 'Expresar lo que siento por medio del arte o la música.'),
(50, 'S', 'Conocer gente nueva y platicar con ella.'),
(51, 'E', 'Tomar decisiones rápidas aunque haya cierto riesgo.'),
(52, 'C', 'Cumplir reglas y normas al pie de la letra.'),
(53, 'R', 'Construir maquetas, prototipos o proyectos manuales.'),
(54, 'I', 'Entender cómo funciona una app o un algoritmo.'),
(55, 'S', 'Sentir que mi trabajo mejora la vida de otras personas.'),
(56, 'E', 'Promocionar una marca, un evento o una causa.'),
(57, 'C', 'Medir resultados y llevar estadísticas.'),
(58, 'R', 'Resolver en el momento un problema práctico, como algo que se descompone.'),
(59, 'I', 'Hacer preguntas hasta entender un tema a fondo.'),
(60, 'A', 'Trabajar con libertad creativa y sin reglas tan fijas.');
