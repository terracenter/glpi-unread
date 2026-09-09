# CLAUDE.md — GLPI Plugin Unread (glpi-unread)

> Plugin GLPI 10.x/11.x para rastreo de tickets no leídos.
>
> La ley global y la ley de desarrollos llegan solas (`~/.claude/CLAUDE.md` y `Desarrollo/CLAUDE.md`).
> No las importes aquí. Aquí va solo lo propio del plugin.

## Stack

- **Lenguaje:** PHP 8.x — excepción al stack Go/Rust: el plugin corre dentro del core de GLPI.
- **Frontend:** HTML, CSS nativo (compatible con Tabler UI de GLPI), JavaScript nativo (AJAX)
- **Base de Datos:** MariaDB/MySQL (esquema del core de GLPI)

## Entorno de pruebas

- **Instancia de GLPI:** `https://glpi-10-test.fibextelecom.info`
- **Credenciales:** `/home/freddy/Workspace/Desarrollo/iptv-load-balancer/.env` (no incluir en commits)
- **Build/Deploy:** usar `/tmp` en el servidor remoto para evitar bloqueos de permisos en producción.

## Handoff y orquestación

- **Handoff oficial:** `.agents/handoffs/glpi-unread.md` — estado actual de tareas y checklist.

## Comandos útiles

```bash
rtk find . -name "*.php" -exec php -l {} \;
```

Logs de error de GLPI en el servidor: `/var/www/html/glpi/files/_log/php-errors.log`.
