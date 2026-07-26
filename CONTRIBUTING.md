# Contribuir a PHPost

## Convención de Commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/).

### Formato

```
<tipo>(<alcance>): <asunto corto>

<lines en blanco>

[cuerpo con descriptcion detallada]

<lines en blanco>

[footer(s)]
```

### Tipos

| Tipo | Descripcion |
|------|-------------|
| `feat` | Nueva funcionalidad |
| `fix` | Correccion de bug |
| `docs` | Solo cambios en documentacion |
| `style` | Formato (no afecta el codigo) |
| `refactor` | Reestructuracion sin cambiar funcionalidad |
| `perf` | Mejora de rendimiento |
| `test` | Agregar o corregir tests |
| `chore` | Tareas de mantenimiento, dependencias, config |
| `ci` | Cambios en integracion continua |
| `build` | Sistema de build o dependencias externas |

### Alcances comunes

| Alcance | Descripcion |
|---------|-------------|
| `src` | Logica de negocio en `src/` |
| `views` | Plantillas y vistas en `views/` |
| `assets` | Frontend: JS, CSS, imagenes en `assets/` |
| `config` | Archivos de configuracion en `config/` |
| `themes` | Sistema de temas en `themes/` |
| `install` | Scripts de instalacion en `install/` |
| `migration` | Migraciones de base de datos en `migration/` |
| `storage` | Archivos almacenados en `storage/` |
| `api` | Endpoints de API |

### Ejemplos

```
feat(views): agregar vista de login con formulario

- Crear views/login.php con form de email/password
- Agregar validacion client-side con Alpine.js
- Integrar con endpoint api.login.php

Refs: #12
```

```
fix(auth): corregir sesion expirada no redirige al login

- Verificar token antes de cada request en header.php
- Destruir sesion si el token es invalido o expirado
- Redirigir a /?page=login con mensaje de error

Fixes: #45
```

```
refactor(src): extraer clase DatabaseHelper de Controller

- Mover metodos de conexion a src/Utils/Database/DatabaseHelper
- Actualizar Controller.php para inyectar la dependencia
- Mantener compatibilidad con codigo existente via facade DB

Closes: #30
```

```
chore(deps): agregar Composer con autoload PSR-4

- Crear composer.json con autoload para src/
- Agregar .env y phpdotenv para credenciales
- Actualizar .gitignore para vendor/

 BREAKING CHANGE: requiere `composer install` previo
```

### Reglas

- El asunto debe tener maximo 72 caracteres
- El cuerpo explica el **que** y el **por que**, no el **como**
- Usar imperativo en el asunto: "agregar" no "agregado"
- Referenciar issues/PRs en el footer: `Fixes: #12`, `Closes: #34`, `Refs: #56`
- Un `BREAKING CHANGE` en el footer indica cambios que rompen compatibilidad
