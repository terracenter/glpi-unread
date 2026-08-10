# CLAUDE.md — GLPI Plugin Unread (glpi-unread)

> Plugin GLPI 10.x/11.x para rastreo de tickets no leídos. Stack y workflow
> específicos. Reglas generales (idioma, git workflow, autoría) en LEY y AGENTS.

@/home/freddy/Workspace/Obsidian/LEY_PRINCIPAL.md
@/home/freddy/Workspace/.agents/AGENTS.md
@/home/freddy/Workspace/Desarrollo/AGENTS.md

## Stack

- **Lenguaje:** PHP 8.x
- **Frontend:** HTML, CSS nativo (compatible con Tabler UI de GLPI), JavaScript nativo (AJAX)
- **Base de Datos:** MariaDB/MySQL (Esquema del core de GLPI)

## Entorno de pruebas

- **Instancia de GLPI:** `https://glpi-10-test.fibextelecom.info`
- **Credenciales:** `/home/freddy/Workspace/Desarrollo/iptv-load-balancer/.env` (no incluir en commits)
- **Build/Deploy:** usar `/tmp` en el servidor remoto para evitar bloqueos de permisos en producción.

## Handoff y orquestación

- **Handoff oficial:** `.agents/handoffs/glpi-unread.md` — estado actual de tareas y checklist.
- **Autoría de commits:** `Freddy Taborda <terracenter@gmail.com>` en TODOS los commits
  (ver `LEY_PRINCIPAL.md §✍️`). El `committer` puede variar si hubo rebase, pero el
  `author` siempre es Freddy.

## Comandos útiles

```bash
find . -name "*.php" -exec php -l {} \;
```

Logs de error de GLPI en el servidor: `/var/www/html/glpi/files/_log/php-errors.log`.