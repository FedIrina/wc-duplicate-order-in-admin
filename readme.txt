=== Repeat Order for WooCommerce  ===
Contributors: polyres, fstaude, uschoene
Tags: woocommerce, order again
Requires at least: 4.8
Tested up to: 5.1.1
Stable tag: 1.2.0
License: GPLv2

Just add an "order again" button in Recent Orders list

== Description ==

Would your customers like to repeat their order and place it in their shopping cart again? WooCommerce has provided an option for this in WooCommerce Core. The button is only displayed in the detail page of the order and if the order have the status "completed" in the customers account.

With our extension you can integrate and display the button "re-order", "repeat order" on the overview page. This is especially useful when complex shopping cart contents are needed again and again, for example by resellers or in the B2B sector. Customers can place the goods they have already ordered in the shopping basket directly from the overview page.

In addition, we have provided additional features for our expansion:

* 	Activation of "Order note" with a reference to the original order.
	The shop manager thus has the information from which completed order the new order was triggered.

* 	Activation of the standard button "Re-order" of WooCommerce, if you use WooCommerce Germanized version 2.0.4 or older, or
	another plugin or theme suppresses the button "Re-order". Then activate this option to reactivate the button.

[](http://coderisk.com/wp/plugin/repeat-order-for-woocommerce/RIPS-sWgOoMX8XN)

== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Installation Instructions =
 
1. Upload plugin files to your plugins folder, or install the plugin through the WordPress plugins screen directly;
2. Activate the plugin through the ‘Plugins’ screen in WordPress;
3. Go to the Settingspage "WooCommerce->Settings->Repeat Order screen to configure the plugin.
 
= Can your plugin also be translated into other languages? =
 
Yes, the plugin can be translated into many languages via WordPress Translate.

= Can I really use this plugin free of charge? =

Yes, Repeat Order for Woocommerce is available for free.

= Does Repeat Order for Woocommerce share any of my data? (GDPR) =

No, Repeat Order for Woocommerce keeps all your data inside your own WordPress & WooCommerce install. There is no data transmitted to us or a third party service.

== Filter and Actions ==

= repeat_order_for_woocommerce_init =
This action is called at the end of the class constructor

= plugin_locale =
This filter can be used to change the plugin locale before translations are loaded. See https://developer.wordpress.org/reference/hooks/plugin_locale/

= repeat_order_for_woocommerce_order_note =
This filter can be used to change the order note in the backend

= woocommerce_valid_order_statuses_for_order_again =
This filter can be used to change the order status for viewing the reorder link

= repeat_order_for_woocommerce_settings =
With this filter it is possiable to add/remove entrys from the plugin settingspage


== Screenshots ==

1. My Account Detail Page
2. My Account Overview Page
3. Settings
4. Order Note "Re-order"
5. Orderlist actions columns with "reorder" icon

== Changelog ==

= 1.2.0 =
* [Added] Reorder icon in orderlist actions column that links to the original order

= 1.1.0 =
* [Added] Preview icon in order note

= 1.0.1 =
* [Feature] WooCommerce 3.5 compatibility
* [Changed] Typo in Title

= 1.0.0 =
* First release.
