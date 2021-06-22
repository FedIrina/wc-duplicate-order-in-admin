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
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'i-collection-custom-types' );

		load_textdomain( 'i-collection-custom-types', trailingslashit( WP_LANG_DIR ) . 'i-collection-custom-types/i-collection-custom-types-' . $locale . '.mo' );
		load_plugin_textdomain( 'i-collection-custom-types', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		wp_set_script_translations( 'i-collection-custom-types-script', 'i-collection-custom-types' );
	}

	/**
	 * Reactivate the reorder link in order details
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function reactivate_action() {

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

}

$ICollectionCustomTypes = new ICollectionCustomTypes();
