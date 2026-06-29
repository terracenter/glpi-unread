# Unread Tracker — Plugin GLPI

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)](LICENSE)
[![GLPI Version](https://img.shields.io/badge/GLPI-10.x%20|%2011.x-green.svg)](https://glpi-project.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B%20%2F%208.2%2B-blue.svg)](https://www.php.net/)
[![Release](https://img.shields.io/badge/Release-1.2.0-brightgreen.svg)](CHANGES)

Plugin para GLPI que implementa un **centro de notificaciones estilo Odoo** y rastreo de tickets **no leídos, actualizados y retrasados** con clasificación visual según la prioridad del ticket.

Mejora la visibilidad operacional, reduce el riesgo de tickets olvidados y acelera el ciclo de respuesta en helpdesk.

---

## 🎯 Características Principales

* 🔔 **Centro de notificaciones Systray (Estilo Odoo)**: Un único ícono de campana interactivo integrado de forma nativa en la barra superior de GLPI.
* 🚦 **Badge reactivo de prioridad**: El badge del ícono de la campana cambia de color según la prioridad del ticket más urgente pendiente de atención (Rojo parpadeante para críticos/mayores, Naranja para altos, Azul para medios).
* 📊 **Filtros rápidos e inteligentes**: El menú desplegable permite ver en tiempo real el desglose de:
  * **Todos**: Todos los tickets no leídos o modificados asignados.
  * **Nuevos**: Tickets en estado *Nuevo* (`status: 1`).
  * **Actualizados**: Tickets que ya habías leído pero que han recibido nuevos comentarios, seguimientos o cambios.
  * **Retrasados (SLA)**: Tickets activos cuya fecha límite de resolución (`solve_delay_limit`) ha expirado.
* ⚡ **Carga ultrarrápida (Cache en cliente)**: Carga instantánea de filtros locales sin necesidad de realizar múltiples consultas AJAX de fondo.
* 🛡️ **Autoloading Nativo**: Cumple estrictamente con el estándar PSR-4 de GLPI 10+; las clases se autocargan desde `src/` sin requerir composer ni dependencias de terceros en producción.

---

## 📋 Requisitos

- **GLPI:** 10.x — 11.x
- **PHP:** 7.4 o superior (GLPI 10.x) / 8.2 o superior (GLPI 11.x)
- **Base de Datos:** MariaDB / MySQL (compatible con esquema GLPI estándar)

---

## 🚀 Despliegue en el Servidor (Regla /tmp)

Para garantizar un despliegue limpio y evitar bloqueos de archivos en producción, el proceso de build se realiza siempre en `/tmp` del servidor remoto:

```bash
# 1. Clonar o actualizar el repositorio en /tmp
git clone https://github.com/terracenter/glpi-unread.git /tmp/unreadtracker

# 2. Empaquetar el build (excluyendo git y dependencias de desarrollo)
tar -czf /tmp/unreadtracker.tar.gz -C /tmp/unreadtracker --exclude='.git' --exclude='composer.json' .

# 3. Limpiar carpeta de producción e instalar
sudo rm -rf /var/www/glpi/plugins/unreadtracker
sudo mkdir -p /var/www/glpi/plugins/unreadtracker
sudo tar -xzf /tmp/unreadtracker.tar.gz -C /var/www/glpi/plugins/unreadtracker/
sudo chown -R www-data:www-data /var/www/glpi/plugins/unreadtracker/

# 4. Instalar y activar vía CLI en la raíz de GLPI
sudo -u www-data php /var/www/glpi/bin/console glpi:plugin:install unreadtracker
sudo -u www-data php /var/www/glpi/bin/console glpi:plugin:activate unreadtracker
```

---

## 📖 Uso y API Interna

El plugin proporciona la clase `GlpiPlugin\Unreadtracker\Tracking` para interactuar con el motor de estados:

### Marcar Ticket como Leído
```php
use GlpiPlugin\Unreadtracker\Tracking;

// Marca un ticket como leído por el técnico
Tracking::markAsRead(
    $tickets_id = 123,  // ID del ticket
    $users_id = 45      // ID del usuario técnico
);
```

### Verificar si un Ticket tiene actualizaciones pendientes
```php
if (Tracking::isUnread($tickets_id, $users_id)) {
    echo "📌 El ticket tiene cambios o respuestas pendientes";
}
```

### Obtener Estadísticas y Listado Unificado
```php
$data = Tracking::getUnreadStatsAndTickets($users_id);
// Retorna: ['stats' => ['total' => X, 'new' => Y, 'updated' => Z, 'overdue' => W], 'tickets' => [...]]
```

---

## 🔧 Fases de Desarrollo

| Fase | Estado | Descripción |
|------|--------|-------------|
| **1. Scaffolding** | ✅ Completa | Setup inicial, instalador, clase base |
| **2. Frontend & Notificaciones** | ✅ Completa | Menú Systray estilo Odoo, filtros locales, colores de prioridad, SLA |
| **3. Integraciones** | 📅 Planificada | Notificaciones email en lote, webhooks, soporte API REST adicional |

---

## 📊 Esquema de Base de Datos

El plugin crea de forma automática la siguiente tabla al activarse:

```sql
CREATE TABLE `glpi_plugin_unreadtracker_read` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tickets_id` INT NOT NULL,
    `users_id` INT NOT NULL,
    `date_read` TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
               ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ticket_user` (`tickets_id`, `users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📜 Licencia

Ver archivo [LICENSE](LICENSE) (Community AGPL-3.0 / Enterprise Comercial).

---

## 🏢 Sobre Humanbyte

Desarrollado por **Freddy Taborda** bajo [Humanbyte](https://humanbyte.io). Especialistas en infraestructura, automatización de red e integraciones avanzadas GLPI/ISP.
