<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    rbve
 * @subpackage rbve/includes
 * @author     Dan Duke <dan@retrobadger.net>
 */
class rbve_Deactivator {

    /**
     * Deactivate this plugin
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }

}