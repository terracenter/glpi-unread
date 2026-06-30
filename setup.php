<?php
/**
 * GLPI Plugin Unread
 * Tracking unread tickets and updates for GLPI technicians
 *
 * @license GPLv3+
 * @author Freddy Taborda & Team
 */

use Glpi\Plugin\Hooks;
use GlpiPlugin\Unreadtracker\Tracking;

define('PLUGIN_UNREADTRACKER_VERSION', '1.2.0');

function plugin_version_unreadtracker()
{
    return [
        'name'           => 'Terracenter - Unread Tracker',
        'version'        => PLUGIN_UNREADTRACKER_VERSION,
        'author'         => 'Freddy Taborda & Team',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://github.com/terracenter/glpi-unread',
        'requirements'   => [
            'glpi' => ['min' => '10.0'],
            'php'  => ['min' => '7.4'],
        ],
    ];
}

function plugin_init_unreadtracker()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['unreadtracker']  = true;
    $PLUGIN_HOOKS[Hooks::DISPLAY_CENTRAL]['unreadtracker'] = [Tracking::class, 'displayCentral'];
    $PLUGIN_HOOKS[Hooks::ITEM_UPDATE]['unreadtracker']     = ['Ticket' => 'plugin_unreadtracker_item_update'];
    $PLUGIN_HOOKS[Hooks::ADD_CSS]['unreadtracker']         = 'css/unread.css';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['unreadtracker']  = 'js/unread.js';
}

function plugin_unreadtracker_check_prerequisites()
{
    $glpi_major = (int) explode('.', GLPI_VERSION)[0];

    // GLPI 10.x: PHP >= 7.4 | GLPI 11.x: PHP >= 8.2
    $php_min = ($glpi_major >= 11) ? '8.2' : '7.4';

    if (version_compare(PHP_VERSION, $php_min, '<')) {
        echo sprintf(
            __('PHP %s o superior requerido para GLPI %s.x', 'unreadtracker'),
            $php_min,
            $glpi_major
        );
        return false;
    }

    if (!version_compare(GLPI_VERSION, '10.0', '>=')) {
        echo __('Este plugin requiere GLPI 10.x o superior.', 'unreadtracker');
        return false;
    }

    return true;
}

function plugin_unreadtracker_check_config($verbose = false)
{
    return true;
}
