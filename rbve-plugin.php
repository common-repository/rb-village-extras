<?php
/*
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name: RB Village Extras (rbve)
 * Depends: CMB2
 * Description: Some extra functionality for village websites. Depends on: cmb2
 * Version: 1.1.10
 * Author: Dan Duke
 * Author URI: http://www.retrobadger.net
 * License: GPL-3.0
 * License URI: https://opensource.org/licenses/GPL-3.0
*/

// This plugin depends upon the cmb2 plugin being also installed
if ( defined( 'CMB2_LOADED' ) ) {

// Boilerplate example: https://github.com/DevinVinson/WordPress-Plugin-Boilerplate

// If this file is called directly, abort.
    if (!defined('WPINC')) {
        die;
    }

    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-plugin-name-activator.php
     */
    function activate_rbve()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/class-rbve-activator.php';
        rbve_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-plugin-name-deactivator.php
     */
    function deactivate_rbve()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/class-rbve-deactivator.php';
        rbve_Deactivator::deactivate();
    }

    register_activation_hook(__FILE__, 'activate_rbve');
    register_deactivation_hook(__FILE__, 'deactivate_rbve');

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path(__FILE__) . 'includes/class-rbve.php';

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_rbve()
    {

        $plugin = new rbve();
        $plugin->run();

    }

    run_rbve();
}
else {
    if ( is_admin() ) {
        print '<div class="error"><p>The cmb2 plugin is required for rbve to work</p></div>';
    }
}
