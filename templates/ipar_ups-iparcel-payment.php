<?php
add_action('plugins_loaded', 'ipar_CH_custom_gateway_class');
function ipar_CH_custom_gateway_class(){
    class WC_UPS_iparcel_payment extends WC_Payment_Gateway {
        public $domain;
        public function __construct() {
            $this->domain = 'ups_iparcel_cart_andoff';
            $this->id                 = 'ups_iparcel_payment';
            $this->has_fields         = false;
            $this->method_title       = __( 'UPS i-parcel', $this->domain );
            $this->method_description = __( 'Provide an international checkout option for international shoppers.', $this->domain );
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();
            // Define user set variables
            $this->title        = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );
            $this->ipar_order_status_before = $this->get_option( 'ipar_order_status_before', 'wc-pending' );
            $this->ipar_order_status_after = $this->get_option( 'ipar_order_status_after', 'wc-processing' );
            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_custom', array( $this, 'thankyou_page' ) );
            // Customer Emails
            // add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
        }
        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', $this->domain ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable UPS i-parcel Payment Method', $this->domain ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => __( 'Title', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
                    'default'     => __( 'UPS i-parcel', $this->domain ),
                    'desc_tip'    => true,
                ),
                'ipar_order_status_before' => array(
                    'title'       => __( 'Order Status (Before Payment)', $this->domain ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'Select your desired order status before UPS i-parcel Handoff occurs.', $this->domain ),
                    'default'     => 'wc-pending',
                    'desc_tip'    => true,
                    'options'     => wc_get_order_statuses()
                ),
				'ipar_order_status_after' => array(
                    'title'       => __( 'Order Status (After Payment)', $this->domain ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'Select your desired order status after UPS i-parcel Handoff and order is paid.', $this->domain ),
                    'default'     => 'wc-processing',
                    'desc_tip'    => true,
                    'options'     => wc_get_order_statuses()
                ),
                'description' => array(
                    'title'       => __( 'Description', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'You will be redirected to UPS i-parcel to complete checkout.', $this->domain ),
                    'default'     => __('Payment Information', $this->domain),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __( 'Instructions', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
            );
        }
        /**
         * Output for the order received page.
         */
        public function thankyou_page() {
            if ( $this->instructions )
                echo wpautop( wptexturize( $this->instructions ) );
        }
        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         */
        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( $this->instructions && ! $sent_to_admin && 'custom' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
                echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
            }
        }
        public function payment_fields(){
            if ( $description = $this->get_description() ) {
                echo wpautop( wptexturize( $description ) );
            }
        }
        /**
         * Process the payment and return the result.
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment( $order_id ) {
            $iparOrder = new WC_Order( $order_id );		
			$iparShipping = $iparOrder->get_items( 'shipping' );	
			$serviceLevelOrderID = array_keys($iparShipping);
			$iparServiceLevelID = wc_get_order_item_meta($serviceLevelOrderID[0], 'UPS-iparcel-service-ID', true);			
			//to get shopper's cart contents - SKU and Qty
			$items = $iparOrder->get_items();
			//create array of items for SetCheckout call
			foreach($items as $item => $values) { 
				$product = wc_get_product( $values['product_id'] );
				if ( $product->is_type( 'variable' ) ) {
					//$productSKU = "VARIABLE_SKU";
					$product = wc_get_product( $values['variation_id'] );
					$productSKU = $product->get_sku();
					if ($productSKU === '') {
						$productSKU = $values['variation_id'];
					}
				} else {
					//$productSKU = "SIMPLE_SKU";
					$productSKU = $product->get_sku();
					if ($productSKU === '') {
						$productSKU = $values['product_id'];
					}
				}
				$iparSKUlist[] = array(
					'item_number' => $productSKU,
					'quantity' => $values['quantity'],
					'item_name' => $values['name'],
					'amount' => $values['line_total']
				);
			}
			//get shoppers shipping/billing information
			$iparOrder_meta = get_post_meta($order_id);
			if ( !isset($iparOrder_meta["_shipping_email"][0]) ) {
				$iparOrderEmail = $iparOrder_meta["_billing_email"][0];
			} else {
				$iparOrderEmail = $iparOrder_meta["_shipping_email"][0];
			}
			//SetCheckout Request
			$iparHandoffRequest = array(
				'key' => get_option("ipar_APIPrivateKey"),
				'currency_code' => get_woocommerce_currency(),
				'page_currency' => rtrim(explode("\"", $_COOKIE['ipar_iparcelSession'])[11], "\\"),
				//'discount_amount_cart' => $iparOrder->get_total_discount(),
				'discount_rate_cart' => '0',
				'cn' => '',
				'no_note' => '0',
				'reference_number' => $order_id,
				'custom' => '',
				'invoice' => '',
				'return' => $this->get_return_url( $iparOrder ),
				'shopping_url' => get_option("ipar_shopURL"),
				'shippingmarkupfixed' => 0,
				'cancel_return' => get_option("ipar_cancelURL"),
				'image_url' => get_option("ipar_imageURL"),
				'cs' => '0',
				'AddressInfo' => array(
					'Billing' => array(
						'email' => $iparOrderEmail,
						'first_name' => $iparOrder_meta["_billing_first_name"][0],
						'last_name' => $iparOrder_meta["_billing_last_name"][0],
						'address1' => $iparOrder_meta["_billing_address_1"][0],
						'address2' => $iparOrder_meta["_billing_address_2"][0],
						'city' => $iparOrder_meta["_billing_city"][0],
						'zip' => $iparOrder_meta["_billing_postcode"][0],
						'state' => $iparOrder_meta["_billing_state"][0],
						'country' => $iparOrder_meta["_billing_country"][0],
						'phone' => $iparOrder_meta["_billing_phone"][0]
					),
					'Shipping' => array(
						'shipping_email' => $iparOrderEmail,
						'shipping_first_name' => $iparOrder_meta["_shipping_first_name"][0],
						'shipping_last_name' => $iparOrder_meta["_shipping_last_name"][0],
						'shipping_address1' => $iparOrder_meta["_shipping_address_1"][0],
						'shipping_address2' => $iparOrder_meta["_shipping_address_2"][0],
						'shipping_city' => $iparOrder_meta["_shipping_city"][0],
						'shipping_zip' => $iparOrder_meta["_shipping_postcode"][0],
						'shipping_state' => $iparOrder_meta["_shipping_state"][0],
						'shipping_country' => $iparOrder_meta["_shipping_country"][0]
						//'shipping_phone' => $iparOrder_meta["_billing_phone"][0]
					),
				),
				'control_number' => '',
				"day_phone_b" => $iparOrder_meta["_billing_phone"][0],
				'ItemDetailsList' => $iparSKUlist,
				'ddp' => '1',
				'servicelevel' => $iparServiceLevelID,
				'prepaidamount' => '0'
			);
			
			$ipar_WPrequest = array(
				'body' => json_encode($iparHandoffRequest),
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
				'cookies' => array()
			);
			
			//if SetCheckout is success set order to pending and redirect to pay.i-parcel.com else redirect back to cart and display
			$ipar_checkoutResponse = wp_remote_post( 'https://pay.i-parcel.com/v1/api/SetCheckout', $ipar_WPrequest );
			$ipar_checkoutResponseBody = wp_remote_retrieve_body($ipar_checkoutResponse);
			$ipar_parcelResponseData = json_decode($ipar_checkoutResponseBody, TRUE);
			
			//if logging is enabled, log the response from SetCheckout
			if(get_option('ipar_logging') != '') {
				$iparPluginlog = plugin_dir_path(__FILE__).'debug.log';
				$iparLogMessage = date("Y-m-d H:i:s").'_SetCheckoutResponse: '.$ipar_checkoutResponseBody.PHP_EOL;
				error_log($iparLogMessage, 3, $iparPluginlog);
			}
			
			//print_r(json_encode($iparHandoffRequest));
			//echo('<p>&nbsp;</p>');
			//print_r($ipar_parcelResponseData);
			
			$iparTrackingNum = $ipar_parcelResponseData['tx'];
			$iparInvalidItems[] = $ipar_parcelResponseData['InvalidItems'];
			if($iparTrackingNum === 'NO-VALID-ITEMS') {
				//redirect back to cart with invalid items
				
			} else if($iparTrackingNum != '') {
				$iparStatusBefore = get_option("woocommerce_ups_iparcel_payment_settings");
				//echo('Before Order Status:'. $iparStatusBefore['ipar_order_status_before']);
				
				$iparOrder->update_status( $iparStatusBefore['ipar_order_status_before'] );
				
				wc_reduce_stock_levels($iparOrder);
				
				return array(
					'result'    => 'success',
					'redirect'  => 'https://pay.i-parcel.com/v1/Cart?key='. get_option("ipar_APIKey") .'&tx='. $iparTrackingNum
            	);
			}			
        }
    }
}

add_filter( 'woocommerce_payment_gateways', 'add_UPS_iparcel_payment_class' );
function add_UPS_iparcel_payment_class( $methods ) {
    $methods[] = 'WC_UPS_iparcel_payment'; 
    return $methods;
}
add_action('woocommerce_checkout_process', 'process_UPS_iparcel_payment');
function process_UPS_iparcel_payment(){
    if($_POST['payment_method'] != 'custom')
        return;
}

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'ipar_CH_payment_update_order_meta' );
function ipar_CH_payment_update_order_meta( $order_id ) {
    if($_POST['payment_method'] != 'custom')
        return;
}