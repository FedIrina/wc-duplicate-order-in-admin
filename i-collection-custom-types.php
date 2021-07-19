<?php

/**
 * Plugin Name: I-collection Custom Types
 * Description: Add Supplier post type and Location product taxonomy
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

define( 'ICCT_VERSION', '1.0.0' );
define ('TEXT_DOMAIN','i-collection-custom-types');

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
		add_action( 'plugins_loaded', array( $this, 'load_textdomain1') );
		if( is_admin() ) {
			register_activation_hook( __FILE__, array( 'ICollectionCustomTypes', 'install' ) );
			register_uninstall_hook( __FILE__, array( 'ICollectionCustomTypes', 'uninstall' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
		add_action( 'init', array($this,'register_supplier_post_type'), 0 );
		add_action( 'init', array($this,'register_product_location_taxonomy'), 0 );
		add_action( 'add_meta_boxes_product', array($this,'add_supplier_meta_box_to_product'), 10 );
		add_action( 'save_post', array($this,'i_collection_save_postdata') );	 
	}	

	public function check_woocommerce() {
		if ( $this->is_woocommerce_activated() === false ) {
			$error = sprintf( __( 'I-collection Custom Types requires %sWooCommerce%s to be installed & activated!' , 'i-collection-custom-types' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
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
	public function load_textdomain1() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'i-collection-custom-types' );

		load_plugin_textdomain( 'i-collection-custom-types', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
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
		wp_enqueue_script( 'i-collection-custom-types-script', plugins_url( '/js/i-collection-custom-types.js' , __FILE__ ), array( 'jquery'), ICCT_VERSION, true );
		wp_enqueue_style( 'i-collection-custom-types_admin_styles', plugins_url('admin-styles.css', __FILE__) );
		wp_set_script_translations( 'i-collection-custom-types-script', 'i-collection-custom-types' );

    }

	// Register Supplier Post Type
	public function register_supplier_post_type() {

		$labels = array(
			'name'                  => _x( 'Suppliers', 'Post Type General Name', 'i-collection-custom-types' ),
			'singular_name'         => _x( 'supplier', 'Post Type Singular Name', 'i-collection-custom-types' ),
			'menu_name'             => _x( 'Suppliers', 'Post Type Menu Name','i-collection-custom-types' ),
			'name_admin_bar'        => __( 'supplier', 'i-collection-custom-types' ),
			'archives'              => __( 'Supplier Archives', 'i-collection-custom-types' ),
			'attributes'            => __( 'Supplier Attributes', 'i-collection-custom-types' ),
			'parent_supplier_colon'     => __( 'Parent Supplier:', 'i-collection-custom-types' ),
			'all_suppliers'             => __( 'All Suppliers', 'i-collection-custom-types' ),
			'add_new_supplier'          => __( 'Add New Supplier', 'i-collection-custom-types' ),
			'add_new'               => __( 'Add New', 'i-collection-custom-types' ),
			'new_supplier'              => __( 'New Supplier', 'i-collection-custom-types' ),
			'edit_supplier'             => __( 'Edit Supplier', 'i-collection-custom-types' ),
			'update_supplier'           => __( 'Update Supplier', 'i-collection-custom-types' ),
			'view_supplier'             => __( 'View Supplier', 'i-collection-custom-types' ),
			'view_suppliers'            => __( 'View Suppliers', 'i-collection-custom-types' ),
			'search_suppliers'          => __( 'Search Supplier', 'i-collection-custom-types' ),
			'not_found'             => __( 'Not found', 'i-collection-custom-types' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'i-collection-custom-types' ),
			'featured_image'        => __( 'Featured Image', 'i-collection-custom-types' ),
			'set_featured_image'    => __( 'Set featured image', 'i-collection-custom-types' ),
			'remove_featured_image' => __( 'Remove featured image', 'i-collection-custom-types' ),
			'use_featured_image'    => __( 'Use as featured image', 'i-collection-custom-types' ),
			'insert_into_supplier'      => __( 'Insert into supplier', 'i-collection-custom-types' ),
			'uploaded_to_this_supplier' => __( 'Uploaded to this supplier', 'i-collection-custom-types' ),
			'suppliers_list'            => __( 'Suppliers list', 'i-collection-custom-types' ),
			'suppliers_list_navigation' => __( 'Suppliers list navigation', 'i-collection-custom-types' ),
			'filter_suppliers_list'     => __( 'Filter suppliers list', 'i-collection-custom-types' ),
		);
		$args = array(
			'label'                 => __( 'Supplier', 'i-collection-custom-types' ),
			'description'           => __( 'Product supplier', 'i-collection-custom-types' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor' ),
			'taxonomies'            => array( 'supplier_cat' ),
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 17,
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
	public function register_product_location_taxonomy() {

		$labels = array(
			'name'                       => __( 'Locations', 'i-collection-custom-types' ),
			'singular_name'              => __( 'Location', 'i-collection-custom-types' ),
			'menu_name'                  => __( 'Locations', 'i-collection-custom-types' ),
			'all_items'                  => __( 'All locations', 'i-collection-custom-types' ),
			'parent_item'                => __( 'Parent location', 'i-collection-custom-types' ),
			'parent_item_colon'          => __( 'Parent location:', 'i-collection-custom-types' ),
			'new_item_name'              => __( 'New location', 'i-collection-custom-types' ),
			'add_new_item'               => __( 'Add New location', 'i-collection-custom-types' ),
			'edit_item'                  => __( 'Edit location', 'i-collection-custom-types' ),
			'update_item'                => __( 'Update location', 'i-collection-custom-types' ),
			'view_item'                  => __( 'View location', 'i-collection-custom-types' ),
			'separate_items_with_commas' => __( 'Separate locations with commas', 'i-collection-custom-types' ),
			'add_or_remove_items'        => __( 'Add or remove locations', 'i-collection-custom-types' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'i-collection-custom-types' ),
			'popular_items'              => __( 'Popular location', 'i-collection-custom-types' ),
			'search_items'               => __( 'Search locations', 'i-collection-custom-types' ),
			'not_found'                  => __( 'Not Found', 'i-collection-custom-types' ),
			'no_terms'                   => __( 'No location', 'i-collection-custom-types' ),
			'items_list'                 => __( 'Locations list', 'i-collection-custom-types' ),
			'items_list_navigation'      => __( 'Locations list navigation', 'i-collection-custom-types' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'meta_box_cb'				=> 'post_categories_meta_box'
		);
		register_taxonomy( 'product_location', array( 'product' ), $args );
	}

	public function add_supplier_meta_box_to_product($post){
		add_meta_box( 'supplier-meta-box', __('Supplier','i-collection-custom-types'), array( $this,'supplier_meta_box_layout'), array('product'), 'side',  'core');
	}


   	public function supplier_meta_box_layout($post, $meta){
	   	$screens = $meta['args'];

		// Используем nonce для верификации
		wp_nonce_field( plugin_basename(__FILE__), 'i-collection-custom-type' );

		// значение поля
		$value = get_post_meta( $post->ID, '_supplier', true );

		// Поля формы для введения данных
		$suppliers = get_posts(
			array(
				'post_type'   => 'supplier',
				'numberposts' => -1,
			));
		if (count($suppliers)){
			echo '<select name="_supplier" style="width:100%;">';
			foreach( $suppliers as $supplier_item ){
				if ($value==$supplier_item->ID){
					$selected='selected="selected"';
				} else {
					$selected="";
				}
			    echo '<option '.$selected.' value="'.$supplier_item->ID.'">'.$supplier_item->post_title.'</option>';
			}
			echo '</select>';
		}
   	}

	public function i_collection_save_postdata( $post_id ) {
		// Убедимся что поле установлено.
		if ( ! isset( $_POST['_supplier'] ) )
			return;

		// проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
		if ( ! wp_verify_nonce( $_POST['i-collection-custom-type'], plugin_basename(__FILE__) ) )
			return;

		// если это автосохранение ничего не делаем
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

		// проверяем права юзера
		if( ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Все ОК. Теперь, нужно найти и сохранить данные
		// Очищаем значение поля input.
		$my_data = sanitize_text_field( $_POST['_supplier'] );

		// Обновляем данные в базе данных.
		update_post_meta( $post_id, '_supplier', $my_data );
	}

}

$ICollectionCustomTypes = new ICollectionCustomTypes();
