<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    rbve
 * @subpackage rbve/includes
 * @author     Dan Duke <dan@retrobadger.net>
 */
class rbve_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $plugin_path;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_path = substr(plugin_dir_path(__FILE__), 0, -6);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rbve-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rbve-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Create the new post types for village extra
	 *   - Village Documents
	 *   - Local Businesses
	 */
	public static function rbve_create_post_types() {
		// Post type: Village Documents
		register_post_type( 'rbve_doc',
			array(
				'labels' => array(
					'name' => __( 'Village Documents' ),
					'singular_name' => __( 'Village Document' ),
					'add_new_item' => __( ' Add new Village Document ' ),
					'edit_item' => __( ' Edit Village Document ' ),
					'new_item' => __( ' New Village Document ' ),
					'view_item' => __( ' View Village Document ' ),
					'search_item' => __( ' Search Village Document ' ),
				),
				'menu_icon' => 'dashicons-media-document',
				'supports' => array(
					'title',
					'author',
					'revisions',
				),
				'map_meta_cap' => true, // map_meta_cap will allow us to remap the existing capabilities with new capabilities to match the new custom post type
				'capabilities' => array(
					'publish_posts' => 'rbve_docs_publish',
					'edit_posts' => 'rbve_docs_edit',
					'edit_published_posts' => 'rbve_docs_edit_published',
					'edit_others_posts' => 'rbve_docs_edit_others',
					'delete_posts' => 'rbve_docs_delete',
					'delete_others_posts' => 'rbve_docs_delete_others',
					'edit_post' => 'rbve_doc_edit',
					'delete_post' => 'rbve_doc_delete',
					'read_post' => 'rbve_doc_read',
				),
				'capability_type' => array('village-doc','village-docs'),
				'public' => true,
				'has_archive' => false,
				'rewrite' => array('slug' => 'village-docs'),
				'hierarchical'        => true,
			)
		);
		// Post type: Local Business/organisation
		register_post_type( 'rbve_business',
			array(
				'labels' => array(
					'name' => __( 'Local Businesses/Organisations' ),
					'singular_name' => __( 'Local Business/Organisation' ),
				),
				'menu_icon' => 'dashicons-store',
				'supports' => array(
					'title',
					'author',
					'revisions',
				),
				'map_meta_cap' => true, // map_meta_cap will allow us to remap the existing capabilities with new capabilities to match the new custom post type
				'capability_type' => array('business','businesses'), // Sets the text to use for the new capabilities (replaces post)
				'public' => true,
				'has_archive' => false,
				'rewrite' => array('slug' => 'business'),
				'hierarchical' => true,
			)
		);
		// Post type: Local Club/Society
		register_post_type( 'rbve_clubs_societies',
			array(
				'labels' => array(
					'name' => __( 'Local Clubs/Societies' ),
					'singular_name' => __( 'Local Club/Society' ),
				),
				'menu_icon' => 'dashicons-groups',
				'supports' => array(
					'title',
					'author',
					'revisions',
				),
				'map_meta_cap' => true, // map_meta_cap will allow us to remap the existing capabilities with new capabilities to match the new custom post type
				'capability_type' => array('clubsociety','clubssocieties'),
				'public' => true,
				'has_archive' => false,
				'rewrite' => array('slug' => 'clubsociety'),
				'hierarchical' => true,
			)
		);
	}

	/**
	 * Create filters for village extras to change how the content is displayed for our new post types
	 *
	 * @param $content
	 * @return string
	 */
	public function rbve_content_filter( $content ) {
		// Load the post for the page, as we will need it for the meta data
		global $post;

		// We are only going to tweak when displaying this item on its own
		if ( is_single() ) {
			// Load the metadata for this post
			$post_meta = get_post_meta($post->ID);

			// If a document has been uploaded for this type, then we will list it, otherwise, we do not display anything
			if (!empty($post_meta['_rbve_doc'][0])) {
				$content .= '<a href="' . $post_meta['_rbve_doc'][0] . '">Download: ' . get_the_title() . '</a>';
			};
		}

		// Returns the content.
		return $content;
	}

	/**
	 * Custom handler to return the url for our new custom posts
	 *
	 * @param $output
	 * @return mixed
	 */
	function rbve_get_post_url($output) {
		global $post;
		switch ($post->post_type) {
			case 'rbve_doc':
				// Load the metadata for this post
				$post_meta = get_post_meta($post->ID);
				return $post_meta['_rbve_doc'][0];
				break;
			default:
				return $output;
		}
	}

	/**
	 * Add custom post types to the homepage list
	 *
	 * @param $query
	 * @return mixed
	 */
	public function add_my_post_types_to_query( $query ) {
		if ( is_home() && $query->is_main_query() ) {
			$query->set('post_type', array('post', 'rbve_doc'));
		}
		return $query;
	}

	/**
	 * Configure settings/help menus for Documents post type
	 */
	public function rbve_docs_settings_menu()
	{
		// We are in admin mode
		$page_title = 'Village Document Settings and Information';
		$menu_title = 'Settings/Info';
		$capability = 'rbve_admin';
		$menu_slug = 'admin-page';
		$function = 'rbve_admin_page';
		$hook = add_submenu_page('edit.php?post_type=rbve_doc', $page_title, $menu_title, $capability, $menu_slug, $function);
	}

	/**
	 * Helper function to create the new post type
	 */
	public function rbve_doctypes_init() {
		wp_register_style( 'rbve', plugins_url('../css/rbve.css',__FILE__ ) );
		wp_enqueue_style('rbve');

		$doctype_vocab_name = 'rbve_tax_doctypes';
		// create a new taxonomy for document types
		register_taxonomy(
				$doctype_vocab_name,
				'None',
				array(
						'label' => __( 'Village Document Types' ),
						'rewrite' => array( 'slug' => 'village-document-types' ),
				)
		);
		// Setup an array of default terms
		$doctype_terms = array(
				'annual-council-minutes' => 'Annual Council Minutes',
				'council-agenda' => 'Council Agenda',
				'council-minutes' => 'Council Minutes',
				'special-council-minutes' => 'Special Council Minutes',
				'newsletter' => 'Newsletter',
				'county-councillors-report' => 'County Councillors Report',
				'district-councillors-report' => 'District Councillors Report',
				'county-council-annual-minutes' => 'County Council Annual Minutes',
				'village-hall-agenda' => 'Village Hall Agenda',
				'village-hall-minutes' => 'Village Hall Minutes',
		);
		// Loop through the terms array, and create the terms
		foreach ($doctype_terms as $key => &$term) {
			// Do not add the term if it already exists
			$parent_term = term_exists( $doctype_vocab_name, $term );
			if(!$parent_term) {
				wp_insert_term($term, $doctype_vocab_name, $args = array(
						'slug' => $key
				));
			}
		}
	}

	/**
	 * Helper function to create the new facilities taxonomy
	 */
	public function rbve_facilities_services_init() {
		$doctype_vocab_name = 'rbve_tax_facilities_services';
		// create a new taxonomy for document types
		register_taxonomy(
			$doctype_vocab_name,
			'None',
			array(
				'label' => __( 'RBVE Facilities/Services' ),
				'rewrite' => array( 'slug' => 'rbve-facilities-services' ),
			)
		);
		// Setup an array of default terms
		$doctype_terms = array(
			'dog-friendly' => 'Dog Friendly', //Fontawesome - paws
			'food-served' => 'Food Served', //Fontawesome - cutlery
			'alcohol-served' => 'Alcohol Served', //Fontawesome - beer
			'drinks-served' => 'Hot Drinks Served', //Fontawesome - tea
			'accommodation' => 'Accommodation', //Fontawesome - bed
			'home-visits-deliveries' => 'Home Visits/Deliveries', //Fontawesome - bed
		);
		// Loop through the terms array, and create the terms
		foreach ($doctype_terms as $key => &$term) {
			// Do not add the term if it already exists
			$parent_term = term_exists( $doctype_vocab_name, $term );
			if(!$parent_term) {
				wp_insert_term($term, $doctype_vocab_name, $args = array(
					'slug' => $key
				));
			}
		}
	}

	/**
	 * Helper function to create the new businesstype taxonomy
	 *
	 * Terms take from: https://www.yell.com/
	 */
	public function rbve_businesstype_init() {
		$doctype_vocab_name = 'rbve_tax_businesstype';
		// create a new taxonomy for document types
		register_taxonomy(
			$doctype_vocab_name,
			'None',
			array(
				'label' => __( 'RBVE Business Types' ),
				'rewrite' => array( 'slug' => 'rbve-businesstype' ),
			)
		);
		// Setup an array of default terms
		$doctype_terms = array(
			'accountants' => 'Accountants',
			'architects' => 'Architects',
			'builders' => 'Builders',
			'butchers' => 'Butchers',
			'cafe-coffee-shop' => 'Cafes & Coffee Shops',
			'carpenters-joiners' => 'Carpenters & Joiners',
			'charitable-voluntary-org' => 'charitable & voluntary organisations',
			'day-nurseries' => 'Day Nurseries',
			'dog-walking' => 'Dog walking',
			'domestic-cleaning' => 'Domestic Cleaning',
			'driving-schools' => 'Driving Schools',
			'educations-services' => 'educational services',
			'electricians' => 'Electricians & Electrical Contractors',
			'estate-agents' => 'Estate Agents',
			'fencing-services' => 'Fencing Services',
			'fishmongers' => 'Fishmongers',
			'florists' => 'Florists',
			'function-rooms' => 'Function Rooms & Banqueting',
			'furniture-shops' => 'Furniture and Homewares',
			'garden-services' => 'Garden Services',
			'gas-engineers' => 'Gas Engineers',
			'grocers-convenience-stores' => 'Grocers & Convenience Stores',
			'hairdressers' => 'Hairdressers',
			'handyman-services' => 'handyman services',
			'healthcare-wellbeing' => 'Healthcare and wellbeing',
			'hotels-inns' => 'Hotels & Inns',
			'marketing' => 'Marketing',
			'painters-decorators' => 'Painters & Decorators',
			'pet-services' => 'Pet services',
			'pharmacies' => 'Pharmacies',
			'plumbers' => 'Plumbers',
			'solicitors' => 'Solicitors',
			'sports-rec-grounds' => 'Sports and Recreation Grounds',
			'takeaway-food' => 'Takeaway Food',
			'taxis-private-transport' => 'Taxis & private transport services',
			'web-design-dev' => 'web design & development',
			'window-cleaners' => 'Window Cleaners',
		);
		// Loop through the terms array, and create the terms
		foreach ($doctype_terms as $key => &$term) {
			// Do not add the term if it already exists
			$parent_term = term_exists( $doctype_vocab_name, $term );
			if(!$parent_term) {
				wp_insert_term($term, $doctype_vocab_name, $args = array(
					'slug' => $key
				));
			}
		}
	}

	public function rbve_templates($single) {
		global $wp_query, $post;

		/* Checks for single template by post type */
		if ($post->post_type == "rbve_business"){
			if(file_exists($this->plugin_path . 'templates/colorway/single-rbve_business.php'))
				return $this->plugin_path . 'templates/colorway/single-rbve_business.php';
		}
		else if ($post->post_type == "rbve_clubs_societies"){
			if(file_exists($this->plugin_path . 'templates/colorway/single-rbve_clubs_societies.php'))
				return $this->plugin_path . 'templates/colorway/single-rbve_clubs_societies.php';
		}
		return $single;
	}

	/**
	 * Add new capabilities used by village extras
	 */
	public function rbve_add_capabilities() {
		// gets the super admin role
		$role_admin = get_role( 'administrator' );
		$role_editor = get_role( 'editor' );

		// Create an array of admin permissions we will want to give
		$caps_admin = array(
			'rbve_docs_publish',
			'rbve_docs_edit',
			'rbve_docs_edit_others',
			'rbve_docs_edit_published',
			'rbve_docs_delete',
			'rbve_docs_read',
			'rbve_docs_delete_others',
			'rbve_admin',
			'publish_businesses',
			'edit_businesses',
			'edit_published_businesses',
			'edit_others_businesses',
			'delete_businesses',
			'delete_others_businesses',
			'edit_business',
			'delete_business',
			'read_business',
			'publish_clubssocieties',
			'edit_clubssocieties',
			'edit_published_clubssocieties',
			'edit_others_clubssocieties',
			'delete_clubssocieties',
			'delete_others_clubssocieties',
			'edit_clubsociety',
			'delete_clubsociety',
			'read_clubsociety',
		);

		$caps_editor = array(
			'rbve_docs_publish',
			'rbve_docs_edit',
			'rbve_docs_edit_others',
			'rbve_docs_edit_published',
			'rbve_docs_read',
			'rbve_business_publish',
			'rbve_business_edit',
			'rbve_business_edit_others',
			'rbve_business_edit_published',
			'rbve_business_read',
			'rbve_clubsociety_publish',
			'rbve_clubsociety_edit',
			'rbve_clubsociety_edit_others',
			'rbve_clubsociety_edit_published',
			'rbve_clubsociety_read',
		);

		// Loop through all the admin permissions, and give them to our admin role
		foreach ($caps_admin as $cap) {
			$role_admin->add_cap($cap);
		}

		// Loop through all the editor permissions, and give them to our editor role
		foreach ($caps_editor as $cap) {
			$role_editor->add_cap($cap);
		}

	}

	/**
	 * Create some new metaboxes for our custom post types
	 */
	public function rbve_docs_metaboxes() {
		// Setup a metabox group for the docs to use (file and doctype)
		$prefix = '_rbve_';

		// Teaser and body
		$cmb_teaserbody = new_cmb2_box( array(
			'id'            => $prefix . 'teaserbody',
			'title'         => __( 'Teaser and Body', 'cmb2' ),
			'object_types'  => array( 'rbve_business', 'rbve_clubs_societies' ), // Post types to add this box to
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );
		$cmb_teaserbody->add_field(array(
			'name'    => 'Teaser',
			'desc'    => 'Some intro text, 1 sentence',
			'id'      => $prefix . 'teaser',
			'type'    => 'textarea',
		));
		$cmb_teaserbody->add_field(array(
			'name'    => 'Body',
			'desc'    => 'The main body of text',
			'id'      => $prefix . 'body',
			'type'    => 'wysiwyg',
		));

		// rbve_docs
		$cmb = new_cmb2_box( array(
				'id'            => $prefix . 'docs',
				'title'         => __( 'Docs', 'cmb2' ),
				'object_types'  => array( 'rbve_doc', ), // Post types to add this box to
				'context'       => 'normal',
				'priority'      => 'high',
				'show_names'    => true, // Show field names on the left
		) );
		// Add the file to the document metabox
		$cmb->add_field(array(
				'name'    => 'Document',
				'desc'    => 'Upload Document',
				'id'      => $prefix . 'doc',
				'type'    => 'file',
				'options' => array(
						'url' => false, // Hide the text input for the url
						'add_upload_file_text' => 'Add Doc' // Change upload button text. Default: "Add or Upload File"
				),
				'attributes'  => array(
						'required'    => 'required',
				),
		));
		// Add the doctype to the document metabox
		$cmb->add_field(array(
				'name'     => 'Document type',
				'desc'     => 'Select the document types that best match this document',
				'id'       => $prefix . 'doctype',
				'taxonomy' => 'rbve_tax_doctypes', // Enter Taxonomy Slug
				'type'     => 'taxonomy_multicheck',
				'select_all_button' => false,
				'options' => array(
						'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
				),
		));
		// Add a date to the document metabox (this will be used instead of published date when set)
		$cmb->add_field(array(
				'name' => 'Document sort date',
				'desc' => 'A date field for sorting content, and used as labels (handy for publishing something before the date)',
				'id' => $prefix . 'sortdate',
				'type' => 'text_date_timestamp',
				'attributes'  => array(
						'required'    => 'required',
				),
		));

		// rbve_business, rbve_socialgroup, rbve_organisation


		// Contact details
		$cmb_contact = new_cmb2_box( array(
			'id'            => $prefix . 'contact',
			'title'         => __( 'Contact', 'cmb2' ),
			'object_types'  => array( 'rbve_business', 'rbve_clubs_societies' ), // Post types to add this box to
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Name of contact',
			'desc'     => 'Name of contact (if an individual)',
			'id'       => $prefix . 'contactname',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Role of contact',
			'desc'     => 'Role of contact (if an individual)',
			'id'       => $prefix . 'contactrole',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Email',
			'desc'     => 'Contact email',
			'id'       => $prefix . 'contactemail',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text_email',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Telephone',
			'desc'     => 'Contact telephone',
			'id'       => $prefix . 'contactphone',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Website',
			'desc'     => 'Website',
			'id'       => $prefix . 'contactwebsite',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text_url',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Facebook',
			'desc'     => 'Facebook url',
			'id'       => $prefix . 'contactfacebook',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text_url',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: Twitter',
			'desc'     => 'Twitter handle (eg. your username not including the @ symbol)',
			'id'       => $prefix . 'contacttwitter',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Contact: YouTube',
			'desc'     => 'YouTube URL',
			'id'       => $prefix . 'contactyoutube',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));

		$cmb_contact = new_cmb2_box( array(
			'id'            => $prefix . 'address',
			'title'         => __( 'Address', 'cmb2' ),
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );
		$cmb_contact->add_field(array(
			'name'     => 'House name/number',
			'desc'     => 'House name/number',
			'id'       => $prefix . 'addresshousenumber',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Street',
			'desc'     => 'Street',
			'id'       => $prefix . 'addressstreet',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Town/Village',
			'desc'     => 'Town/Village',
			'id'       => $prefix . 'addresstownvillage',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));
		$cmb_contact->add_field(array(
			'name'     => 'Postcode',
			'desc'     => 'Postcode',
			'id'       => $prefix . 'addresspostcode',
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'type'     => 'text',
		));

		$cmb_facilities = new_cmb2_box( array(
			'id'            => $prefix . 'business-settings',
			'title'         => __( 'Facilities', 'cmb2' ),
			'object_types'  => array( 'rbve_business', 'rbve_clubs_societies' ), // Post types to add this box to
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );
		$cmb_facilities->add_field(array(
			'name'     => 'Logo',
			'desc'     => '200x200 pixel logo',
			'id'       => $prefix . 'logo',
			'taxonomy' => 'rbve_tax_logo', // Enter Taxonomy Slug
			'type'     => 'file',
			'select_all_button' => false,
			'options' => array(
				'url' => false, // Hide the text input for the url
			),
			'text'    => array(
				'add_upload_file_text' => 'Add Logo' // Change upload button text. Default: "Add or Upload File"
			),
		));
		$cmb_facilities->add_field(array(
			'name'     => 'Facilites/Services',
			'desc'     => 'Select the facilities/services available',
			'id'       => $prefix . 'facilities_services',
			'taxonomy' => 'rbve_tax_facilities_services', // Enter Taxonomy Slug
			'type'     => 'taxonomy_multicheck',
			'select_all_button' => false,
			'options' => array(
				'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
			),
		));
		$cmb_facilities->add_field(array(
			'name'     => 'Primary Business type',
			'desc'     => 'Select the most relevant business type',
			'id'       => $prefix . 'businesstype',
			'taxonomy' => 'rbve_tax_businesstype', // Enter Taxonomy Slug
			'type'     => 'taxonomy_select',
			'select_all_button' => false,
			'options' => array(
				'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
			),
		));

		$cmb_openinghours = new_cmb2_box( array(
			'id'            => $prefix . 'openinghours',
			'title'         => __( 'Opening Hours', 'cmb2' ),
			'object_types'  => array( 'rbve_business', ), // Post types to add this box to
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
			'closed'     => true,
		) );
		$cmb_openinghours->add_field(array(
			'name'     => 'Monday Opening',
			'id'       => $prefix . 'openingmonday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Monday Closing',
			'id'       => $prefix . 'openingmonday2',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Tuesday Opening',
			'id'       => $prefix . 'openingtuesday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Tuesday Closing',
			'id'       => $prefix . 'openingtuesday2',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Wednesday Opening',
			'id'       => $prefix . 'openingwednesday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Wednesday Closing',
			'id'       => $prefix . 'openingwednesday2',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Thursday Opening',
			'id'       => $prefix . 'openingthursday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Thursday Closing',
			'id'       => $prefix . 'openingthursday2',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Friday Opening',
			'id'       => $prefix . 'openingfriday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Friday Closing',
			'id'       => $prefix . 'openingfriday2',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Saturday Opening',
			'id'       => $prefix . 'openingsaturday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Saturday Closing',
			'id'       => $prefix . 'openingsaturday2',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Sunday Opening',
			'id'       => $prefix . 'openingsunday',
			'type'     => 'text_time',
		));
		$cmb_openinghours->add_field(array(
			'name'     => 'Sunday Closing',
			'id'       => $prefix . 'openingsunday2',
			'type'     => 'text_time',
		));

		$cmb_facilities = new_cmb2_box( array(
			'id'            => $prefix . 'club-extras',
			'title'         => __( 'Extras', 'cmb2' ),
			'object_types'  => array( 'rbve_clubs_societies' ), // Post types to add this box to
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );
		$cmb_facilities->add_field(array(
			'name'    => 'Dates/Times',
			'desc'    => 'When do you meet?',
			'id'      => $prefix . 'datestimes',
			'type'    => 'textarea',
		));
		$cmb_facilities->add_field(array(
			'name'     => 'Venue',
			'id'       => $prefix . 'venue',
			'type'     => 'text',
		));

	}

	public function enqueue_our_required_stylesheets(){
		wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
	}

	public function rbve_image_sizes() {
		add_image_size( 'rbve_business_logo', 300 );
	}

}

/**
 * Seperate function outside of the main object to handle the content on our admin page
 */
function rbve_admin_page() {
	$title = '<h2>Village Document Settings and Information</h2>';

	$intro = '<p>This page is largely available to give information to the content editors.  But it may also house settings in the future if/when we feel it is useful to add some.</p>';

	$about_the_type = '<h3>About the type</h3><p>The village documents type is available so you can upload the various documents which are commonplace in a village.  It also comes with useful shortcodes so you can quickly list out these documents onto any page within your site, allowing simple upload once, making the documents visible automatically in all the places you expect them to appear.</p>';

	$doc_types = '<h3>Default doc types</h3><ul><li>Council Minutes</li><li>Newsletters</li><li>County Councillors Report</li><li>District Councillors Report</li></ul>';

	$usage = '<h3>Usage</h3><p>To use these documents, simply upload a new document, and tag it appropriately, using the doc types available.  To add a new doctype, simply go to <a href="/wp-admin/edit-tags.php?taxonomy=rbve_tax_doctypes">the types admin page</a> (it is a taxonomy), and add the new items you would like.  Although, in mist cases, please suggest to me, as it would be good to get some of these into the core module for others to use.</p>';

	$shortcodes = '<h3>Using the shortcodes</h3><h4>[rbve_docs]</h4><p>DEFAULT lists all village documents in the site, in DESC order of published date, grouped by year</p><ul>';
	$shortcodes .= '<li>labeltype (monthyear|year) - the label is the month and year the document was published, rather than the post name</li>';
	$shortcodes .= '<li>doctypes (eg. council-minutes) - Filters the results by the doctype(s) (slug) supplied (you can seperate multiple with a comma)</li>';
	$shortcodes .= '<li>limit-year (eg. 2016) - Filter the items by year (eg. only show items for 2016)</li>';
	$shortcodes .= '<li>showtype (disabled) - Disable the filetype display</li>';
	$shortcodes .= '<li>grouptitle (disabled) - Disable the title display</li>';
	$shortcodes .= '</ul>';

	$shortcodes .= '<h4>[rbve_business_directory]</h4><p>DEFAULT lists all businesses</p><ul>';
	$shortcodes .= '<li>businesstypes (monthyear|year) - Only lists businesses of the chosen type (eg. [rbve_business businesstypes="public-house-restaurant"])</li>';
	$shortcodes .= '</ul>';

	$shortcodes .= '<h4>[rbve_clubs_directory]</h4><p>DEFAULT lists all clubs/societies</p><ul>';
	$shortcodes .= '</ul>';

	$useful_links = '<h3>Some useful links:</h3><ul>';
	$useful_links .= '<li><a href="https://www.crimereports.co.uk/#">UK Local crime reports (with email alerts)</a></li>';
	$useful_links .= '</ul>';

	print $title . $intro . $about_the_type . $doc_types . $usage . $shortcodes . $useful_links . '<br />';
}
