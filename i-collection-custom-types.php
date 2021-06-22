<?php

/**
 * Plugin Name: I-collection Custom Types
 * Description: Add Supplier post type and Wharehouse product taxonomy
 * Version: 1.0.0
 * Author: Иван Никитин и партнеры
 * Author URI: https://ivannikitin.com/
 * Text Domain: i-collection-custom-types
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/ivannikitin-com/i-collection-custom-types
 * GitHub Branch:     master
 * Requires WP:       5.0
 * Requires PHP:      5.3
 * Tested up to: 5.5.1
 *
 * @license   GPL-2.0+
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'WCDO_VERSION', '1.0.0' );
define ('TEXT_DOMAIN',TEXT_DOMAIN);

class ICollectionCustomTypes {

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @access  public
	 * @action wc_wholesale_prices_calculator_init
	 */
	public function __construct() {
		$plugin = plugin_basename( __FILE__ );
		add_action( 'plugins_loaded', array( $this, 'check_woocommerce' ), 9 );
		if( is_admin() ) {
			add_action( 'init', array( $this, 'reactivate_action'), 9999 );
			add_action( 'init', array( $this, 'load_plugin_textdomain') );
			register_activation_hook( __FILE__, array( 'ICollectionCustomTypes', 'install' ) );
			register_uninstall_hook( __FILE__, array( 'ICollectionCustomTypes', 'uninstall' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
		add_action( 'init', array($this,'register_supplier_post_type'), 0 );
		add_action( 'init', array($this,'register_product_wharehouse_taxonomy'), 0 );
		add_action( 'add_meta_boxes_product', array($this,'add_supplier_meta_box_to_product'), 10 );	 
	}	

	public function check_woocommerce() {
		if ( $this->is_woocommerce_activated() === false ) {
			$error = sprintf( __( 'I-collection Custom Types requires %sWooCommerce%s to be installed & activated!' , TEXT_DOMAIN ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
			$message = '<div class="error"><p>' . $error . '</p></div>';
			echo $message;
			return;
		}
	}	

	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Load the translation
	 *
	 * @since    1.0.0
	 * @access  public
	 * @filter plugin_locale
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, TEXT_DOMAIN );

		load_textdomain( TEXT_DOMAIN, trailingslashit( WP_LANG_DIR ) . 'i-collection-custom-types/i-collection-custom-types-' . $locale . '.mo' );
		load_plugin_textdomain( TEXT_DOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		wp_set_script_translations( 'i-collection-custom-types-script', TEXT_DOMAIN );
	}

	/**
	 * Setup Database on activating the plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	static public function install() {

	}

	/**
	 * Cleanup Database on deleting the plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	static public function uninstall() {
		
	}


	/**
	 *
	 * @since    1.1.0
	 * @access  public
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'i-collection-custom-types-script', plugins_url( '/js/i-collection-custom-types.js' , __FILE__ ), array( 'jquery'), WCDO_VERSION, true );
		wp_enqueue_style( 'i-collection-custom-types_admin_styles', plugins_url('admin-styles.css', __FILE__) );

    }

	// Register Supplier Post Type
	public function register_supplier_post_type() {

		$labels = array(
			'name'                  => _x( 'Suppliers', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'supplier', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Suppliers', 'text_domain' ),
			'name_admin_bar'        => __( 'supplier', 'text_domain' ),
			'archives'              => __( 'Supplier Archives', 'text_domain' ),
			'attributes'            => __( 'Supplier Attributes', 'text_domain' ),
			'parent_supplier_colon'     => __( 'Parent Supplier:', 'text_domain' ),
			'all_suppliers'             => __( 'All Suppliers', 'text_domain' ),
			'add_new_supplier'          => __( 'Add New Supplier', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_supplier'              => __( 'New Supplier', 'text_domain' ),
			'edit_supplier'             => __( 'Edit Supplier', 'text_domain' ),
			'update_supplier'           => __( 'Update Supplier', 'text_domain' ),
			'view_supplier'             => __( 'View Supplier', 'text_domain' ),
			'view_suppliers'            => __( 'View Suppliers', 'text_domain' ),
			'search_suppliers'          => __( 'Search Supplier', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_supplier'      => __( 'Insert into supplier', 'text_domain' ),
			'uploaded_to_this_supplier' => __( 'Uploaded to this supplier', 'text_domain' ),
			'suppliers_list'            => __( 'Suppliers list', 'text_domain' ),
			'suppliers_list_navigation' => __( 'Suppliers list navigation', 'text_domain' ),
			'filter_suppliers_list'     => __( 'Filter suppliers list', 'text_domain' ),
		);
		$args = array(
			'label'                 => __( 'Supplier', 'text_domain' ),
			'description'           => __( 'Product supplier', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor' ),
			'taxonomies'            => array( 'supplier_cat' ),
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'supplier', $args );

	}

	// Register Custom Taxonomy
	public function register_product_wharehouse_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Wharehouses', 'Taxonomy General Name', 'TEXT_DOMAIN' ),
			'singular_name'              => _x( 'Wharehouse', 'Taxonomy Singular Name', 'TEXT_DOMAIN' ),
			'menu_name'                  => __( 'Wharehouse', 'TEXT_DOMAIN' ),
			'all_items'                  => __( 'All wharehouses', 'TEXT_DOMAIN' ),
			'parent_item'                => __( 'Parent wharehouse', 'TEXT_DOMAIN' ),
			'parent_item_colon'          => __( 'Parent wharehouse:', 'TEXT_DOMAIN' ),
			'new_item_name'              => __( 'New wharehouse', 'TEXT_DOMAIN' ),
			'add_new_item'               => __( 'Add New wharehouse', 'TEXT_DOMAIN' ),
			'edit_item'                  => __( 'Edit wharehouse', 'TEXT_DOMAIN' ),
			'update_item'                => __( 'Update wharehouse', 'TEXT_DOMAIN' ),
			'view_item'                  => __( 'View wharehouse', 'TEXT_DOMAIN' ),
			'separate_items_with_commas' => __( 'Separate wharehouses with commas', 'TEXT_DOMAIN' ),
			'add_or_remove_items'        => __( 'Add or remove wharehouses', 'TEXT_DOMAIN' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'TEXT_DOMAIN' ),
			'popular_items'              => __( 'Popular wharehouse', 'TEXT_DOMAIN' ),
			'search_items'               => __( 'Search wharehouses', 'TEXT_DOMAIN' ),
			'not_found'                  => __( 'Not Found', 'TEXT_DOMAIN' ),
			'no_terms'                   => __( 'No wharehouse', 'TEXT_DOMAIN' ),
			'items_list'                 => __( 'Wharehouses list', 'TEXT_DOMAIN' ),
			'items_list_navigation'      => __( 'Wharehouses list navigation', 'TEXT_DOMAIN' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'product_wharehouse', array( 'product' ), $args );
	}

	public function add_supplier_meta_box_to_product($post){
		add_meta_box( 'supplier-meta-box', 'Supplier', 'supplier_meta_box_layout', array('product'), 'side',  'high', array('__back_compat_meta_box' => false));
	}

   public function supplier_meta_box_layout(){
   		echo "Select a supplier";
   }

}

$ICollectionCustomTypes = new ICollectionCustomTypes();
