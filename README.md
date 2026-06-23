# Unread Tracker — Plugin GLPI

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)](LICENSE)
[![GLPI Version](https://img.shields.io/badge/GLPI-10.x%20|%2011.x-green.svg)](https://glpi-project.org/)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-blue.svg)](https://www.php.net/)
[![Release](https://img.shields.io/badge/Release-1.0.0-brightgreen.svg)](CHANGES)

Plugin para GLPI que implementa rastreo de tickets y actualizaciones **no leídas** por técnico.
Mejora la visibilidad operacional, reduce el riesgo de tickets olvidados y acelera el ciclo de
respuesta en helpdesk.

---

## 🎯 Características Principales

✅ **Rastreo automático** — marca tickets como leídos/no leídos por usuario  
✅ **Tabla de rastreo optimizada** — queries eficientes con índice composite  
✅ **Lógica temporal** — detecta actualizaciones no consultadas (`date_mod > date_read`)  
✅ **Contadores globales** — dashboard con cantidad de tickets pendientes por técnico  
✅ **Compatible** — GLPI 10.x y 11.x, PHP 8.0+  
✅ **Ligero** — sin dependencias externas, solo BD nativa  

---

## 📋 Requisitos

- **GLPI:** 10.0 — 11.9
- **PHP:** 8.0 o superior
- **Base de Datos:** MariaDB / MySQL (compatible con esquema GLPI estándar)
- **Acceso:** Rol administrador en GLPI para activación del plugin

---

## 🚀 Instalación

### 1. Descargar el Plugin

```bash
# Opción A: Clonar desde GitHub
cd /var/www/html/glpi/plugins/
git clone git@github.com:terracenter/glpi-unread.git unread

# Opción B: Descargar ZIP y extraer
unzip glpi-unread-main.zip
mv glpi-unread-main /var/www/html/glpi/plugins/unread
```

### 2. Permisos

```bash
cd /var/www/html/glpi/plugins/unread
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

### 3. Activar en GLPI

1. Acceder a GLPI como administrador
2. Ir a **Configuración** → **Plugins**
3. Buscar **"Unread Tracker"**
4. Hacer clic en **Activar**
5. Verificar que la tabla `glpi_plugin_unread_read` se crea automáticamente

---

## 📖 Uso

### Marcar Ticket como Leído

Una vez activado, el plugin proporciona métodos en la clase `PluginUnreadTracking`:

```php
// Marcar un ticket como leído por un usuario
PluginUnreadTracking::markAsRead(
    $tickets_id = 123,  // ID del ticket
    $users_id = 45      // ID del usuario técnico
);
```

### Verificar si un Ticket está No Leído

```php
// Retorna true si el ticket tiene actualizaciones no vistas
if (PluginUnreadTracking::isUnread($tickets_id, $users_id)) {
    echo "📌 Este ticket tiene cambios no consultados";
}
```

### Contar Tickets No Leídos por Usuario

```php
// Obtener cantidad de tickets no leídos asignados al técnico
$unread_count = PluginUnreadTracking::getUnreadCountForUser($users_id);
echo "Tienes $unread_count tickets pendientes de revisar";
```

---

## 🔧 Desarrollo y Fases Futuras

| Fase | Estado | Descripción |
|------|--------|-------------|
| **1. Scaffolding** | ✅ Completa | Setup inicial, instalador, clase base |
| **2. Frontend** | 🔄 Próxima | Dashboard de contadores, UI de badges, AJAX |
| **3. Integraciones** | 📅 Planificada | Notificaciones email, webhooks, API REST |
| **4. Optimizaciones** | 📅 Planificada | Caché de contadores, bulk operations |

---

## 📊 Esquema de Base de Datos

```sql
CREATE TABLE `glpi_plugin_unread_read` (
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

Este plugin se distribuye bajo **dos modelos de licencia**:

### Community Edition (AGPL-3.0)
- **Gratis** para uso en organizaciones pequeñas y startups
- Código abierto: toda mejora debe ser compartida
- Soporte comunitario vía issues de GitHub
- Ver archivo [LICENSE](LICENSE) para detalles completos

### Enterprise Edition
- **Licencia comercial propietaria**
- Sin obligación AGPL
- Soporte prioritario y SLA garantizado
- Implementación personalizada e integraciones

📧 **Para consultar sobre licencia Enterprise:** [terracenter@gmail.com](mailto:terracenter@gmail.com)

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas bajo la licencia AGPL-3.0.

1. Fork el repositorio
2. Crear rama `dev-tu-feature`
3. Hacer commits con formato: `feat(modulo): descripción`
4. Abrir Pull Request a `dev`
5. Validación y merge a `main` con versionado Asterisk

Ver [`CHANGES`](CHANGES) para historial de versiones.

---

## 🆘 Soporte

| Canal | Descripción |
|-------|-------------|
| **Issues** | Bugs y features (comunitario) |
| **Email** | terracenter@gmail.com (consultas comerciales) |
| **SLA** | Enterprise: respuesta en <24h |

---

## 📝 Changelog

Ver archivo [`CHANGES`](CHANGES) para resumen completo de versiones.

**Versión actual:** 1.0.0 (2026-06-23)

---

## 🏢 Sobre Humanbyte

Desarrollado por **Freddy Taborda** bajo [Humanbyte](https://humanbyte.io).
Especialistas en infraestructura, automation y plugins empresariales GLPI/ISP.

---

**¿Dudas? Abre un [issue](https://github.com/terracenter/glpi-unread/issues) o contacta directamente.**
