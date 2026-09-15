# Resumen de Cambios — RentWheels (rama `main-front-end`)

Documento de estudio con todo lo que se ha hecho en las rondas de mejoras del frontend.
Plan: volver acá cuando preguntes "¿cómo hiciste esto?".

---

## Índice
1. [Contexto y reglas de trabajo](#1-contexto-y-reglas-de-trabajo)
2. [Sistema compartido de notificaciones (JS)](#2-sistema-compartido-de-notificaciones-js)
3. [Ronda 1 — Tickets, toasts, panel y reportes (v0.3.1)](#3-ronda-1--tickets-toasts-panel-y-reportes-v031)
4. [Ronda 2 — Filtros, colores y reportes con datos reales (v0.3.2)](#4-ronda-2--filtros-colores-y-reportes-con-datos-reales-v032)
5. [Ronda 3 (complementos) — Mensajes de login/register como toasts](#5-ronda-3-complementos--mensajes-de-loginregister-como-toasts)
6. [Ronda 4 — Ingreso por rol, panel, flota y verificación (v0.3.3)](#6-ronda-4--ingreso-por-rol-panel-flota-y-verificación-v033)
7. [Ronda 5 (complementos) — URL en modal, recuperación de contraseña y saludo](#7-ronda-5-complementos--url-en-modal-recuperación-de-contraseña-y-saludo)
8. [Ronda 6 — Galería del inicio desde la BD y buscador mejorado](#8-ronda-6--galería-del-inicio-desde-la-bd-y-buscador-mejorado)
9. [Cómo verificar que todo sigue funcionando](#9-cómo-verificar-que-todo-sigue-funcionando)
10. [Errores típicos y trucos al trabajar este proyecto](#10-errores-típicos-y-trucos-al-trabajar-este-proyecto)

---

## 1. Contexto y reglas de trabajo

- El frontend usa el tema **rojo `#C61A30`** y **antracita `#141414`** (variables `--rw-red`, `--rw-ink`, etc.).
- Trabajamos en la rama **`main-front-end`** y **no se hace commit**: los cambios quedan "volando" en el working tree hasta que el usuario decida.
- Stack: XAMPP / PHP 8.2 + **PDO**, **jQuery 3.6.0**, **Chart.js** (CDN), base de datos **`rentwheels_db`** (`includes/config.php`).
- Cada vista se incluye desde `dashboard.php` (un switch por rol + `?view=...`). El sidebar se arma en `dashboard.php`, no en cada vista.
- `includes/config.php` llama a `session_start()` en la línea 3 (de ahí un notice inofensivo al probar por CLI).

### Roles y valores de BD importantes
- Rol en `$_SESSION['rol']` con valores: `turista`, `compania`, `administrador`, `soporte`.
- Estados reales de **tickets** en BD: `abierto`, `respondido`, `cerrado` (NO existe "en proceso").
- Estados de **reservas**: `pendiente`, `confirmada`, `cancelada`.
- Columna `verificado` (0/1) en `usuarios` — la usamos para el check azul y para ocultar "Verificar cuenta".

### Enrutado actual de `redirigirSegunRol()` (includes/auth.php)
| Rol | Destino al iniciar sesión |
|---|---|
| turista | `dashboard.php?view=panel` (Mi panel) |
| compania | `dashboard.php?view=panel` (Mi panel) |
| administrador | `dashboard.php?view=dashboard` (Dashboard) |
| soporte | `dashboard.php?view=tickets` |

---

## 2. Sistema compartido de notificaciones (JS)

Vive en **`js/modals.js`** y se carga en todas las páginas (`<script src="js/modals.js"></script>` al final del body).

- `showAlert(mensaje, tipo, titulo)` → alias de `showToast`.
- `showToast(mensaje, tipo, titulo)` → **toast en la esquina inferior derecha**, auto-cierra a los 5 s.
    - Tipos: `success` (verde), `error` (rojo), `warning` (ámbar), `info` (rojo del tema).
    - El mensaje se pinta con `textContent` (¡por eso es seguro: no acepta HTML!).
    - **Antiapilado por mensaje igual**: si se repite el mismo texto + tipo, el toast existente se **reinicia** (se limpia el temporizador, se reinicia la barra de progreso y vuelve a los 5 s) en vez de duplicarse — p. ej. pulsar "Reservar" N veces no apila N toasts iguales.
    - Cómo funciona: el contenedor guarda un `Map` (`container.__rwToasts`) con clave `tipo + '|' + mensaje`. Si la clave existe y su toast aún está conectado al DOM, se reutiliza; si no, se crea uno nuevo. Mensajes **distintos sí se apilan** (clave distinta). Cuando un toast se cierra se elimina del Map.
    - Verificado con un mock DOM en Node (mismo mensaje x5 → 1 toast; + mensaje distinto → 2; repetir el primero → sigue 2; mismo texto pero distinto tipo → 3).
- `showConfirm(mensaje, onConfirm, onCancel)` → **modal centrado** con "Sí"/"No" (reemplaza al `confirm()` nativo).
- `showModal(options)` → modal genérico: `{ message, type, title, onConfirm, onCancel, confirmText, cancelText }`.
- `handleAjaxError(xhr)` → muestra el `error`/`message` del JSON de respuesta como toast de error.

### Patrón "toast de mensaje al cargar la página" (usado en login/register/recuperar/restablecer)
PHP construye un arreglo y lo serializa con `json_encode`; JS lo recorre y llama `showAlert`:

```php
$toasts = [];
if (isset($_GET['error'])) $toasts[] = ['t' => 'error', 'm' => 'Credenciales inválidas.'];
?>
<script>
    (function() {
        var toasts = <?php echo json_encode($toasts, JSON_UNESCAPED_UNICODE); ?>;
        toasts.forEach(function(t) { showAlert(t.m, t.t); });
    })();
</script>
<script src="js/modals.js"></script>
```

Por qué `json_encode`: escapa comillas/acentos y evita inyectar HTML; por qué `textContent`: XSS-safe incluso si el texto viene de la URL.

---

## 3. Ronda 1 — Tickets, toasts, panel y reportes (v0.3.1)

### Tickets con "forma de ticket"
- Archivos: `views/admin/tickets.php`, `views/soporte/tickets.php`, `views/turista/soporte.php`.
- Cada ticket es una tarjeta con **franja superior de color según estado** (`colorEstado()`):

| Estado | Color |
|---|---|
| `abierto` | `#f59e0b` (amarillo / pendiente) |
| `respondido` | `#10b981` (verde) |
| `cerrado` | `#6b7280` (gris) |

- Botones de acción con `.btn-small`, texto **blanco** (gracias a `css/style-global.css` → `.btn-small { color: var(--rw-white) }`):
  - **Responder** = verde `#10b981`
  - **Cerrar** = gris `#6b7280`
  - **Eliminar** = rojo `#ef4444`

### Toasts en general
- `views/compania/gestion_flota.php` usa `showAlert(...)` para éxito/error al agregar/eliminar vehículos.

### Sidebar "Mi panel"
- En `dashboard.php` (sidebar), turista/compania/soporte ganaron **"Mi panel"** como primer enlace → `?view=panel` → incluye `views/dashboard_main.php`. Soporte además tiene **"Tickets"**.
- Admin tiene "Dashboard" (`?view=dashboard` → `views/admin/dashboard_admin.php`).

### Logout con confirmación
- `dashboard.php` → `confirmarLogout()` usa `showConfirm('¿Estás seguro...', ...)` y redirige a `logout.php`.

### "Limpiar filtros" en el buscador (v0.3.1)
- `views/turista/buscar.php` → el botón "Ver todo" pasó a ser **"Limpiar filtros"** (outline, icono X) y `limpiarFiltros()` resetea ciudad + fechas y recarga el catálogo.

### Reportes del admin con gráficas (base)
- `views/admin/reportes.php` → KPIs + Chart.js. Se extendió en la Ronda 2.

### Badge en "Mis reservas"
- `views/turista/mis_reservas.php` → etiqueta "Pendiente de confirmación" para reservas no confirmadas.

---

## 4. Ronda 2 — Filtros, colores y reportes con datos reales (v0.3.2)

### Barra de filtros "igual a la de reservas"
Vista de referencia: **`views/admin/reservas.php`**. Estructura (flex, sin la clase `.table-toolbar`):

```html
<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1rem;">
    <select class="form-control" style="max-width:220px;">...</select>   <!-- desplegable de orden -->
    <input class="form-control" style="flex:1;">...</input>              <!-- búsqueda -->
    <button class="btn btn-primary" onclick="aplicarFiltrosX()">Filtrar</button>
    <span style="margin-left:0;">N de M</span>
</div>
```

Reglas clave:
- Los filtros **solo se aplican al pulsar "Filtrar"** (sin eventos `input`/`change` en el keydown).
- El contador "N de M" muestra resultados actuales vs total.
- Si no hay coincidencias, se muestra mensaje en lugar de vaciar la lista.

Dónde se aplicó:
- `views/admin/tickets.php`, `views/soporte/tickets.php`, `views/turista/soporte.php` → `aplicarFiltrosTickets()`. Búsqueda por asunto/mensaje/usuario/estado. Orden: `fecha_desc` (default), `fecha_asc`, `estado_asc`, `estado_desc`.
- `views/admin/usuarios.php` → `aplicarFiltrosUsuarios()`. Búsqueda por nombre/email/ciudad/rol/id. Orden: `id_asc` (default), `id_desc`, `nombre_asc`, `nombre_desc`, `rol_asc`, `ciudad_asc`.

### Menú del usuario
- `css/style-global.css` → el hover de "Cerrar sesión" dejó de mostrar fondo "cremita" (`background: transparent` en `.user-menu-item.logout:hover`).

### Reportes con datos reales + exportación CSV
- `api/admin.php` con `?reporte=1` ya regresa además:
  - `ingresos_por_mes` (solo confirmadas, 6 meses)
  - `top_vehiculos` (TOP 5 más reservados, con ingresos)
  - `reservas_por_compania` (cantidad + ingresos por compañía)
- `views/admin/reportes.php` → contentido en vez de repetir el dashboard:
  - KPIs, **doughnut** de reservas por estado, **línea** de ingresos por mes, **barras** de reservas por mes, **tabla Top 5 vehículos**, **tabla por compañía**, **lista por rol**.
  - Botón **"Exportar CSV"** → `exportarCSV()` genera `.csv` con BOM `\uFEFF` (para que Excel lea acentos) y `csvCell()` escapa comas/comillas/saltos. Descarga `reporte_rentwheels.csv`.
- Alineación numérica: `.reporte-table th.num { text-align: right; }` en `css/style.css`, y `class="num"` en las cabeceras "Reservas"/"Ingresos" de las tablas del reporte (para que el título quede alineado con los valores).

---

## 5. Ronda 3 (complementos) — Mensajes de login/register como toasts

- `login.php` y `register.php`: se eliminaron los `<div class="error-msg">` / `success-msg` / `warning-msg` y ahora las alertas salen como **toasts** (patrón de la sección 2).
- `login.php` muestra toasts para:
  - `?error` → error "Credenciales inválidas. Verifica tu email y contraseña."
  - `?recuperado=ok` → éxito "Contraseña actualizada correctamente..."
  - `?codigo=...` → warning con el código de verificación de la empresa
  - `?registro=ok` → éxito "Registro exitoso."
- `register.php`: `?error` → toast error (texto libre, ya viene decodificado) y `?registro=ok` → éxito. Se borró el div `#formError` (muerto) y la línea de `validarRegistro()` que lo referenciaba.
- Nota: el `urldecode()` que estaba en las vistas era redundante (`$_GET` ya llega decodificado) — se eliminó.

---

## 6. Ronda 4 — Ingreso por rol, panel, flota y verificación (v0.3.3)

### Pantalla de ingreso por rol
- `includes/auth.php` → `redirigirSegunRol()` (ver tabla de la sección 1).

### Buscador — botón que se salía del cuadro
- `views/turista/buscar.php` → el contenedor de los dos botones pasó de `display:flex` (donde dos `.btn-block` al 100% se desbordaban) a **grid de 2 columnas**:
  ```html
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">...</div>
  ```

### Panel de usuario (`views/dashboard_main.php`)
- Saludo del héroe → **"¡Bienvenido, compañía <nombre>!"** si el rol es `compania`; el resto conserva "¡Bienvenido, <nombre>!".
- **"Accesos rápidos" eliminada por completo** (turista, compañía, admin y soporte) — se borró el bloque `.quick-title`/`.quick-grid`.
- **Gráfico "Resumen de reservas"**: ya no se expande infinitamente.
  - Antes: `<canvas width="400" height="200">` directo en `.chart-panel` con `responsive:true; maintainAspectRatio:false` → crecimiento circular Canvas↔contenedor.
  - Ahora: el canvas va en `.chart-wrap` (`position:relative; height:300px` en `css/style.css`) y sin `width/height` en el HTML. Chart.js lo llena sin crecer.
  - Si todas las cantidades son 0 → muestra "No hay datos para mostrar." (es el placeholder del doughnut confirmadas/pendientes/canceladas).

### Gestión de flota (`views/compania/gestion_flota.php`) — filtros + modal
Se reescribió toda la vista:
- **Botones "Agregar vehículo"**: uno en el encabezado y otro junto a "Vehículos actuales"; ambos abren un **modal** propio (`#vehiculoModalOverlay`):
  - Overlay fijo + diálogo animado (`.flota-modal-*` en un `<style>` local de la vista).
  - Cierra con **×**, clic fuera del diálogo o **Escape**.
  - Campos: marca, modelo, año, precio por día y **URL de imagen con vista previa en vivo** (`actualizarPreview()`): al escribir el link se muestra el `<img>` (con `onerror` cae a placeholder "Sin imagen cargada").
- **Barra de filtros** (`.table-toolbar`) sobre "Vehículos actuales":
  - Desplegable **estado**: Todos / Disponible / Reservado (estado=Reservado si el vehículo tiene reserva `pendiente` o `confirmada`).
  - **Buscador** por marca o modelo.
  - **Orden**: recientes/antiguos/marca A-Z y Z-A/precio menor-mayor y mayor-menor.
  - Botón **Filtrar** → `aplicarFiltros()` re-renderiza (solo al pulsarlo).
  - Contador "N vehículos" (`.toolbar-count` con `margin-left:auto`).
- JS con estado global: `vehiculosData` y `reservasActivas`; `cargarVehiculos()` los rellena y pinta "Últimos vehículos" (5 más recientes) + aplica filtros. `cardHtml(v)` es la plantilla reutilizable (escape con `escapeHtml`).

### Cuenta verificada
- `dashboard.php` consulta `verificado` del usuario al inicio (`$user_verificado`).
  - **Sidebar**: el enlace "Verificar cuenta" aparece solo si empresa **no verificada**.
  - **Check azul estilo Twitter** junto al nombre en el header (`.rw-verified-badge`, `#1da1f2`) si `verificado = 1`.
- `views/compania/verificacion.php` ya mostraba el estado; con el enlace oculto la sección desaparece de la navegación cuando está verificada.

---

## 7. Ronda 5 (complementos) — URL en modal, recuperación de contraseña y saludo

- **Campo URL del modal** (`views/compania/gestion_flota.php`):
  - Antes quedaba suelto debajo de la grilla (se veía "raro", con estilo nativo de `type="url"`).
  - Ahora está **dentro de `.flota-modal-grid`** ocupando todo el ancho con `style="grid-column:1 / -1;"`, `type="text"` y misma clase `.form-control` → sigue el diseño de los demás campos.
- **Recuperar / restablecer contraseña con toasts**:
  - `recuperar_contrasena.php`: `?error` → toast error; `?success` → toast éxito. El token de prueba ya no se pinta como `success-msg`, sino en una **caja discreta** bajo el formulario con el enlace "Clic aquí para restablecer tu contraseña".
  - `restablecer_contrasena.php`: errores de `procesar_restablecer.php` y el éxito → toasts; se conserva el botón "Ir al inicio de sesión". Eliminados `error-msg`/`success-msg`.
- **Saludo con nombre**: "¡Bienvenido, compañía <nombre>!" (antes solo decía "compañía" sin el nombre).

---

## 8. Ronda 6 — Galería del inicio desde la BD y buscador mejorado

### Galería del inicio (`index.php`)
El hero-gallery ya NO tiene autos hardcodeados: los **5 vehículos más recientes** (`disponible = 1`) salen de la BD con `ORDER BY id DESC LIMIT 5` (un solo `$pdo->query` arriba de la página). Con eso:
- Se generan dinámicamente los `.gallery-item` y sus **5 `.gallery-tab`** (puntos / "círculos-óvalos").
- Cada imagen trae el badge **"Marca Modelo $precio/día"** y el `alt` "Marca Modelo".
- Las posiciones se reparten con las clases `active` / `pos1` / `pos2` / `pos3` (el 5º auto comparte `pos3`, es la parte trasera del "mazo" de cartas).
- Si no hay autos disponibles, cae a un item placeholder ("No hay vehículos por ahora").
- **Click en cualquier imagen la trae al frente**: en el script se agregó `items.forEach(... item.addEventListener('click', ...))` que hace `apply(dataset.idx)` y reinicia el autoplay (igual que un tab). Además `.gallery-item` ganó `cursor: pointer`.
- **Puntos más chicos**: `.gallery-tab` pasó de `11px` a `8px`, el `gap` de `0.55rem` a `0.5rem`, y el activo escala `1.35` en vez de `1.4` (sigue siendo rojo `--rw-red-light`).

Vehículos que hoy saldrían (id DESC, disponibles): 42 Mustang GT $200, 40 Tucson $75, 39 Sentra $50, 38 Corolla $45, 37 HR-V $80.

### Buscador de vehículos (`views/turista/buscar.php`)
- **Desplegable de ciudad (tres niveles)**:
  - `value=""` → **"Seleccionar ciudad"** (por defecto): al pulsar Buscar NO se busca, se muestra el mensaje de indicación.
  - `value="todas"` → **"Todas las ciudades"**: busca en todo el catálogo (el JS lo convierte a `lugar=''` con `lugarEnvio = (lugar === 'todas') ? '' : lugar`).
  - El resto son las ciudades reales con su **conteo** (ver siguiente bloque).
- **Ya no se auto-carga el catálogo al entrar**: se eliminó `buscarVehiculos(false);` del inicio. En su lugar `mostrarMensajeInicial()` pinta un estado vacío (misma clase `.empty-state` que "No hay vehículos disponibles", pero con icono de map-pin y **otro texto**: "Selecciona una ciudad / Elige la ciudad donde vas a alquilar y pulsa Buscar para ver los vehículos disponibles.").
  - `limpiarFiltros()` ahora vuelve a ese mensaje (ya no recarga el catálogo completo).
- **Botones más chicos y dentro del cuadro**: se quitaron los `.btn-block` (que al 100% de ancho se veían enormes y se desbordaban). El contenedor pasó a `display:flex; flex-wrap:wrap; gap:0.75rem; margin-left:auto;` (pegados a la derecha) y los botones ocupan solo su contenido. **"Limpiar filtros" lleva `border:2px solid var(--rw-red)` por defecto** (además de su clase `.btn-outline`).
- **Formulario en modo flex**: el `search-form` dejó de ser grid (`minmax(180px,1fr)`) y ahora es `display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;`:
  - **Fechas juntas a la izquierda**: `fecha_inicio` y `fecha_fin` viven en un mismo wrapper `display:flex; gap:0.5rem;` → quedan pegadas entre sí.
  - Los botones con `margin-left:auto` quedan después con espacio, en fila: **Buscar** y a su **derecha Limpiar filtros**.

### Conteo de vehículos por ciudad en el desplegable
- Cada opción de ciudad ahora muestra a la derecha **"(N)"** donde N es cuántos vehículos hay **disponibles** en esa ciudad según la BD.
- El conteo sale de un JOIN como el del buscador: `SELECT u.ciudad, COUNT(*) FROM vehiculos v JOIN usuarios u ON v.compania_id = u.id WHERE v.disponible = 1 GROUP BY u.ciudad` → se guarda en `$conteoPorCiudad[]` y se consulta con `$conteoPorCiudad[$ciudad] ?? 0` (las ciudades sin vehículos muestran 0).
  - Nota: `vehiculos` NO tiene columna `ciudad`; la ciudad es la de la compañía (`usuarios.ciudad`), igual que filtra `api/vehiculos.php` con `u.ciudad LIKE`.
  - Guardado con `isset($pdo)` porque la vista vive dentro de `dashboard.php`.
- Hoy: Ciudad de Panamá (4), Bocas del Toro (3), Aguadulce (1), resto (0).
- "Seleccionar ciudad" y "Todas las ciudades" NO llevan conteo.

### Fechas más cortas
- Los inputs `type="date"` (inicio y fin) ahora llevan `style="width:100%; max-width:140px;"` para que no se estiren todo el ancho de la celda (la grilla del formulario pedía mínimo 180px por celda).

---

## 9. Cómo verificar que todo sigue funcionando

```powershell
# Sintaxis PHP de las páginas tocadas
php -l includes\auth.php
php -l dashboard.php
php -l views\dashboard_main.php
php -l views\turista\buscar.php
php -l views\compania\gestion_flota.php
php -l login.php; php -l register.php
php -l recuperar_contrasena.php; php -l restablecer_contrasena.php
php -l api\admin.php
php -l index.php

# Sintaxis del script inline del carousel (index.php): extraer el bloque
# entre <script> y </script> (es puro JS, no tiene <?php) y correr node --check
# Igual con el <script> final de views\turista\buscar.php

# Sintaxis del script inline de gestion_flota (extraer el <script> sin src y pasar a node)
# 1) Abrir views/compania/gestion_flota.php, copiar el contenido entre <script> y </script>
# 2) guardarlo en un .js temporal y correr:  node --check temp.js
```

### Render por CLI (truco)
Los archivos CLI no tienen `$_SERVER['REQUEST_METHOD']` ni sesión; prepararlos antes de `include`:

```php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
session_start();
$_SESSION['user_id'] = 42;          // usuario real de la BD
$_SESSION['nombre']  = 'Empresa Test';
$_SESSION['rol']     = 'compania';
$_GET['view'] = 'panel';
ob_start();
include 'dashboard.php';            // CWD = raíz del proyecto
$html = ob_get_clean();
// verificar con strpos() las cadenas esperadas
```

Usuarios reales útiles (verificado): 29=compania(1), 31=turista(0), 42=compania(1), 47=turista(0), 48=compania(0), 49=compania(1), 51=turista(1), 53=turista(1), 54=compania(0), 55=soporte(1).

---

## 10. Errores típicos y trucos al trabajar este proyecto

- **`&&` no sirve en PowerShell 5.1** → encadenar con `;` o `if ($?) { }`.
- **`node --check` sobre scripts con `<?php` inline falla** (el placeholder PHP rompe el JS); se valida tras el render o copiando el script ya "expandido".
- **`$pdo` por scope**: al hacer `include 'dashboard.php'` dentro de una función, `$pdo` (creado en `config.php` vía `require_once`) solo existe en esa primera llamada; si repites includes en el mismo proceso, las siguientes se quedan sin `$pdo` (Error "Call to a member function prepare() on null"). Por eso cada probe de render probaba UNA sola vista por script.
- **Sesiones por CLI**: sesiones no persistidas con archivos `C:\xampp\tmp\sess_<id>`; si se escribe `$_SESSION` con strings mal deserializados ($s:NN con largo incorrecto), PHP borra la sesión.
- **Temas/colores**: usar variables `--rw-*`; no existe `--rw-gray-50` (usar literal `#fafaf9`).
- **Chart.js con `maintainAspectRatio:false`** necesita un contenedor con altura fija (`.chart-wrap`) o crece sin límite.
- **`showToast` es `textContent`**: no admite HTML en el mensaje; para links/códigos de prueba se pintan aparte (caja bajo el formulario).
- **Estados de tickets**: `abierto`/`respondido`/`cerrado` (no "en proceso").
- **Sin emojis en el código** (regla del proyecto).
- **No commitear** salvo que el usuario lo pida explícitamente.