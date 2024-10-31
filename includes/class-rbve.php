<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.retrobadger.net
 * @since      1.0.0
 *
 * @package    rbve
 * @subpackage rbve/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    rbve
 * @subpackage rbve/includes
 * @author     Dan Duke <dan@retrobadger.net>
 */
class rbve {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'rbve';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
     * - Plugin_Name_i18n. Defines internationalization functionality.
     * - Plugin_Name_Admin. Defines all hooks for the admin area.
     * - Plugin_Name_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rbve-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rbve-admin.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/custom_shortcodes.php';

        $this->loader = new rbve_Loader();

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new rbve_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $plugin_admin, 'rbve_create_post_types' );

        // Setup taxonomies
        $this->loader->add_action( 'init', $plugin_admin, 'rbve_doctypes_init' );
        $this->loader->add_action( 'init', $plugin_admin, 'rbve_facilities_services_init' );
        $this->loader->add_action( 'init', $plugin_admin, 'rbve_businesstype_init' );

        $this->loader->add_filter( 'single_template', $plugin_admin, 'rbve_templates' );

        $this->loader->add_action( 'pre_get_posts', $plugin_admin, 'add_my_post_types_to_query' );

        $this->loader->add_action( 'after_setup_theme', $plugin_admin, 'rbve_image_sizes', 20 );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_our_required_stylesheets', 20 );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'rbve_docs_settings_menu' );

        $this->loader->add_filter( 'the_content', $plugin_admin, 'rbve_content_filter', 20 );

        $this->loader->add_action( 'admin_init', $plugin_admin, 'rbve_add_capabilities' );

        $this->loader->add_action( 'cmb2_admin_init', $plugin_admin, 'rbve_docs_metaboxes' );

        $this->loader->add_filter( 'post_type_link', $plugin_admin, 'rbve_get_post_url', 20 );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}

function rbve_doc_template($title, $link) {
    $output = '<h2><a href="' . $link . '">' . $title . '</a></h2><p><a href="' . $link . '">Download document</a></p>';
    return $output;
}