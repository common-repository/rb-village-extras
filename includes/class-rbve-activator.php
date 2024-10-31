<?php

/**
 * Fired during plugin activation
 *
 * @link       http://retrobadger.net
 * @since      1.0.0
 *
 * @package    rbve
 * @subpackage rbve/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    rbve
 * @subpackage rbve/includes
 * @author     Dan Duke <dan@retrobadger.net>
 */
class rbve_Activator {

    /**
     * Activate this plugin
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Load in the admin object so we can use its methods
        require_once plugin_dir_path(__FILE__) . '../admin/class-rbve-admin.php';
        rbve_Admin::rbve_create_post_types();

        flush_rewrite_rules();
    }

}
