# GLPI Plugin Unread (glpi-unread)

Este es el repositorio de desarrollo del plugin de GLPI compatible con las versiones 10.x y 11.x para el rastreo de tickets y actualizaciones no leídas.

---

## 🛠️ Stack Tecnológico
- **Lenguaje:** PHP 8.x
- **Frontend:** HTML, CSS nativo (compatible con Tabler UI de GLPI), JavaScript nativo (AJAX)
- **Base de Datos:** MariaDB/MySQL (Esquema del core de GLPI)

---

## 🌐 Configuración del Entorno de Pruebas
- **Instancia de GLPI:** `https://glpi-10-test.fibextelecom.info`
- **Variables de Entorno y Credenciales:** `/home/freddy/Workspace/Desarrollo/iptv-load-balancer/.env`
- **Regla de Compilación y Despliegue:** Para preparar la compilación (build de Composer/dependencias), **siempre** se debe utilizar el directorio `/tmp` en el servidor remoto para evitar la ejecución directa o bloqueos de permisos en caliente sobre la carpeta de producción del plugin.

---

## 🤝 Colaboración Multi-Agente (Orquestador)
Este desarrollo se coordina mediante el orquestador manual.
- **Handoff Oficial:** Referirse a [.agents/handoffs/glpi-unread.md](file:///home/freddy/Workspace/.agents/handoffs/glpi-unread.md) para el estado actual de las tareas y el checklist de desarrollo.
- **Identidad de Commits:** Utilizar el prefijo `[Claude@cachy]` o `[Claude@minipc]` en los commits de acuerdo con el host donde se ejecute el agente (ver `BOOTSTRAP.md` en la raíz de `.agents`).

---

## 🔍 Comandos Útiles y Rutas
- Para verificar errores de sintaxis en PHP localmente:
  ```bash
  find . -name "*.php" -exec php -l {} \;
  ```
- Logs de error de GLPI en el servidor (si se tiene acceso):
  `/var/www/html/glpi/files/_log/php-errors.log`
