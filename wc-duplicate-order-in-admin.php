<?php

/**
 * Plugin Name: Duplaicate Order for Woocommerce in Admin
 * Description: Add an "duplicate order" button in admin Orders list
 * Version: 1.2.0
 * Author: Иван Никитин и партнеры
 * Author URI: https://ivannikitin.com/
 * Text Domain: wc-duplicate-order-in-admin
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/ivannikitin-com/wc-duplicate-order-in-admin
 * GitHub Branch:     master
 * Requires WP:       4.8
 * Requires PHP:      5.3
 * Tested up to: 5.5.1
 *
 * @license   GPL-2.0+
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'PRRO_VERSION', '1.2.0' );

class DuplicateOrderForWoocommerce {

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @access  public
	 * @action wc_duplicate_order_in_admin_init
	 */
	public function __construct() {
		$plugin = plugin_basename( __FILE__ );
		if( is_admin() ) {
			add_filter( 'woocommerce_settings_tabs_array',          array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_duplicate_order',   array( $this, 'settings_tab' ) );
			add_action( 'woocommerce_update_options_duplicate_order',  array( $this, 'update_settings' ) );
			add_action( 'woocommerce_ordered_again',                array( $this, 'ordered_again' ) );
			add_filter( "plugin_action_links_$plugin",              array( $this, 'plugin_add_settings_link' ) );
			add_action( 'init',                                     array( $this, 'reactivate_action'), 9999 );
			add_action( 'init',                                     array( $this, 'load_plugin_textdomain') );
			add_action( 'current_screen',                           array( $this, 'current_screen') );
			//add_filter( 'woocommerce_admin_order_actions',          array( $this, 'add_duplicate_order_button' ), 100, 2 );
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_duplicate_order_button' ) );
			add_action( 'admin_head', array( $this, 'add_duplicate_order_actions_button_css' ));

			register_activation_hook( __FILE__,                 array( 'DuplicateOrderForWoocommerce', 'install' ) );
			register_uninstall_hook( __FILE__,                  array( 'DuplicateOrderForWoocommerce', 'uninstall' ) );

			do_action( 'wc_duplicate_order_in_admin_init' );
			add_action( 'wp_ajax_duplicate_order', array($this, 'duplicate_order' ) );
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
		$locale = apply_filters( 'plugin_locale', $locale, 'wc-duplicate-order-in-admin' );

		load_textdomain( 'wc-duplicate-order-in-admin', trailingslashit( WP_LANG_DIR ) . 'wc-duplicate-order-in-admin/wc-duplicate-order-in-admin-' . $locale . '.mo' );
		load_plugin_textdomain( 'wc-duplicate-order-in-admin', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Reactivate the reorder link in order details
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function reactivate_action() {
		if ( get_option( 'prro_reactivate_action' ) == 'yes' ) {
			add_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button', 9999 );
		}
	}

	/**
	 * Add a link to plugin settings to the plugin list
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function plugin_add_settings_link( $links ) {
		$settings_link = '<a href="'. admin_url( 'admin.php?page=wc-settings&tab=duplicate_order' ) .'">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Save old order id to woocommerce session
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	/*public function ordered_again( $order_id ) {
		WC()->session->set( 'reorder_from_orderid', $order_id );
		$notice = get_option( 'prro_cart_notice' );
		if ( $notice != '' ) {
			wc_add_notice( $notice, 'notice' );
		}
	}*/


	/**
	 * Add a new settings tab to woocommerce/settings
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['duplicate_order'] = _x( 'Duplicate Order', 'WooCommerce Settngs Tab', 'wc-duplicate-order-in-admin' );
		return $settings_tabs;
	}

	/**
	 * @ince    1.0.0
	 * @access  public
	 */
	public  function settings_tab() {
		woocommerce_admin_fields( self::get_settings() );
	}

	/**
	 * @since    1.0.0
	 * @access  public
	 */
	function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}


	/**
	 * Define the settings for this plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 * @filters wc_duplicate_order_in_admin_settings
	 */
	public function get_settings() {
		$settings = array(
			'section_title' => array(
				'name'     => __( 'Duplicate Order', 'wc-duplicate-order-in-admin' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'prro_section_title'
			),
			'duplicate_customer' => array(
				'name' => __( 'Duplicate customer', 'wc-duplicate-order-in-admin' ),
				'type' => 'checkbox',
				'desc' => __( '', 'wc-duplicate-order-in-admin' ),
				'id'   => 'do_duplicate_customer'
			),
			'duplicate_shipping_data' => array(
				'name' => __( 'Duplicate shipping data', 'wc-duplicate-order-in-admin' ),
				'type' => 'checkbox',
				'desc' => __( '', 'wc-duplicate-order-in-admin' ),
				'id'   => 'do_duplicate_shipping_data'
			),	
			'duplicate_billing_data' => array(
				'name' => __( 'Duplicate billing data', 'wc-duplicate-order-in-admin' ),
				'type' => 'checkbox',
				'desc' => __( '', 'wc-duplicate-order-in-admin' ),
				'id'   => 'do_duplicate_billing_data'
			),	
			'duplicate_coupons' => array(
				'name' => __( 'Duplicate coupons', 'wc-duplicate-order-in-admin' ),
				'type' => 'checkbox',
				'desc' => __( '', 'wc-duplicate-order-in-admin' ),
				'id'   => 'do_duplicate_coupons'
			),
			'create_note' => array(
				'name' => __( 'Create order note', 'wc-duplicate-order-in-admin' ),
				'type' => 'checkbox',
				'desc' => __( 'If checked, it create an order note with a link to the original order.', 'wc-duplicate-order-in-admin' ),
				'id'   => 'do_create_order_note'
			),
		);

		$settings['section_end'] = array(
			'type' => 'sectionend',
			'id' => 'prro_section_end'
		);

		return apply_filters( 'wc_duplicate_order_in_admin_settings', $settings );
	}

	/**
	 * Setup Database on activating the plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	static public function install() {
		if ( false === get_option( 'create_order_note' ) ) {
			add_option( 'create_order_note', '1' );
		}
		if ( false === get_option( 'prro_reactivate_action' ) ) {
			add_option( 'prro_reactivate_action', 'no' );
		}
	}

	/**
	 * Cleanup Database on deleting the plugin
	 *
	 * @since    1.0.0
	 * @access  public
	 */
	static public function uninstall() {
		delete_option( 'prro_reactivate_action' );
		delete_option( 'prro_cart_notice' );
		delete_option( 'do_duplicate_customer' );
		delete_option( 'do_duplicate_shipping_data' );
		delete_option( 'do_duplicate_billing_data' );
		delete_option( 'do_duplicate_coupons' );
		delete_option( 'do_create_order_note' );
		
	}

	/**
	 *
	 * @since    1.1.0
	 * @access  public
	 */
	public function current_screen() {
		$cs = get_current_screen();
		if ( $cs->post_type == 'shop_order' ) {
		    add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
	}

	/**
	 *
	 * @since    1.1.0
	 * @access  public
	 */
	public function admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		//wp_register_script( 'wc-orders', WC()->plugin_url() . '/assets/js/admin/wc-orders' . $suffix . '.js', array( 'jquery', 'wp-util', 'underscore', 'backbone', 'jquery-blockui' ), PRRO_VERSION );
		wp_register_script( 'duplicate_orders', plugins_url( '/js/duplicate_orders' . $suffix . '.js' , __FILE__ ), array( 'jquery'), PRRO_VERSION,true );
		wp_enqueue_script( 'duplicate_orders' );
		wp_register_style( 'do_admin_styles', plugins_url('style.css', __FILE__), array(), PRRO_VERSION );
		wp_enqueue_style( 'do_admin__styles' );
    }


	//public function add_duplicate_order_button( $actions, $order ) {
	public function add_duplicate_order_button( $order ) {
	    $order_id = $order->get_id();
	    $reorder_from  = get_post_meta( $order_id, '_reorder_from_id', true );
	    //$icon = "https://medknigaservis.mydevserver.ru/wp-content/plugins/wc-duplicate-order-in-admin/images/img_460317.png";
        $actions['duplicate_order'] = array(
        	'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=duplicate_order&status=processing&order_id='.$order_id ), '' ),
        	'img'		=> plugin_dir_url(__FILE__) .'images/img_460317.png',
        	'name'      => __( 'Duplicate', 'wc-duplicate-order-in-admin' ),
        	'alt'		=> "Duplicate order",
        	'action'    => "duplicate_order",
        ); ?>
        <a href="<?php echo $actions['duplicate_order']['url']; ?>" class="button wc-action-button wc-action-button-duplicate_order duplicate_order" target="_blank" alt="<?php echo $actions['duplicate_order']['alt']; ?>" data-tip="<?php echo $actions['duplicate_order']['alt']; ?>">
				<img src="<?php echo $actions['duplicate_order']['img']; ?>" alt="<?php echo $actions['duplicate_order']['alt']; ?>" width="16">
			</a>
		<?php //return $actions;
	}
	public function add_duplicate_order_actions_button_css() {
   		$action_slug = "duplicate_order"; // The key slug defined for your action button
    	echo '<style>.wc-action-button-'.$action_slug.':after { display: block; background: url('.plugin_dir_url(__FILE__) .'images/img_460317.png) transparent no-repeat;
width: 16px!important; height: 16px!important; content: ""; }</style>';
	}

	public function duplicate_order() {
		global $order;
		$order_id = $_GET['order_id'];
		if (!$order_id) {
			add_action( 'admin_notices', array($this, 'duplicate__error'));
			wp_die( __( 'Не указан номер заказа.', 'wc-duplicate-order-in-admin' ) );
		}
		$original_order = new WC_Order($order_id);
		$user_id = $original_order->get_user_id();
		$order_data =  array(
				'post_type'     => 'shop_order',
				'post_status'   => 'wc-processing',
				'ping_status'   => 'closed',
				'post_author'   => $user_id,
				'post_excerpt'  => 'Дубликат заказа #' . $original_order_id,
				'post_password' => uniqid( 'order_' ),
		);
		$duplicate_order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
		if ( is_wp_error( $duplicate_order_id ) ) {
			//add_action( 'admin_notices', array($this, 'clone__error'));
			wp_die( __( 'Ошибка в клонировании заказа.', 'wc-duplicate-order-in-admin' ) );
		} else {
			$this->clone_order_data($duplicate_order_id, $order_id, true, false);
		}
		if ( get_option( 'do_create_order_note' )) {
			$this->create_order_note($duplicate_order_id, $order_id);
		}
		echo $duplicate_order_id;
		wp_die();
	}

	public function clone_order_data($order_id, $original_order_id, $clone_order=true, $reduce_stock=false, $clone_shipping=true){
		$this->clone_order_header($order_id, $original_order_id);
		if ( get_option( 'do_duplicate_shipping_data' )) {
			$this->clone_order_shipping_fields($order_id, $original_order_id);
		}
		if ( get_option( 'do_duplicate_billing_data' )) {
			$this->clone_order_billing_fields($order_id, $original_order_id);
		}		
		$this->clone_order_items($order_id, $original_order_id);
		if ( get_option( 'do_duplicate_coupons' )) {
			$this->clone_coupons($order_id, $original_order_id);
		}
		$this->clone_payment_info($order_id, $original_order_id);
	}

	public function clone_order_header($order_id, $original_order_id) {
		if ( get_option( 'do_duplicate_shipping_data' )) {
			update_post_meta( $order_id, '_order_shipping',         get_post_meta($original_order_id, '_order_shipping', true) );
		}
        update_post_meta( $order_id, '_order_discount',         get_post_meta($original_order_id, '_order_discount', true) );
        update_post_meta( $order_id, '_cart_discount',          get_post_meta($original_order_id, '_cart_discount', true) );
        update_post_meta( $order_id, '_order_tax',              get_post_meta($original_order_id, '_order_tax', true) );
        if ( get_option( 'do_duplicate_shipping_data' )) {
        	update_post_meta( $order_id, '_order_shipping_tax',     get_post_meta($original_order_id, '_order_shipping_tax', true) );
        }
        update_post_meta( $order_id, '_order_total',            get_post_meta($original_order_id, '_order_total', true) );

        update_post_meta( $order_id, '_order_key',              'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
        update_post_meta( $order_id, '_customer_user',          get_post_meta($original_order_id, '_customer_user', true) );
        update_post_meta( $order_id, '_order_currency',         get_post_meta($original_order_id, '_order_currency', true) );
        update_post_meta( $order_id, '_prices_include_tax',     get_post_meta($original_order_id, '_prices_include_tax', true) );
        update_post_meta( $order_id, '_customer_ip_address',    get_post_meta($original_order_id, '_customer_ip_address', true) );
        update_post_meta( $order_id, '_customer_user_agent',    get_post_meta($original_order_id, '_customer_user_agent', true) );
	}

	public function clone_order_billing_fields($order_id, $original_order_id) {
		update_post_meta( $order_id, '_billing_city',           get_post_meta($original_order_id, '_billing_city', true));
        update_post_meta( $order_id, '_billing_state',          get_post_meta($original_order_id, '_billing_state', true));
        update_post_meta( $order_id, '_billing_postcode',       get_post_meta($original_order_id, '_billing_postcode', true));
        update_post_meta( $order_id, '_billing_email',          get_post_meta($original_order_id, '_billing_email', true));
        update_post_meta( $order_id, '_billing_phone',          get_post_meta($original_order_id, '_billing_phone', true));
        update_post_meta( $order_id, '_billing_address_1',      get_post_meta($original_order_id, '_billing_address_1', true));
        update_post_meta( $order_id, '_billing_address_2',      get_post_meta($original_order_id, '_billing_address_2', true));
        update_post_meta( $order_id, '_billing_country',        get_post_meta($original_order_id, '_billing_country', true));
        update_post_meta( $order_id, '_billing_first_name',     get_post_meta($original_order_id, '_billing_first_name', true));
        update_post_meta( $order_id, '_billing_last_name',      get_post_meta($original_order_id, '_billing_last_name', true));
        update_post_meta( $order_id, '_billing_company',        get_post_meta($original_order_id, '_billing_company', true));
	}

	public function clone_order_shipping_fields($order_id, $original_order_id) {
		update_post_meta( $order_id, '_shipping_country',       get_post_meta($original_order_id, '_shipping_country', true));
        update_post_meta( $order_id, '_shipping_first_name',    get_post_meta($original_order_id, '_shipping_first_name', true));
        update_post_meta( $order_id, '_shipping_last_name',     get_post_meta($original_order_id, '_shipping_last_name', true));
        update_post_meta( $order_id, '_shipping_company',       get_post_meta($original_order_id, '_shipping_company', true));
        update_post_meta( $order_id, '_shipping_address_1',     get_post_meta($original_order_id, '_shipping_address_1', true));
        update_post_meta( $order_id, '_shipping_address_2',     get_post_meta($original_order_id, '_shipping_address_2', true));
        update_post_meta( $order_id, '_shipping_city',          get_post_meta($original_order_id, '_shipping_city', true));
        update_post_meta( $order_id, '_shipping_state',         get_post_meta($original_order_id, '_shipping_state', true));
        update_post_meta( $order_id, '_shipping_postcode',      get_post_meta($original_order_id, '_shipping_postcode', true));
	}	

	public function clone_order_items($order_id, $original_order_id) {
		$original_order = new WC_Order($original_order_id);
		foreach($original_order->get_items() as $originalOrderItem){

            $itemName = $originalOrderItem['name'];
            $qty = $originalOrderItem['qty'];
            $lineTotal = $originalOrderItem['line_total'];
            $lineTax = $originalOrderItem['line_tax'];
            $productID = $originalOrderItem['product_id'];

            $item_id = wc_add_order_item( $order_id, array(
                'order_item_name'       => $itemName,
                'order_item_type'       => 'line_item'
            ) );

            wc_add_order_item_meta( $item_id, '_qty', $qty );
            //wc_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
            wc_add_order_item_meta( $item_id, '_product_id', $productID );
            //wc_add_order_item_meta( $item_id, '_variation_id', $values['variation_id'] );
            wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $lineTotal ) );
            wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $lineTotal ) );
            wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( '0' ) );
            wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( '0' ) );

            $original_order_shipping_items = $original_order->get_items('shipping');
	        foreach ( $original_order_shipping_items as $original_order_shipping_item ) {

	        }
        }
        if ( get_option( 'do_duplicate_shipping_data' )) {
	       	$item_id = wc_add_order_item( $order_id, array(
		        'order_item_name'       => $original_order_shipping_item['name'],
		        'order_item_type'       => 'shipping'
		    ) );
		    if ( $item_id ) {
		        wc_add_order_item_meta( $item_id, 'method_id', $original_order_shipping_item['method_id'] );
		        wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $original_order_shipping_item['cost'] ) );
		    }
		}
	}
	public function clone_coupons($order_id, $originalorder_id) {
		$original_order = new WC_Order($originalorder_id);
		$original_order_coupons = $original_order->get_items('coupon');
        foreach ( $original_order_coupons as $original_order_coupon ) {
            $item_id = wc_add_order_item( $order_id, array(
                'order_item_name'       => $original_order_coupon['name'],
                'order_item_type'       => 'coupon'
            ) );
            // Add line item meta
            if ( $item_id ) {
                wc_add_order_item_meta( $item_id, 'discount_amount', $original_order_coupon['discount_amount'] );
            }
        }
	}

	public function clone_payment_info($order_id, $originalorder_id) {
		update_post_meta( $order_id, '_payment_method',         get_post_meta($original_order_id, '_payment_method', true) );
        update_post_meta( $order_id, '_payment_method_title',   get_post_meta($original_order_id, '_payment_method_title', true) );
        update_post_meta( $order->id, 'Transaction ID',         get_post_meta($original_order_id, 'Transaction ID', true) );
	}

	/**
	 * Create a order note with link to the old order
	 *
	 * @since    1.0.0
	 * @access  public
	 * @filters wc_duplicate_order_in_admin_order_note
	 */
	public function create_order_note( $order_id, $originalorder_id ) {
		if ($order_id != '' ) {
            add_post_meta( $originalorder_id, '_duplicate_from_id', $order_id, true );
		}
		if ($order_id != '' ) {
			//$order = wc_get_order( $originalorder_id );
			$url = get_edit_post_link( $order_id );
			$note = sprintf( wp_kses( __( 'This is a duplicate of the order #<a href="%1s">%2s</a> <a href="#" class="order-preview" data-order-id="%3s" title="Vorschau"></a>. ', 'wc-duplicate-order-in-admin' ), array(  'a' => array( 'href' => array(), 'class' => array(), 'data-order-id' => array() ) ) ), esc_url( $url ), $order_id,  $order_id );
			$order = new WC_Order($order_id);
			$order->add_order_note( apply_filters( 'wc_duplicate_order_in_admin_order_note', $note, $order_id, $originalorder_id ) );
		}
		//WC()->session->set( 'reorder_from_orderid' , '' );
	}

}

$DuplicateOrderForWoocommerce = new DuplicateOrderForWoocommerce();
