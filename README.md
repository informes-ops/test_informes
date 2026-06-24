# ZGROUP — Sistema de Informes Técnicos

Sistema web PHP + MySQL para registro de inspecciones preliminares e informes técnicos de equipos **Reefer** y **Genset**, con panel de supervisión, integración Odoo, notificaciones (Telegram, WhatsApp, Push) y asistente de redacción con IA.

## Documentación

Consulta **[funcionamiento.md](funcionamiento.md)** para la arquitectura MVC, flujos de negocio, base de datos e instrucciones de mantenimiento.

## Requisitos

- PHP 8.0+ con extensiones: `pdo_mysql`, `curl`, `json`, `mbstring`, `dom`
- MySQL 5.7+ / MariaDB 10.3+
- Servidor web Apache o Nginx

## Estructura MVC

```
app/
├── bootstrap.php          # Autoload y configuración global
├── Config/                # app.php, database.php
├── Core/                  # Database, Model, Controller, JsonResponse, SchemaHelper
├── Models/                # TecnicoModel, InformeModel, PreinspeccionModel, CatalogoModel
├── Controllers/           # Controladores API (OdooSyncController, ...)
└── Helpers/               # Funciones auxiliares

database/
└── schema.sql             # Esquema MySQL consolidado

index.php                  # Formulario técnico (legacy, en migración)
panel.php                  # Panel supervisores (legacy, en migración)
db.php                     # Punto de compatibilidad → app/Core/Database
```

## Configuración rápida

1. Copiar `app/Config/database.example.php` y ajustar credenciales MySQL, o definir variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
2. Copiar archivos de ejemplo: `telegram_config.example.php`, `whatsapp_config.example.php`.
3. Crear carpeta `informes/` con permisos de escritura.
4. Importar `database/schema.sql` en una instalación nueva.

## Accesos por defecto

| Área | URL | Contraseña |
|------|-----|------------|
| Técnicos | `index.php` | `tecnicos` |
| Supervisores | `panel.php` | `123456` |

> Cambiar estas contraseñas en producción vía `app/Config/app.php` o variables de entorno.
