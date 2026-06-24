# ZGROUP — Sistema de Informes Técnicos

Sistema web PHP + MySQL para registro de inspecciones preliminares e informes técnicos de equipos **Reefer** y **Genset**, con panel de supervisión, integración Odoo, notificaciones (Telegram, WhatsApp, Push) y asistente de redacción con IA.

## Documentación

Consulta **[funcionamiento.md](funcionamiento.md)** para la arquitectura MVC, flujos de negocio, base de datos e instrucciones de mantenimiento.

## Requisitos

- PHP 8.0+ con extensiones: `pdo_mysql`, `curl`, `json`, `mbstring`, `dom`
- MariaDB 10.3+ / MySQL 5.7+
- Apache2 con `mod_php`

---

## Modo predeterminado: Apache2 nativo

Instalación recomendada para producción y uso diario en el servidor:

```bash
cd /home/telemetriazgroup/Proyectos/test_informes
sudo bash scripts/setup_apache.sh
```

| Área | URL | Contraseña |
|------|-----|------------|
| Técnicos | http://zgroup-informes.local/index.php | `tecnicos` |
| Supervisores | http://zgroup-informes.local/panel.php | `123456` |

Credenciales MySQL: `app/Config/database.php` (`localhost` / `zgroupin_zgroupinformes`).

Verificar: `php database/install.php --verify`

### Instalación manual Apache (si ya tiene LAMP)

```bash
sudo apt install apache2 libapache2-mod-php php-mysql php-curl php-mbstring php-xml mariadb-server
bash scripts/import_db.sh
sudo cp deploy/apache/zgroup-informes.conf /etc/apache2/sites-available/
sudo a2ensite zgroup-informes.conf && sudo a2enmod rewrite && sudo systemctl reload apache2
mkdir -p informes && sudo chown www-data:www-data informes
```

---

## Modo alternativo: Docker (puerto 8877)

Para desarrollo, pruebas o entornos aislados **sin tocar Apache del sistema**:

```bash
bash scripts/docker_up.sh
```

| Área | URL |
|------|-----|
| Técnicos | http://localhost:8877/index.php |
| Supervisores | http://localhost:8877/panel.php |

Incluye Apache + PHP + MariaDB en contenedores. El dump `zgroupin_zgroupinformes.sql` se importa automáticamente la primera vez.

```bash
bash scripts/docker_down.sh          # detener
USE_DOCKER=1 bash scripts/import_db.sh   # reimportar datos
FORCE=1 USE_DOCKER=1 bash scripts/import_db.sh
```

Personalizar puerto: copiar `.env.example` → `.env` y cambiar `WEB_PORT=8877`.

> **Nota:** Apache nativo y Docker no deben usarse a la vez en el mismo puerto. `setup_apache.sh` detiene Docker automáticamente si está activo.

---

## Estructura del proyecto

```
app/                    # MVC (Controllers, Models, Services, Views)
public/assets/js/       # JavaScript del formulario
database/               # schema.sql, install.php
deploy/
├── apache/             # VirtualHost Apache2 (modo nativo)
└── docker/             # Dockerfile (modo Docker :8877)
docker-compose.yml
scripts/
├── setup_apache.sh     # Instalador Apache (predeterminado)
├── docker_up.sh        # Levantar Docker :8877
├── docker_down.sh
└── import_db.sh        # Importar SQL (nativo o Docker)
index.php               # Área técnica
panel.php               # Panel supervisores
```

> Cambiar contraseñas de acceso en producción vía `app/Config/app.php` o variables de entorno.
