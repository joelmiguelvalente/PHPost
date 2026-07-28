# PHPost

Sistema de gestión de contenido y compartimiento de enlaces, diseñado para comunidades activas.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.5+-8892BF.svg)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.6+-003545.svg)](https://mariadb.org/)

---

## Acerca de

PHPost es una plataforma para crear sitios de compartimiento de enlaces estilo Taringa!, con soporte para publicaciones, comentarios, votaciones, sistemas de reputación y moderación.

## Tecnologías

| Componente | Tecnología |
|------------|------------|
| Backend | PHP 8.5+ |
| Templates | Smarty 5.8.2 |
| Frontend | jQuery 4.0.0 |
| Base de datos | MariaDB |
| Email | PHPMailer |

## Estructura del proyecto

```
phppost/
├── src/            # Lógica de negocio
│   ├── Api/        # Endpoints de API
│   ├── Class/      # Clases principales
│   ├── Database/    # Capa de acceso a datos
│   ├── Extras/     # Utilidades y helpers
│   ├── Helpers/    # Helpers de presentación
│   └── Utils/      # Utilidades generales
├── views/          # Plantillas PHP
├── assets/         # Frontend (JS, CSS, imágenes)
├── themes/         # Sistema de temas
├── config/         # Configuración
├── install/        # Scripts de instalación
├── migration/      # Migraciones de base de datos
└── storage/        # Archivos almacenados
```

## Requisitos

- PHP 8.5+ (compatible con PHP 8.3+)
- MariaDB 10.6+
- Composer
- Servidor web con soporte `.htaccess` (Apache) o equivalente

## Instalación

```bash
# Clonar repositorio
git clone https://github.com/DevBroken/PHPost.git
cd PHPost

# Instalar dependencias
composer install

# Configurar base de datos
cp config/Config.Database.php.example config/Config.Database.php
# Editar credenciales en Config.Database.php

# Ejecutar instalador
# Visitar http://tu-dominio/install/
```

## Desarrollo

### Convención de commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

```
<tipo>(<alcance>): <asunto corto>

- Detalle 1
- Detalle 2

Fixes: #12
```

Ver [CONTRIBUTING.md](CONTRIBUTING.md) para la guía completa.

## Seguridad

Si descubres una vulnerabilidad, consulta [SECURITY.md](SECURITY.md) para el proceso de reporte.

## Licencia

Este proyecto está bajo la licencia MIT. Ver [LICENSE](LICENSE) para detalles.

---

**Última actualización**: julio 2026
