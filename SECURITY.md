# Política de Seguridad

## Versiones compatibles

PHPost v3.9.10 es compatible con PHP 8.5+ (PHP 8.4.13 y 8.5.0 soportados) y Smarty 5.8.2+.

| Versión | Tipo | Soportado |
| ------- | ---- | --------- |
| 5.8.2   | Smarty | :white_check_mark: |
| 8.3.16  | PHP   | :white_check_mark: |
| 8.4.13  | PHP   | :white_check_mark: |
| 8.5.0   | PHP   | :white_check_mark: |

## Vulnerabilidades

### Resueltas (2026)

| Hallazgo | Severidad | Descripción |
| -------- | --------- | ----------- |
| S1 - Inyección SQL | Crítico | 188 llamadas `db_exec()` migradas a `DB::preparedQuery()` |
| S2 - CSRF | Crítico | Validación automática en todos los métodos POST/PUT/PATCH/DELETE |
| F4 - XSS Reflejado | Crítico | Variables Smarty en `<script>` con `|json_encode` |
| FA3 - XSS Patrón .html() | Crítico | 13 archivos JS migrados a helper `api()` |
| S5 - Password escape | Medio | $password sin escapeHTML antes de verificar |
| S7 - MD5 session ID | Bajo | bin2hex(random_bytes(32)) — 256 bits de entropía |

### Pendientes

| Hallazgo | Severidad | Descripción |
| -------- | --------- | ----------- |
| F1 - eval() | Crítico | eval() en jquery.plugins.js:134 |
| F2 - Open redirect | Crítico | Validar redirect URL con whitelist |
| F3 - XSS almacenado | Crítico | Escapar user_name en perfil.js |

### Completados (2026)

| Hallazgo | Severidad | Descripción |
| -------- | --------- | ----------- |
| S6 - SameSite | Medio | Unificado en c.session.php y config/bootstrap.session.php |
| S8 - unserialize | Bajo | json_encode() en Container.php para cache keys |
| T6 - setSEO() | Bajo | Eliminado de c.core.php y migrado a Extras::slugify() |
| T7 - global vars | Medio | Eliminado en Database.php |
| A1 - sanitizeTableName | Medio | Extraído como función única en c.dbmanager.php |

**Otros pendientes**: T4, T5, A2, A3, A4, A5, FA1-FA2, F5

## Resumen

- **Resueltas**: 11 hallazgos (4 críticos, 1 medio, 1 bajo, 5 completados)
- **Pendientes**: 12 hallazgos (3 críticos, 1 medio, 8 bajos)
- **Archivos modificados**: ~50 PHP
- **Funciones tipadas**: ~2000+
- **Errores de sintaxis**: 0

## Informar de una vulnerabilidad

Si ha descubierto un problema de seguridad con PHPost, contáctenos al **portfoliomiguel92@gmail.com**.

No divulgue sus hallazgos públicamente y POR FAVOR, POR FAVOR, no presente un Problema.

Intentaremos confirmar la vulnerabilidad y desarrollar una solución si corresponde. Cuando publiquemos la corrección, publicaremos una versión de seguridad. Por favor, háganos saber si desea ser acreditado.
