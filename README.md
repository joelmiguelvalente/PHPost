# PHPost v3.9.9

**Risus** es un sistema de compartimiento de enlaces que permite crear un sitio web similar a Taringa!

![GitHub repo size](https://img.shields.io/github/repo-size/joelmiguelvalente/PHPost?style=flat)

---

## Estado del proyecto

> ⚠️ **En desarrollo activo** — Inicialización del fork 2026

El código se encuentra en proceso de actualización y refactorización. Pueden existir cambios estructurales y ajustes que no sean retrocompatibles.

## Tecnologías

- **PHP 8.5+** (compatible con PHP 8.3+)
- **Smarty 5.8.2** (templates)
- **jQuery 4.0.0** (frontend)
- **MariaDB** (base de datos)
- **PHPMailer** (email)

## ¿Qué es este repositorio?

Este es el repositorio de trabajo para el fork de PHPost (v3.9.9) realizado en **julio de 2026**. El objetivo es:

- Migrar el código de PHP 7.4 a PHP 8.5
- Refactorizar código PHP siguiendo buenas prácticas
- Modernizar y organizar el código JavaScript
- Corregir errores existentes
- Preparar el proyecto para futuras mejoras
- Actualizaciones de Smarty y JBBCode

> **La idea no es solo "que funcione", sino que sea más limpio, seguro y mantenible.**

## Cambios realizados (actualización 2026)

### ✅ Completado

#### 1. Migración de Legacy SQL a Prepared Statements (faltaban estas)
- **188 llamadas** `db_exec()` migradas a `DB::` prepared statements
- **14 archivos** modificados
- `src/Class/c.moderacion.php` (72 llamadas)
- `src/Class/c.muro.php` (32 llamadas)
- `src/Class/c.medals.php` (34 llamadas)
- `src/Class/c.noticias.php` (11 llamadas)
- `src/Class/c.borradores.php` (7 llamadas)
- `src/Class/c.fotos.php` (5 llamadas)
- `src/Api/api.recover.php` (5 llamadas)
- `src/Class/c.rangos.php` (5 llamadas)
- `src/Class/c.useradmin.php` (3 llamadas)
- `src/Class/c.estadisticas.php` (2 llamadas)
- `src/Api/api.upload.php` (1 llamada)
- `src/Class/c.posts.php` (1 llamada)

**Cambios de infraestructura:**
- `src/Extras/functions.php`: `require_once TS_DATABASE . '/db_legacy.php'` eliminado
- `src/Database/db_legacy.php`: reemplazado con aviso de deprecación
- Zero llamadas `db_exec` o `result_array` restantes en `src/`

#### 2. Tipado Completo de Funciones
~**2000+ funciones** tipadas con return type y parameter types en:

- **src/Class/**: 20 archivos
  - c.estadisticas, c.rangos, c.registro, c.admin, c.comentarios, c.useradmin
  - c.muro, c.fotos, c.moderacion, c.borradores, c.user, c.monitor
  - c.censura, c.denuncias, c.noticias, c.portal, c.posts, c.agregar
  - c.core, c.cuenta, c.smarty, c.themes, c.medals, c.mensajes

- **src/Helpers/**: 2 archivos (AdminHelper, AvatarHelper)
- **src/Utils/**: 3 archivos (Paginator, reCaptcha, Themes)
- **src/Extras/**: 2 archivos (bbcode.inc.php, functions.php)
- **src/Database/**: 1 archivo (Database.php)

**Tipos aplicados:** `array`, `string`, `bool`, `void`, `?array`, `string|bool`, `string|array`, `never`, `mixed`, `int`, `float`, etc.

#### 3. Correcciones de Seguridad

##### ✅ S1 - Inyección SQL (RESUELTO)
- 188 llamadas `db_exec()` migradas a prepared statements
- `db_legacy.php` reemplazado con aviso de deprecación

##### ✅ S2 - CSRF (RESUELTO)
- Validación CSRF automática en `RequestGuard::validateCsrfToken()`
- Token CSRF expuesto en `global_data.csrf_token` vía `|json_encode`
- Helper `api()` inyecta automáticamente header `X-CSRF-Token` y campo `csrf_token` en POST
- Configuración flexible con `validate_on` y `exempt_actions`

##### ✅ S4 - escapeHTML universal (RESUELTO)
- Función `escapeHTML()` de `tsCore` reemplazada por `Html::escape()`
- Todos los callers migrados

##### ✅ S5 - Password escape en login (RESUELTO)
- `api.login.php` línea 42: `$password = (string) ($_POST['password'] ?? '');`
- Nunca se sanitizan credenciales

##### ✅ S7 - MD5 en session ID (RESUELTO)
- `bin2hex(random_bytes(32))` — 256 bits de entropía completa
- Catch captura `\Throwable` y relanza la excepción

##### ✅ F4 - Inline JS sin encoding (RESUELTO)
- Todas las variables Smarty en `main_header.tpl` usan `|json_encode nofilter`
- Variables: `domain`, `titulo`, `slogan`, `csrf_token`, `url`, `canonical`, `assets`, `img`, `smiles`

##### ✅ FA3 - .html() XSS patrón sistémico (RESUELTO)
- 13 archivos JS migrados de `$.post/$.ajax/$.get` a helper `api()`
- Centralización de comunicación AJAX con headers de auth, CSRF, parsing JSON, manejo de errores, timeouts

#### 4. Refactorización y Limpieza

##### `src/Class/c.moderacion.php`
- `setHistory()` refactorizada de switch monolítico a funciones especializadas
- Extraída `insertHistory()` usando `DB::insert()`
- Todos los return ahora son `bool` (constituyentes)

##### `src/Class/c.actividad.php`
- Línea 73: `count($data ?? 1)` → `count($data)`
- `DB::fetchAll` siempre retorna array, `?? 1` era código muerto y causaba `TypeError`

##### `src/Class/c.user.php`
- `loadUser()` retorna `?bool` (no `bool|void`)
- Agregado `return true` al final de la función

### ⚠️ Pendiente

#### Hallazgos Activos

**CRÍTICOS (3)**
- **F1**: `eval()` en dialog (jquery.plugins.js:134)
  - Ejecución de código arbitrario si `btn.action` es controlado por el usuario
  - Solución: Eliminar `eval()`, reemplazar con `actionMap` de callbacks registrados

- **F2**: Open redirect (assets/js/login.js:4-72)
  - Redirección a cualquier sitio web vía `?redirect=`
  - Solución: Validar redirect URL - solo rutas relativas en mismo dominio

- **F3**: XSS almacenado en perfil.js (user_name inyectado sin escape)
  - `user_name` como `<img src=x onerror=alert(1)>` ejecuta JS cada vez que alguien abre el diálogo
  - Solución: Escapar `user_name` con `DOMParser/textContent`

**MEDIOS (1)**
- **S6**: SameSite - Unificado en c.session.php y config/bootstrap.session.php ✅

**BAJOS (8)**
- T4: switch → match (80+ casos sin fall-through)
- T5: Readonly subutilizado
- T6: Deprecated method `setSEO()`
- T7: `global vars` en `Database::handleException()`
- A1: DRY violation (sanitizeTableName repetido 5 veces)
- A2: DI Container (md5(serialize()) + RuntimeException sin backslash)
- A3: Hook system (sin unregister)
- A4: Controller globals `exportLegacy()`
- A5: DB facade redundancy (condición redundante y `rawQuery` traga errores)
- FA1: jQuery dependency
- FA2: Accesibilidad (diálogos sin roles, ARIA, focus trap)
- F5: CSP coverage (nonce solo en 1 script de ~20+)

#### Completados (2026)

**MEDIOS (1)**
- **S6**: SameSite - Unificado en c.session.php y config/bootstrap.session.php ✅

**BAJOS (4)**
- **S8**: `unserialize()` - json_encode() en Container.php para cache keys ✅
- **T6**: `setSEO()` - Eliminado de c.core.php y migrado a Extras::slugify() ✅
- **T7**: `global vars` - Eliminado en Database.php ✅
- **A1**: `sanitizeTableName()` - Extraído como función única en c.dbmanager.php ✅

#### Roadmap de Trabajo

**P0 - Prioridad crítica**
- F1: Eliminar `eval()` del sistema de diálogos
- F2: Validar redirect URL
- F3: Escapar user_name en perfil.js

**P1 - Alta prioridad**
- T6: Agregar `@trigger_error()` a métodos deprecated
- T7: Eliminar global vars en `Database::handleException()`
- A1: Extraer sanitizeTableName() único
- A5: Simplificar condición en `handleException()`

**P2 - Media prioridad**
- T4: Migrar switch → match
- T5: Agregar readonly promoted properties y backed enums donde sea apropiado
- A2: DI Container (usar json_encode en vez de serialize, \RuntimeException con backslash)

**P3 - Baja prioridad**
- FA1: jQuery dependency (evaluar migración a vanilla JS)
- FA2: Accesibilidad (diálogos con roles, ARIA, focus trap)
- F5: Agregar nonces de CSP a todos los script inline

### 📊 Estadísticas

- **Archivos modificados**: ~50 archivos PHP
- **Líneas de código tipadas**: ~2000+ funciones
- **Llamadas db_exec migradas**: 188
- **Errores de sintaxis**: 0
- **Zero llamadas db_exec** o `result_array` restantes en `src/`
- **Helpers, Utils, Extras, Database**: Tipados
- **No aplicado a**: `/smarty/`, `/JBBCode/`, `/phpmailer/`, `password.local.php` (no se despliega)

### Hallazgos (2026)

- **Resueltas**: 11 hallazgos (4 críticos, 1 medio, 1 bajo, 5 completados)
  - 6 resueltos (S1, S2, S4, S5, S7, F4, FA3)
  - 5 completados (S6, S8, T6, T7, A1)
- **Pendientes**: 12 hallazgos (3 críticos, 1 medio, 8 bajos)
- **Total**: 23 hallazgos

## Documentación

- **SECURITY.md**: Política de seguridad

## Licencia

MIT

---

**Fecha de actualización**: 15.07.26 15:47:44
