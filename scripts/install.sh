#!/usr/bin/env bash
# glpi-unread installer
# Instala y activa el plugin glpi-unread en una instancia GLPI 10.x/11.x
# Uso: sudo ./install.sh [--glpi-path /var/www/html/glpi] [--web-user www-data] [--admin-user glpi]

set -euo pipefail

# ---------- Valores por defecto ----------
GLPI_PATH="/var/www/html/glpi"
WEB_USER="www-data"
ADMIN_USER="glpi"
BRANCH="main"
REPO_URL="https://github.com/terracenter/glpi-unread.git"
PLUGIN_NAME="unread"

# ---------- Colores ----------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ok()   { echo -e "${GREEN}✅  $*${NC}"; }
warn() { echo -e "${YELLOW}⚠️   $*${NC}"; }
err()  { echo -e "${RED}❌  $*${NC}" >&2; exit 1; }

# ---------- Parsear argumentos ----------
while [[ $# -gt 0 ]]; do
    case "$1" in
        --glpi-path)   GLPI_PATH="$2";   shift 2 ;;
        --web-user)    WEB_USER="$2";    shift 2 ;;
        --admin-user)  ADMIN_USER="$2";  shift 2 ;;
        --branch)      BRANCH="$2";      shift 2 ;;
        --help|-h)
            echo "Uso: sudo $0 [opciones]"
            echo ""
            echo "Opciones:"
            echo "  --glpi-path   <ruta>    Ruta de instalación GLPI (default: /var/www/html/glpi)"
            echo "  --web-user    <usuario> Usuario del webserver (default: www-data)"
            echo "  --admin-user  <usuario> Usuario admin GLPI para CLI (default: glpi)"
            echo "  --branch      <rama>    Branch o tag del plugin a instalar (default: main)"
            echo ""
            exit 0
            ;;
        *) err "Argumento desconocido: $1. Usa --help para ver opciones." ;;
    esac
done

PLUGIN_DIR="$GLPI_PATH/plugins/$PLUGIN_NAME"

echo ""
echo "========================================"
echo " glpi-unread Installer"
echo "========================================"
echo " GLPI path   : $GLPI_PATH"
echo " Web user    : $WEB_USER"
echo " Admin user  : $ADMIN_USER"
echo " Branch      : $BRANCH"
echo "========================================"
echo ""

# ---------- Validaciones previas ----------
[[ $EUID -ne 0 ]] && err "Este script debe ejecutarse con sudo."

[[ -f "$GLPI_PATH/inc/based_config.php" ]] || \
    err "No se encontró GLPI en '$GLPI_PATH'. Verifica --glpi-path."

command -v php  >/dev/null 2>&1 || err "PHP no está disponible en el PATH."
command -v git  >/dev/null 2>&1 || err "Git no está disponible en el PATH."

# ---------- Detectar versión GLPI ----------
GLPI_VERSION=$(php -r "
    include('$GLPI_PATH/inc/based_config.php');
    echo GLPI_VERSION;
" 2>/dev/null) || err "No se pudo detectar GLPI_VERSION desde based_config.php."

GLPI_MAJOR=$(echo "$GLPI_VERSION" | cut -d. -f1)
ok "GLPI $GLPI_VERSION detectado (major: $GLPI_MAJOR)"

# ---------- Validar PHP mínimo según versión GLPI ----------
PHP_VERSION=$(php -r "echo PHP_VERSION;")

if [[ "$GLPI_MAJOR" -ge 11 ]]; then
    PHP_MIN="8.2"
else
    PHP_MIN="7.4"
fi

php -r "exit(version_compare(PHP_VERSION, '$PHP_MIN', '>=') ? 0 : 1);" || \
    err "PHP $PHP_MIN+ requerido para GLPI ${GLPI_MAJOR}.x. PHP actual: $PHP_VERSION"

ok "PHP $PHP_VERSION cumple requisito (>= $PHP_MIN para GLPI ${GLPI_MAJOR}.x)"

# ---------- Instalar o actualizar el plugin ----------
if [[ -d "$PLUGIN_DIR/.git" ]]; then
    warn "Plugin ya existe en $PLUGIN_DIR — actualizando..."
    git -C "$PLUGIN_DIR" fetch origin
    git -C "$PLUGIN_DIR" checkout "$BRANCH"
    git -C "$PLUGIN_DIR" pull origin "$BRANCH"
    ok "Plugin actualizado a branch '$BRANCH'"
else
    warn "Clonando plugin en $PLUGIN_DIR..."
    git clone --branch "$BRANCH" --depth 1 "$REPO_URL" "$PLUGIN_DIR"
    ok "Plugin clonado correctamente"
fi

# ---------- Aplicar permisos ----------
chown -R "$WEB_USER":"$WEB_USER" "$PLUGIN_DIR"
find "$PLUGIN_DIR" -type d -exec chmod 755 {} \;
find "$PLUGIN_DIR" -type f -exec chmod 644 {} \;
ok "Permisos aplicados (owner: $WEB_USER, dirs: 755, files: 644)"

# ---------- Instalar vía GLPI CLI ----------
echo ""
echo "Instalando plugin via GLPI CLI..."
sudo -u "$WEB_USER" php "$GLPI_PATH/bin/console" glpi:plugin:install \
    --username="$ADMIN_USER" "$PLUGIN_NAME" \
    && ok "Plugin instalado (tabla BD creada)" \
    || err "Error al instalar el plugin. Revisa: $GLPI_PATH/files/_log/php-errors.log"

# ---------- Activar vía GLPI CLI ----------
sudo -u "$WEB_USER" php "$GLPI_PATH/bin/console" glpi:plugin:activate "$PLUGIN_NAME" \
    && ok "Plugin activado correctamente" \
    || err "Error al activar el plugin. Revisa: $GLPI_PATH/files/_log/php-errors.log"

# ---------- Resumen final ----------
echo ""
echo "========================================"
ok "glpi-unread $BRANCH instalado y activo en GLPI $GLPI_VERSION"
echo " Verifica en GLPI: Configuración → Plugins → Unread Tracker"
echo " Tabla creada: glpi_plugin_unread_read"
echo "========================================"
echo ""
