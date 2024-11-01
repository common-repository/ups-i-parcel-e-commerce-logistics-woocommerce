<?php
/**
* Plugin Name: UPS i-parcel E-Commerce & Logistics (WooCommerce)
* Description: A plug-in to enable UPS i-parcel Shipping Services & Payment Methods for your international shoppers
* Version: 1.4.5
* Author: UPS i-parcel
* Author URI: http://www.i-parcel.com/
* Text Domain: i-parcel
* @package i-parcel
*/
/* Check if WooCommerce is active */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if (! function_exists('write_log')) {
   function write_log ( $log )  {
	  if (is_array($log) || is_object($log)) {
		  error_log(print_r($log, true));
	  } else {
		 error_log($log);
	  }
   }
}
	
//admin notices to check for required items
function ipar_admin_reqiurements() {
	//do check to see if Tax are enabled.
	$ipar_taxEnabled = get_option('woocommerce_calc_taxes');
	if($ipar_taxEnabled === 'no') {
		echo('<div class="notice notice-error""><p><b>Taxes need to be enabled inside WooCommerce for UPS i-parcel to show Tax and Duty to your international shoppers during checkout.</b></p></div>');
	}
	
	$ipar_taxDisplay = get_option('woocommerce_tax_total_display');
	if($ipar_taxDisplay != 'single') {
		echo('<div class="notice notice-error"><p><b>"Display tax totals" must be set as "As a single total" to show Tax and Duty to your international shoppers during checkout.</b></p></div>');
	}
}
add_action( 'admin_notices', 'ipar_admin_reqiurements' );
add_action('admin_head', 'ipar_CH_css');
function ipar_CH_css() {
	echo '<style>
	.ipartooltip {position:relative;display:inline-block;float:right;}
	.ipartooltip .ipartooltiptext {visibility:hidden;width:200px;background-color:#555;color:#fff;text-align:center;border-radius:6px;padding:5px;position:absolute;z-index:1;bottom:125%;left:50%;margin-left:-105px;opacity:0;transition:opacity 0.3s;font-size:11px;}
	.ipartooltip .ipartooltiptext::after {content: "";position: absolute;top: 100%;left: 50%;margin-left: -5px;border-width: 5px;border-style: solid;border-color: #555 transparent transparent transparent;}
	.ipartooltip:hover .ipartooltiptext {visibility:visible;opacity:1;}
	</style>';
}
//Add i-parcel pages to WordPress admin
function ipar_CH_config() { include('templates/ipar_config.php');}
function ipar_CH_catalogSync() { include('templates/ipar_catalog.php'); }
function ipar_CH_catalogSync_GO() { include('templates/ipar_catalog_upload.php'); }
add_action('admin_menu', 'ipar_CH_config_admin_actions');
function ipar_CH_config_admin_actions() {
    add_menu_page( 'i-parcel Global Access', 'i-parcel', 'manage_options', 'ipar_config.php', 'ipar_CH_config', 'https://www.i-parcel.com/wp-content/themes/i-parcel/images/favicon/favicon-16x16.png' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Config', 'Configuration', 'manage_options', 'ipar_config.php', 'ipar_CH_config' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Catalog Sync', 'Catalog Sync', 'manage_options', 'ipar_catalog.php', 'ipar_CH_catalogSync' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Shipping Methods', 'Shipping Methods', 'manage_options', 'admin.php?page=wc-settings&tab=shipping', '' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Payment Method', 'Payment Method', 'manage_options', 'admin.php?page=wc-settings&tab=checkout', '' );
}
//Register options to save Config settings
add_action( 'admin_init', 'ipar_CH_register_config_settings' );
function ipar_CH_register_config_settings() {
	register_setting( 'iparconfig-group', 'ipar_CustomerID' );
	register_setting( 'iparconfig-group', 'ipar_APIKey' );
	register_setting( 'iparconfig-group', 'ipar_APIPrivateKey' );
	register_setting( 'iparconfig-group', 'ipar_ScriptID' );
	register_setting( 'iparconfig-group', 'ipar_enableLines' );
	register_setting( 'iparconfig-group', 'ipar_linesLocale' );
	register_setting( 'iparconfig-group', 'ipar_unwrapCurr' );
	register_setting( 'iparconfig-group', 'ipar_shopURL' );
	register_setting( 'iparconfig-group', 'ipar_cancelURL' );
	register_setting( 'iparconfig-group', 'ipar_imageURL' );
	register_setting( 'iparconfig-group', 'ipar_logging' );
}
//Register options to save Catalog settings
add_action( 'admin_init', 'ipar_CH_register_catalog_settings' );
function ipar_CH_register_catalog_settings() {
	register_setting( 'iparcatalog-group', 'ipar_catalogConfig' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute1' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute2' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute3' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute4' );
	register_setting( 'iparcatalog-group', 'ipar_cat_CountryOfOrigin' );
	register_setting( 'iparcatalog-group', 'ipar_cat_HTSCodes' );
	register_setting( 'iparcatalog-group', 'ipar_cat_shipAlone' );
	register_setting( 'iparcatalog-group', 'ipar_cat_OverrideCountryOfOrigin' );
}
//create rewrite rules for order notification
add_action( 'init', 'ipar_CH_ups_iparcel_order_notification' );
function ipar_CH_ups_iparcel_order_notification(){
    add_rewrite_rule( 'ipar_ups-order-notification.php$', 'index.php?ordernotification=ipar', 'top' );
}
add_filter( 'query_vars', 'ipar_CH_ups_iparcel_order_notification_vars' );
function ipar_CH_ups_iparcel_order_notification_vars( $query_vars ){
    $query_vars[] = 'ordernotification';
    return $query_vars;
}
//function to grab order notification
function ipar_CH_ups_iparcel_woo_Order_notifiation() {
	if (empty($_POST)) {
		die('GET requests not supported');
	}
	global $wpdb;
	$merchantKey = sanitize_text_field($_POST['business']);
	$orderStatus = sanitize_text_field($_POST['status']);
	$failedDescription = sanitize_text_field($_POST['failure_description']);
	$refNumber = sanitize_text_field($_POST['reference_number']);
	$trackingNumber = sanitize_text_field($_POST['trackingnumber']);
	//check key to make sure it matches what's in the settings
	if($merchantKey != strtoupper(get_option("ipar_APIPrivateKey"))) {
		die('Submitted key doesn\'t match config settings!');
	}
	//if order failed, update order with failed notice
	if($orderStatus === 'FAILED') {
		$iparOrder = wc_get_order($refNumber);
		$iparOrder->add_order_note('Order Payment Failed via UPS i-parcel: "'. $failedDescription .'"<br/>UPS i-parcel tracking number: '. $trackingNumber);
		$iparOrder->update_status('failed');
		die('order #'. $refNumber .' failed because: '. $failedDescription);
	}
	//now get the full details of the order - https://pay.i-parcel.com/v1/api/GetCheckoutDetails
	$ipar_GCDR_WPrequest = array(
		'body' => '{"key": "'. $merchantKey .'","tx": "'. $trackingNumber .'"}',
		'timeout' => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
		'cookies' => array()
	);
	//use GetCheckoutDetails to get orders of detail if changed on Handoff page and update order accordingly
	$ipar_getDetailsResponse = wp_remote_post( 'https://pay.i-parcel.com/v1/api/GetCheckoutDetails', $ipar_GCDR_WPrequest );
	$ipar_getDetailsResponseBody = wp_remote_retrieve_body($ipar_getDetailsResponse);
	$ipar_GCDR_responseData = json_decode($ipar_getDetailsResponseBody, TRUE);
	
	//if logging is enabled, log the response from GetCheckoutDetails
	if(get_option('ipar_logging') != '') {
		$iparPluginlog = plugin_dir_path(__FILE__).'/templates/debug.log';
		$iparLogMessage = date("Y-m-d H:i:s").'_GetCheckoutDetailsResponse: '.$ipar_getDetailsResponseBody.PHP_EOL;
		error_log($iparLogMessage, 3, $iparPluginlog);
	}
	
	$orderGCDRStatus = $ipar_GCDR_responseData['status'];
	$refGCDRNumber = $ipar_GCDR_responseData['reference_number'];
	$iparGCDRServiceLevel = $ipar_GCDR_responseData['servicelevel'];
	$iparGCDROrder = wc_get_order($refGCDRNumber);
	$iparGCDROrder_data = $iparGCDROrder->get_data();
	$iparGCDRShippingCost = $ipar_GCDR_responseData['shipping_cost'];
	$iparGCDROrderTaxAndDuty = $ipar_GCDR_responseData['tax'] + $ipar_GCDR_responseData['duty'];
	foreach( $iparGCDROrder->get_items( 'tax' ) as $item_id => $item_tax ){
		$tax_data = $item_tax->get_data();
		$iparOrderTaxID = $tax_data['id'];
	}
	foreach( $iparGCDROrder->get_items( 'shipping' ) as $item_id => $item_shipping ){
		$shipping_data = $item_shipping->get_data();
		$iparOrderShippingID = $shipping_data['id'];
	}
	
	//get WC subtotal and discount value
	$iparWCOrderSubtotal = $iparGCDROrder->get_subtotal();
	$iparWCOrderDiscount = $iparGCDROrder->get_discount_total();
	
	//Add up all values to match handoff page
	$iparWCNewOrderTotal = $iparWCOrderSubtotal - $iparWCOrderDiscount + $iparGCDRShippingCost + $iparGCDROrderTaxAndDuty;
	
	update_post_meta( $refGCDRNumber, '_billing_first_name', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['first_name'] ) );
	update_post_meta( $refGCDRNumber, '_billing_last_name', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['last_name'] ) );
	update_post_meta( $refGCDRNumber, '_billing_email', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['email'] ) );
	update_post_meta( $refGCDRNumber, '_billing_phone', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['phone'] ) );
	update_post_meta( $refGCDRNumber, '_billing_address_1', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['address1'] ) );
	update_post_meta( $refGCDRNumber, '_billing_address_2', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['address2'] ) );
	update_post_meta( $refGCDRNumber, '_billing_city', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['city'] ) );
	update_post_meta( $refGCDRNumber, '_billing_state', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['state'] ) );
	update_post_meta( $refGCDRNumber, '_billing_zip', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['zip'] ) );
	update_post_meta( $refGCDRNumber, '_billing_country', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Billing']['country'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_first_name', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_first_name'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_last_name', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_last_name'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_email', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_email'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_address_1', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_address1'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_address_2', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_address2'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_city', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_city'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_state', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_state'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_zip', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_zip'] ) );
	update_post_meta( $refGCDRNumber, '_shipping_country', sanitize_text_field( $ipar_GCDR_responseData['AddressInfo']['Shipping']['shipping_country'] ) );
	update_post_meta( $refGCDRNumber, '_order_shipping', $iparGCDRShippingCost );
	update_post_meta( $refGCDRNumber, '_order_total', $iparWCNewOrderTotal );
	wc_update_order_item_meta($iparOrderShippingID, 'cost', $iparGCDRShippingCost);
	wc_update_order_item_meta($iparOrderShippingID, 'UPS-iparcel-service-ID', $iparGCDRServiceLevel);
	//rename service level if changed from cart to handoff
	if($iparGCDRServiceLevel === '115') {
		$serviceLevelName = 'UPS Wordwide Economy';
	} else if ($iparGCDRServiceLevel === '207') {
		$serviceLevelName = 'UPS Express';
	} else if ($iparGCDRServiceLevel === '208') {
		$serviceLevelName = 'UPS Expedited';
	} else if ($iparGCDRServiceLevel === '211') {
		$serviceLevelName = 'UPS Standard';
	} else if ($iparGCDRServiceLevel === '254') {
		$serviceLevelName = 'UPS Express Plus';
	} else if ($iparGCDRServiceLevel === '265') {
		$serviceLevelName = 'UPS Express Saver';
	}
	$wpdb->query($wpdb->prepare("UPDATE ". $wpdb->prefix ."woocommerce_order_items SET order_item_name = %d WHERE order_item_id = %s", $serviceLevelName, $iparOrderShippingID ));
	wc_update_order_item_meta($iparOrderTaxID, 'tax_amount', $iparGCDROrderTaxAndDuty);
	wc_update_order_item_meta($iparOrderTaxID, 'label', 'Tax & Duty');
	$iparStatusAfter = get_option("woocommerce_ups_iparcel_payment_settings");
	$iparGCDROrder->update_status($iparStatusAfter['ipar_order_status_after']);
	$iparGCDROrder->add_order_note('Order Payment Success via UPS i-parcel<br/><br/>UPS i-parcel Tracking Number: '. $ipar_GCDR_responseData['trackingnumber'] .'<br/><br/><a href="https://webservices.i-parcel.com/api/Barcode?key='. $merchantKey .'&trackingnumber='. $ipar_GCDR_responseData['trackingnumber'] .'" target="_blank">Click here</a> to generate your barcode<br/><br/><a href="https://webservices.i-parcel.com/api/GetLabel?key='. $merchantKey .'&trackingnumber='. $ipar_GCDR_responseData['trackingnumber'] .'" target="_blank">Click here</a> to generate your label');
	
	// Get the WC_Email_New_Order object
	$iparEmailNewOrder = WC()->mailer()->get_emails()['WC_Email_New_Order'];
	// Sending the new Order email notification for an $order_id (order ID)
	$iparEmailNewOrder->trigger( $refGCDRNumber );
	echo($refGCDRNumber);
}	
add_action( 'parse_request', 'ipar_CH_ups_iparcel_order_notification_parse_request' );
function ipar_CH_ups_iparcel_order_notification_parse_request( $wp ){
    if ( array_key_exists( 'ordernotification', $wp->query_vars ) ) {
        ipar_CH_ups_iparcel_woo_Order_notifiation();
		exit();
    }
    return;
}	
//Add Action to feed updated product details to i-parcel catalog on product save
function ipar_CH_ipar_update_catalog_price($post_id) {
	$iparSyncCheck = esc_attr(get_option('ipar_catalogConfig'));
	if($iparSyncCheck == 1) {
		global $post;
		$slug = 'product';
		$variantSlug = 'product_variation';
		$iparPostType = get_post_type($post);
		if ( $slug != $iparPostType ) {
			return;
		}
		$iparCatArgs = array( 'taxonomy' => 'product_cat',);
		$iparCatTerms = wp_get_post_terms($post->ID,'product_cat', $iparCatArgs);
		$ipar_catArray = array();
		$count = count($iparCatTerms); 
		if ($count > 0) {
			foreach ($iparCatTerms as $iparCatTerm) {
				$ipar_catArray[] = $iparCatTerm->name;
			}
		}
		$ipar_prodCats = implode(",", $ipar_catArray);
		// Check if product is variable and send variant SKU data
		$iparProduct = new WC_Product_Variable($post_id);
		$iparProdVars = $iparProduct->get_available_variations();
		if(!empty($iparProdVars)) {
			// get IDs of main product and variants
			foreach ( $iparProdVars as $iparProdVar ) {
				if( get_post_type($iparProdVar['variation_id']) == $variantSlug ) {
					//get variation ID if needed for parent
					$iparVariation = wc_get_product($iparProdVar['variation_id']);
					$WooSKU = get_post_meta($iparProdVar['variation_id'], '_sku', true);
					if($WooSKU == '') {
						$WooSKU = $iparProdVar['variation_id'];
					}
					// Product Name
					$iparProdName = get_the_title($iparProdVar['variation_id']);
					if(strpos($iparProdName, 'Variation') !== false) {
						$iparProdName = explode (' ', $iparProdName, 4);
						$iparProdName = $iparProdName[3];
					}
					// Get Country of Origin
					$iparCountryOfOrigin = get_option("ipar_cat_OverrideCountryOfOrigin");
					if( $iparCountryOfOrigin == '') {
						$iparCountryOfOrigin = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_CountryOfOrigin"));
						$iparCountryOfOrigin = $iparCountryOfOrigin[0]->name;
					}
					if( get_option("ipar_cat_attribute1") != 'none' ) {
						$iparAttribute2 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute1"));
						$iparAttribute2 = $iparAttribute2[0]->name;
					} else {
						$iparAttribute2 = "";
					}
					if( get_option("ipar_cat_attribute2") != 'none' ) {
						$iparAttribute3 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute2"));
						$iparAttribute3 = $iparAttribute3[0]->name;
					} else {
						$iparAttribute3 = "";
					}
					if( get_option("ipar_cat_attribute3") != 'none' ) {
						$iparAttribute4 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute3"));
						$iparAttribute4 = $iparAttribute4[0]->name;
					} else {
						$iparAttribute4 = "";
					}
					if( get_option("ipar_cat_attribute4") != 'none' ) {
						$iparAttribute5 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute4"));
						$iparAttribute5 = $iparAttribute5[0]->name;
					} else {
						$iparAttribute5 = "";
					}
					if( get_option("ipar_cat_HTSCodes") != 'none' ) {
						$iparHTSCode = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_HTSCodes"));
						$iparHTSCode = $iparHTSCode[0]->name;
					} else {
						$iparHTSCode = NULL;
					}
					if( get_option("ipar_cat_shipAlone") != 'none' ) {
						$iparShipAlone = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_shipAlone"));
						
						$iparShipAlone = $iparShipAlone[0]->name;
					
					} else {
					
						$iparShipAlone = NULL;
					}
					$iparSKUWeight = get_post_meta($iparProdVar['variation_id'], '_weight', true);
					if($iparSKUWeight === '') {
						$iparSKUWeight = get_post_meta($iparVariation->get_parent_id(), '_weight', true);
					}
					$iparSKUHeight = get_post_meta($iparProdVar['variation_id'], '_height', true);
					if($iparSKUHeight === '') {
						$iparSKUHeight = get_post_meta($iparVariation->get_parent_id(), '_height', true);
					}
					$iparSKUWidth = get_post_meta($iparProdVar['variation_id'], '_width', true);
					if($iparSKUWidth === '') {
						$iparSKUWidth = get_post_meta($iparVariation->get_parent_id(), '_width', true);
					}
					$iparSKULength = get_post_meta($iparProdVar['variation_id'], '_length', true);
					if($iparSKULength === '') {
						$iparSKULength = get_post_meta($iparVariation->get_parent_id(), '_length', true);
					}
					
					//get Weight unit and convert based on selected value
					$iparMerchantWeightSetting = esc_attr( get_option('woocommerce_weight_unit') );
					if($iparMerchantWeightSetting === 'oz') {
						//convert oz to lbs
						$iparSKUWeight = $iparSKUWeight / 16;
					} else if($iparMerchantWeightSetting === 'g') {
						//convert g to kg
						$iparSKUWeight = $iparSKUWeight / 1000;
					}
					$ipar_SKUs[] = array(
						'SKU' => $WooSKU,
						'ProductName' => $iparProdName,
						'Attribute2' => $iparAttribute2,
						'Attribute3' => $iparAttribute3,
						'Attribute4' => $iparAttribute4,
						'Attribute5' => $iparAttribute5,
						'Attribute6' => $post->post_content,
						'HSCodeUS' => $iparHTSCode,
						'HSCodeCA' => NULL,
						'CountryOfOrigin' => $iparCountryOfOrigin,
						'CurrentPrice' => get_post_meta($iparProdVar['variation_id'], '_price', true),
						'ProductURL' => get_permalink($iparProdVar['variation_id']),
						'Weight' => $iparSKUWeight,
						'SKN' => NULL,
						'Length' => $iparSKULength,
						'Width' => $iparSKUWidth,
						'Height' => $iparSKUHeight,
						'ShipAlone' => $iparShipAlone,
						'Delete' => false,
						'WeightInKilos' => NULL
					);
				}
				
			}
		}
		//Send original product data
		$WooSKU = get_post_meta($post->ID, '_sku', true);
		if($WooSKU == '') {
			$WooSKU = $post->ID;
		}
		// Get Country of Origin
		$iparCountryOfOrigin = get_option("ipar_cat_OverrideCountryOfOrigin");
		if( $iparCountryOfOrigin == '') {
			//$iparCountryOfOrigin = get_post_meta($WooProdID, get_option("ipar_cat_CountryOfOrigin"), true);
			$iparCountryOfOrigin = get_the_terms($post->ID, get_option("ipar_cat_CountryOfOrigin"));
			$iparCountryOfOrigin = $iparCountryOfOrigin[0]->name;
		}
		if( get_option("ipar_cat_attribute1") != 'none' ) {
			$iparAttribute2 = get_the_terms($post->ID, get_option("ipar_cat_attribute1"));		
			$iparAttribute2 = $iparAttribute2[0]->name;
		} else {
			$iparAttribute2 = "";
		}
		if( get_option("ipar_cat_attribute2") != 'none' ) {
			$iparAttribute3 = get_the_terms($post->ID, get_option("ipar_cat_attribute2"));
			$iparAttribute3 = $iparAttribute3[0]->name;
		} else {
			$iparAttribute3 = "";
		}
		if( get_option("ipar_cat_attribute3") != 'none' ) {
			$iparAttribute4 = get_the_terms($post->ID, get_option("ipar_cat_attribute3"));
			$iparAttribute4 = $iparAttribute4[0]->name;
		} else {
			$iparAttribute4 = "";
		}
		if( get_option("ipar_cat_attribute4") != 'none' ) {
			$iparAttribute5 = get_the_terms($post->ID, get_option("ipar_cat_attribute4"));
			$iparAttribute5 = $iparAttribute5[0]->name;
		} else {
			$iparAttribute5 = "";
		}
		if( get_option("ipar_cat_HTSCodes") != 'none' ) {
			$iparHTSCode = get_the_terms($post->ID, get_option("ipar_cat_HTSCodes"));
			$iparHTSCode = $iparHTSCode[0]->name;
		} else {
			$iparHTSCode = NULL;
		}
		
		if( get_option("ipar_cat_shipAlone") != 'none' ) {
			$iparShipAlone = get_the_terms($post->ID, get_option("ipar_cat_shipAlone"));
			$iparShipAlone = $iparShipAlone[0]->name;
		} else {
			$iparShipAlone = NULL;
		}
		//get Weight unit and convert based on selected value
		$iparMerchantWeightSetting = esc_attr( get_option('woocommerce_weight_unit') );
		$iparSKUWeight = $post->_weight;
		if($iparMerchantWeightSetting === 'oz') {
			//convert oz to lbs
			$iparSKUWeight = $iparSKUWeight / 16;
		} else if($iparMerchantWeightSetting === 'g') {
			//convert g to kg
			$iparSKUWeight = $iparSKUWeight / 1000;
		}
		$ipar_SKUs[] = array(
			'SKU' => $WooSKU,
			'ProductName' => get_the_title($post->ID),
			'Attribute1' => $ipar_prodCats,
			'Attribute2' => $iparAttribute2,
			'Attribute3' => $iparAttribute3,
			'Attribute4' => $iparAttribute4,
			'Attribute5' => $iparAttribute5,
			'Attribute6' => $post->post_content,
			'HSCodeUS' => $iparHTSCode,
			'HSCodeCA' => NULL,
			'CountryOfOrigin' => $iparCountryOfOrigin,
			'CurrentPrice' => $post->_price,
			'ProductURL' => get_permalink($post->ID),
			'Weight' => $iparSKUWeight,
			'SKN' => NULL,
			'Length' => $post->_length,
			'Width' => $post->_width,
			'Height' => $post->_height,
			'ShipAlone' => $iparShipAlone,
			'Delete' => false,
			'WeightInKilos' => NULL
		);
		// Put data in array for UPS i-parcel;		
		$ipar_SKUData = array(
			'key' => get_option("ipar_APIPrivateKey"),
			'SKUs' => $ipar_SKUs
		);
		$ipar_WPrequest = array(
			'body' => json_encode($ipar_SKUData),
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
			'cookies' => array()
		);
		$ipar_catalogResponse = wp_remote_post( 'https://webservices.i-parcel.com/api/SubmitCatalog', $ipar_WPrequest );
		$ipar_catalogResponseBody = wp_remote_retrieve_body($ipar_quoteResponse);
		$ipar_responseData = json_decode($ipar_catalogResponseBody, TRUE);
	}
}
	
add_action('woocommerce_update_product', 'ipar_CH_ipar_update_catalog_price');
//Add 2 lines to the header
//Check to see if we're adding the two lines of code.  "0" = yes || "1" = no
$iparTwoLinesVerified = get_option('ipar_enableLines');
$iparTwoLinesLocale = get_option('ipar_linesLocale');
if ($iparTwoLinesVerified == "0" && $iparTwoLinesLocale == "0") {
	add_action('wp_head','ipar_CH_add_jscss_code');
	function ipar_CH_add_jscss_code() {
		$iparDashID = get_option('ipar_ScriptID');
		$iparUnwrapCurr = get_option('ipar_unwrapCurr');
		$jsLine = '<!-- i-parcel added lines --><script type="text/javascript" src="//script.i-parcel.com/JavaScript?h='. $iparDashID .'"></script>';
		$cssLine = '<link rel="stylesheet" type="text/css" href="//script.i-parcel.com/CSS?h='. $iparDashID .'" />';
		
		
		$iparScriptLine = '<script type="text/javascript" src="'. get_bloginfo('url') .'/wp-content/plugins/ups-i-parcel-e-commerce-logistics-woocommerce/js/iparscript.js"></script>';
		echo $jsLine;
		echo $cssLine;
		
		
		echo $iparScriptLine;
		if($iparUnwrapCurr === '0') {
			echo('<meta name="iparUnwrapCurr" />');
		}
	}
} else if ($iparTwoLinesVerified == "0" && $iparTwoLinesLocale == "1") {
	add_action('wp_footer','ipar_CH_add_jscss_code', 100);
	function ipar_CH_add_jscss_code() {
		$iparDashID = get_option('ipar_ScriptID');
		$iparUnwrapCurr = get_option('ipar_unwrapCurr');
		$jsLine = '<!-- i-parcel added lines --><script type="text/javascript" src="//script.i-parcel.com/JavaScript?h='. $iparDashID .'"></script>';
		$cssLine = '<link rel="stylesheet" type="text/css" href="//script.i-parcel.com/CSS?h='. $iparDashID .'" />';
		
		
		$iparScriptLine = '<script type="text/javascript" src="'. get_bloginfo('url') .'/wp-content/plugins/ups-i-parcel-e-commerce-logistics-woocommerce/js/iparscript.js"></script>';
		echo $jsLine;
		echo $cssLine;
		
		
		echo $iparScriptLine;
		if($iparUnwrapCurr === '0') {
			echo('<meta name="iparUnwrapCurr" />');
		}
	}
}
//option for WP_Cron catalog sync
$iparSyncCheck = esc_attr(get_option('ipar_catalogConfig'));
if($iparSyncCheck == 2) {
	if ( ! wp_next_scheduled( 'ipar_catalog_sync_cron' ) ) {
		wp_schedule_event( time(), 'daily', 'ipar_catalog_sync_cron' );
	}
} else {
	$iparCronTimestamp = wp_next_scheduled( 'ipar_catalog_sync_cron' );
	wp_unschedule_event( $iparCronTimestamp, 'ipar_catalog_sync_cron' );
}
function ipar_CH_ipar_catalog_sync_cron() {
	
	$ipar_SKUs = array();
	global $wpdb;
	$ipar_synced = 0;
	$ipar_syncOffset = 0;
	$ipar_syncLoadSize = 25;
	$WooCatalogSize = $wpdb->get_results(
		"
		SELECT ID, post_title, post_content, post_name
		FROM $wpdb->posts
		WHERE post_type = 'product' OR post_type = 'product_variation'
		"
	);
	while ($ipar_synced <= count($WooCatalogSize)) {
		//place bulk sync function here...
		$WooProducts = $wpdb->get_results( 
		"
		SELECT ID, post_title, post_content, post_name
		FROM $wpdb->posts
		WHERE post_type = 'product' OR post_type = 'product_variation'
		ORDER BY ID ASC
		LIMIT ". $ipar_syncOffset .",". $ipar_syncLoadSize
		);
		foreach ( $WooProducts as $WooProduct ) {
			$WooProdID = $WooProduct->ID;
			$WooCategory = $wpdb->get_results(
			"
			SELECT name
			FROM $wpdb->terms
			LEFT JOIN $wpdb->term_relationships ON ($wpdb->terms.term_ID = $wpdb->term_relationships.term_taxonomy_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_ID = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->term_relationships.object_id = '$WooProdID'
			AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
			AND $wpdb->terms.name NOT LIKE 'simple'
			AND $wpdb->terms.name NOT LIKE 'group'
			AND $wpdb->terms.name NOT LIKE 'variable'
			AND $wpdb->terms.name NOT LIKE 'external'
			"
			);
			$ipar_catArray = array();
			foreach ($WooCategory as $WooCatName) {
				$ipar_catArray[] = $WooCatName->name;
			}
			$ipar_prodCats = implode(",", $ipar_catArray);
			// SKU Data in array
			$WooSKU = get_post_meta($WooProdID, '_sku', true);
			if($WooSKU == '') {
				$WooSKU = $WooProdID;
			}
			// Product Name
			$iparProdName = $WooProduct->post_title;
			if(strpos($iparProdName, 'Variation') !== false) {
				$iparProdName = explode (' ', $iparProdName, 4);
				$iparProdName = $iparProdName[3];
			}
			// Product Description
			$iparProdDescription = $WooProduct->post_content;
			if($iparProdDescription == '') {
				$iparProdDescription = get_post_meta($WooProdID, '_variation_description', true);
			}
			// Get Country of Origin
			$iparCountryOfOrigin = get_option("ipar_cat_OverrideCountryOfOrigin");
			if( $iparCountryOfOrigin == '') {
				$iparCountryOfOrigin = get_the_terms($WooProdID, get_option("ipar_cat_CountryOfOrigin"));
				$iparCountryOfOrigin = $iparCountryOfOrigin[0]->name;
			}
			if( get_option("ipar_cat_attribute1") != 'none' ) {
				$iparAttribute2 = get_the_terms($WooProdID, get_option("ipar_cat_attribute1"));
				$iparAttribute2 = $iparAttribute2[0]->name;
			} else {
				$iparAttribute2 = "";
			}
			if( get_option("ipar_cat_attribute2") != 'none' ) {
				$iparAttribute3 = get_the_terms($WooProdID, get_option("ipar_cat_attribute2"));
				$iparAttribute3 = $iparAttribute3[0]->name;
			} else {
				$iparAttribute3 = "";
			}
			if( get_option("ipar_cat_attribute3") != 'none' ) {
				$iparAttribute4 = get_the_terms($WooProdID, get_option("ipar_cat_attribute3"));
				$iparAttribute4 = $iparAttribute4[0]->name;
			} else {
				$iparAttribute4 = "";
			}
			if( get_option("ipar_cat_attribute4") != 'none' ) {
				$iparAttribute5 = get_the_terms($WooProdID, get_option("ipar_cat_attribute4"));
				$iparAttribute5 = $iparAttribute5[0]->name;
			} else {
				$iparAttribute5 = "";
			}
			if( get_option("ipar_cat_HTSCodes") != 'none' ) {
				$iparHTSCode = get_the_terms($WooProdID, get_option("ipar_cat_HTSCodes"));
				$iparHTSCode = $iparHTSCode[0]->name;
			} else {
				$iparHTSCode = NULL;
			}
			if( get_option("ipar_cat_shipAlone") != 'none' ) {
				$iparShipAlone = get_the_terms($post->ID, get_option("ipar_cat_shipAlone"));
				$iparShipAlone = $iparShipAlone[0]->name;
			} else {
				$iparShipAlone = NULL;
			}
			$iparSKUWeight = get_post_meta($WooProdID, '_weight', true);
			if($iparSKUWeight === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKUWeight = get_post_meta($iparVariation->get_parent_id(), '_weight', true);
				if($iparSKUWeight === false) {
					$iparSKUWeight = "0";
				}
			}
			$iparSKUHeight = get_post_meta($WooProdID, '_height', true);
			if($iparSKUHeight === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKUHeight = get_post_meta($iparVariation->get_parent_id(), '_height', true);
				if($iparSKUHeight === false) {
					$iparSKUHeight = "0";
				}
			}
			$iparSKUWidth = get_post_meta($WooProdID, '_width', true);
			if($iparSKUWidth === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKUWidth = get_post_meta($iparVariation->get_parent_id(), '_width', true);
				if($iparSKUWidth === false) {
					$iparSKUWidth = "0";
				}
			}
			$iparSKULength = get_post_meta($WooProdID, '_length', true);
			if($iparSKULength === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKULength = get_post_meta($iparVariation->get_parent_id(), '_length', true);
				if($iparSKULength === false) {
					$iparSKULength = "0";
				}
			}
			
			//get Weight unit and convert based on selected value
			$iparMerchantWeightSetting = esc_attr( get_option('woocommerce_weight_unit') );
			if($iparMerchantWeightSetting === 'oz') {
				//convert oz to lbs
				$iparSKUWeight = $iparSKUWeight / 16;
			} else if($iparMerchantWeightSetting === 'g') {
				//convert g to kg
				$iparSKUWeight = $iparSKUWeight / 1000;
			}
			$ipar_SKUs[] = array(
				'SKU' => $WooSKU,
				'ProductName' => $iparProdName,
				'Attribute1' => $ipar_prodCats,
				'Attribute2' => $iparAttribute2,
				'Attribute3' => $iparAttribute3,
				'Attribute4' => $iparAttribute4,
				'Attribute5' => $iparAttribute5,
				//'Attribute6' => $post->post_content,
				'HSCodeUS' => $iparHTSCode,
				'HSCodeCA' => NULL,
				'CountryOfOrigin' => $iparCountryOfOrigin,
				'CurrentPrice' => get_post_meta($WooProdID, '_price', true),
				'ProductURL' => get_permalink($WooProduct->ID),
				'Weight' => $iparSKUWeight,
				'SKN' => null,
				'Length' => $iparSKULength,
				'Width' => $iparSKUWidth,
				'Height' => $iparSKUHeight,
				'ShipAlone' => $iparShipAlone,
				'Delete' => false,
				'WeightInKilos' => null,
				'WeightInKilos' => false,
				'WeightUnit' => null,
				'DimensionUnit' => null
			);
			}
			$ipar_SKUData = array(
				'key' => get_option("ipar_APIPrivateKey"),
				'SKUs' => $ipar_SKUs
			);
			$ipar_WPrequest = array(
				'body' => json_encode($ipar_SKUData),
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
				'cookies' => array()
			);
			$ipar_catalogResponse = wp_remote_post( 'https://webservices.i-parcel.com/api/SubmitCatalog', $ipar_WPrequest );
			$ipar_catalogResponseBody = wp_remote_retrieve_body($ipar_catalogResponse);
			$ipar_responseData = json_decode($ipar_catalogResponseBody, TRUE);
			unset($ipar_SKUs);
			$ipar_SKUs = array();
		$ipar_synced = $ipar_synced + $ipar_syncLoadSize;
		$ipar_syncOffset = $ipar_syncOffset + $ipar_syncLoadSize;
	}
	
}
	
add_action( 'ipar_catalog_sync_cron',  'ipar_CH_ipar_catalog_sync_cron' );
//Add UPS i-parcel Shipping Method
add_filter('woocommerce_shipping_methods', 'ipar_CH_add_ups_iparcel_method');
function ipar_CH_add_ups_iparcel_method( $methods ) {
	$methods['ups_iparcel'] = 'WC_UPS_iparcel_method';
	return $methods;
}
add_action( 'woocommerce_shipping_init', 'ipar_CH_ups_iparcel_method' );
function ipar_CH_ups_iparcel_method() {
	require_once 'templates/ipar_ups-iparcel-shipping.php';
}
//require payment method file
require_once 'templates/ipar_ups-iparcel-payment.php';
//front-end JS to hide payment methods based on shipping selection
add_action( 'wp_enqueue_scripts', 'ipar_CH_include_iparJS' );
function ipar_CH_include_iparJS()
	{
		// Register the script like this for a plugin:
		wp_register_script( 'ipar-frontend-script', plugins_url( '/js/iparscript.js', __FILE__ ), array( 'jquery' ) );
		// Register the script like this for a theme:
		wp_register_script( 'ipar-frontend-script', get_template_directory_uri() . '/js/iparscript.js', array( 'jquery' ) );
		// For either a plugin or a theme, you can then enqueue the script:
		wp_enqueue_script( 'ipar-frontend-script' );
	}
/* Close WooCommerce Check */
}
?>