var iparcelExtJs = {

	markupExclusions: ["body.woocommerce-cart ul#shipping_method label .woocommerce-Price-amount","body.woocommerce-cart tr.tax-total td .woocommerce-Price-amount","body.woocommerce-cart tr.cart-discount .woocommerce-Price-amount","body.woocommerce-checkout ul#shipping_method .woocommerce-Price-amount","body.woocommerce-checkout tr.tax-total td .woocommerce-Price-amount","body.woocommerce-checkout tr.cart-discount .woocommerce-Price-amount","body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr:contains(Shipping:) td .woocommerce-Price-amount","body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr:contains(Tax:) td .woocommerce-Price-amount"],

	//function to recalculate total since rev share is not included in shipping/taxes anymore.

	onCurrencySwept: function(){

	},

}

jQuery( document ).ajaxComplete(function() {

	//var selectedShippingMethod = jQuery('#order_review td[data-title="Shipping"] li:has(input:checked) input').attr('value');

	//Update Tax label on cart and hide non-standard payment methods

	if(jQuery('form.woocommerce-shipping-calculator').length && jQuery('.cart-collaterals tr.shipping').length) {

		if(jQuery('.cart-collaterals tr.shipping input.shipping_method').attr('value').indexOf('ups_iparcel_') === 0 || jQuery('.cart-collaterals tr.shipping li:has(input:checked) input').attr('value').indexOf('ups_iparcel_') === 0) {

			jQuery('.cart-collaterals tr.tax-total th').text('Tax & Duty');

			//hide PayPal Express Checkout options

			jQuery('.wcppec-checkout-buttons').hide();

		}

	} else {

		jQuery('.cart-collaterals tr.tax-total th').text('Tax');

		//show PayPal Express Checkout options

		jQuery('.wcppec-checkout-buttons').show();

	}

	//Update Tax label on checkout

	if(jQuery('form.woocommerce-checkout').length && jQuery('table.woocommerce-checkout-review-order-table tr.shipping').length) {

		if(jQuery('table.woocommerce-checkout-review-order-table tr.shipping td input.shipping_method[value*="ups_iparcel_"]').length === 1 && jQuery('table.woocommerce-checkout-review-order-table tr.shipping td input').length === 1 || jQuery('table.woocommerce-checkout-review-order-table tr.shipping td select.shipping_method').length === 1 && jQuery('table.woocommerce-checkout-review-order-table tr.shipping td select.shipping_method').val().indexOf('ups_iparcel_') === 0 || jQuery('table.woocommerce-checkout-review-order-table tr.shipping td input:checked[value*="ups_iparcel_"]').length) {

			jQuery('ul.wc_payment_methods.payment_methods li').each(function() {

				if(jQuery(this).attr('class').split('payment_method_')[1] !== 'ups_iparcel_payment') {

					jQuery(this).hide();

				}

			});

			jQuery('li.wc_payment_method.payment_method_ups_iparcel_payment label').click();
			
			jQuery('li.wc_payment_method.payment_method_ups_iparcel_payment').show();

			jQuery('li.wc_payment_method.payment_method_ups_iparcel_payment .payment_box.payment_method_ups_iparcel_payment').show();

			jQuery('#order_review tfoot tr.tax-total th').text('Tax & Duty');

		} else {

			jQuery('ul.wc_payment_methods.payment_methods li').each(function() {

				if(jQuery(this).attr('class').split('payment_method_')[1] === 'ups_iparcel_payment') {

					jQuery(this).hide();

				} else {

					jQuery(this).show();

				}

			});

			jQuery('#order_review tfoot tr.tax-total th').text('Tax');

			jQuery('li.wc_payment_method:eq(0) label').click();

		}

	}

	//hide UPS i-parcel payment method if no shipping address is present (usually for digital downloads)

	if(jQuery('table.woocommerce-checkout-review-order-table tr.shipping').length === 0) {

		jQuery('ul.wc_payment_methods li.payment_method_ups_iparcel_payment').hide();

	}

	jQuery('span.woocommerce-Price-amount').each(function() {

		jQuery(this).text(jQuery(this).text());

	});

	//if iparcel script is not defined, do not sweep prices

	setTimeout(function() {

		if(typeof iparcel === 'undefined') {

			//do  nothing

		} else {

			iparcel.currency.sweepAllPrices();

		}	

	}, 1000);

});

jQuery( document ).ready(function() {

	//unwrap price elements if meta[name="iparUnwrapCurr"] exist

	if(jQuery('meta[name="iparUnwrapCurr"]').length) {

		jQuery('span.woocommerce-Price-amount').each(function() {

			jQuery(this).text(jQuery(this).text());

		});

		//fire off again after initial function in case any where missed

		setTimeout(function() {

			jQuery('span.woocommerce-Price-amount').each(function() {

				jQuery(this).text(jQuery(this).text());

			});

		}, 250);

	}

	//on product variant change

	jQuery('form table.variations select, form table.variations input[type="radio"]').on('change', function() {

		jQuery('span.woocommerce-Price-amount').each(function() {

			jQuery(this).text(jQuery(this).text());

		});

		//fire off again after initial function in case any where missed

		setTimeout(function() {

			jQuery('span.woocommerce-Price-amount').each(function() {

				jQuery(this).text(jQuery(this).text());

			});

			if(typeof iparcel === 'undefined') {

				//do  nothing

			} else {

				iparcel.currency.sweepAllPrices();

			}

		}, 250);

	});

});