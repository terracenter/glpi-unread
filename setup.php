<?php
/**
 * GLPI Plugin Unread
 * Tracking unread tickets and updates for GLPI technicians
 *
 * @license GPLv3+
 * @author Freddy Taborda & Team
 */

function plugin_version_unread()
{
    return [
        'name'           => 'Unread Tracker',
        'version'        => '1.0.0',
        'author'         => 'Freddy Taborda & Team',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://github.com/terracenter/glpi-unread',
        'minGlpiVersion' => '10.0',
        'requirements'   => [
            'glpi' => ['min' => '10.0'],
            'php'  => ['min' => '7.4'],
        ],
    ];
}

function plugin_init_unread()
{
    // Plugin initialization hook
    // Register core hooks and assets
    return true;
}

function plugin_unread_check_prerequisites()
{
    $glpi_major = (int) explode('.', GLPI_VERSION)[0];

    // GLPI 10.x: PHP >= 7.4 | GLPI 11.x: PHP >= 8.2 (fuente: glpi-install.readthedocs.io)
    $php_min = ($glpi_major >= 11) ? '8.2' : '7.4';

    if (version_compare(PHP_VERSION, $php_min, '<')) {
        echo sprintf(
            __('PHP %s o superior requerido para GLPI %s.x', 'unread'),
            $php_min,
            $glpi_major
        );
        return false;
    }

    if (!version_compare(GLPI_VERSION, '10.0', '>=')) {
        echo __('Este plugin requiere GLPI 10.x o superior.', 'unread');
        return false;
    }

    return true;
}
