# Guía paso a paso · Subir la PWA unificada a Clever Cloud

Es igual a lo que ya hiciste, y vas a usar tu misma base de datos **selene**.
Las tablas nuevas empiezan con `pwa_`, así que **no borran ni tocan** las de Fisioterapia (`usuarios`, `materias`…) ni las de Marketing (`mkt_…`).

> ⚠️ **No borres nada en phpMyAdmin.** Solo vas a importar.

---

## Paso 1 · Sube el proyecto a GitHub
1. GitHub → botón **+** → **New repository** → nombre `carreras-buap` → **Public** → **Create repository**.
2. Clic en **uploading an existing file**.
3. En tu Mac abre la carpeta **Proyecto_Carreras_BUAP** (la que sale al descomprimir el zip).
4. Presiona **Cmd + Shift + .** (para ver archivos ocultos) y luego **Cmd + A**.
5. Arrastra todo al recuadro de GitHub y espera a que termine la lista.
6. Clic en **Commit changes**.

## Paso 2 · Importa la base de datos nueva
1. Clever Cloud → menú izquierdo → **selene** → **PHPMyAdmin**.
2. Clic en tu base **bsn3dhythr3xoydiboz7** (columna izquierda).
3. Pestaña **Importar** → **Seleccionar archivo** → `carreras-buap/database/carreras_buap.sql` → **Importar**.
4. Deben aparecer 7 tablas nuevas que empiezan con **pwa_** (entre ellas `pwa_test_preguntas` con 60 filas).

## Paso 3 · Crea la app
1. **+ Create** → **an application** → pestaña **GitHub** → elige **carreras-buap**.
2. Tipo **PHP** → tamaño más pequeño (**nano** / **XS**) → **Next**.
3. Nombre: `carreras-buap` → **Next**.
4. Add-ons: **I don't need any add-ons**.

## Paso 4 · Variable CC_WEBROOT
1. Pestaña **Environment variables**.
2. Variable name: `CC_WEBROOT` · Variable value: `/carreras-buap` → **ADD**.
3. Botón verde **UPDATE CHANGES**.

## Paso 5 · Conecta la base de datos
1. Pestaña **Service dependencies** → **Link add-ons**.
2. Casilla **Add-ons ▼** → **selene** → **ADD**.

## Paso 6 · Enciende la app
1. Pestaña **Overview** → **RE-BUILD AND START**.
2. Pestaña **Logs** → espera **Successfully deployed** (en verde).

## Paso 7 · Prueba y capturas para el reporte
1. Pestaña **Domain names** → abre tu dirección `...cleverapps.io`.
2. Haz el **Test vocacional** completo → toma captura del resultado.
3. **Iniciar sesión** → **🛠️ Administrador** → **Panel de control** → captura del recuadro **Servidor y base de datos** (debe decir **Conectado**).
4. En phpMyAdmin abre `pwa_test_resultados` → **Examinar** → captura: ahí se ve tu test guardado.

## Si algo sale mal
| Lo que ves | Qué hacer |
|---|---|
| «No se pudo conectar a MySQL» | Falta el Paso 5 o hacer **RE-BUILD AND START** después. |
| Error 404 / página en blanco | Revisa que `CC_WEBROOT` sea exactamente `/carreras-buap`. |
| «Table ... pwa_... doesn't exist» | Falta importar `carreras_buap.sql` (Paso 2). |
