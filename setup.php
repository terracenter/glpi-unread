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
        'maxGlpiVersion' => '11.9',
        'requirements'   => [
            'php' => '8.0',
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
    // Check PHP version
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        echo 'PHP 8.0 or higher is required';
        return false;
    }

    // Check GLPI version if available
    if (defined('GLPI_VERSION')) {
        $glpiVersion = GLPI_VERSION;
        if (version_compare($glpiVersion, '10.0', '<') || version_compare($glpiVersion, '11.9', '>')) {
            echo 'GLPI version 10.0 to 11.9 is required';
            return false;
        }
    }

    return true;
}
