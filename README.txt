=== UPS i-parcel E-Commerce & Logistics ===

Contributors: upsiparcel
Tags: UPS Shipping International Shipping, i-parcel, UPS, Shipping Method, Payment Method
Requires at least: 4.0
Tested up to: 5.3
Requires PHP: 5.6.30
Stable tag: 1.4.5
Version: 1.4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable UPS i-parcel shipping method in your checkout for your international shoppers.

== Description ==

Integrate the UPS i-parcel E-Commerce & Logistics plug-in into your WooCommerce website to offer a shipping option for your international shoppers as well as a International Payment gateway to collect international payment.  Requires a UPS i-parcel Global Access account.  Shipping quotes include shipping costs, tax and duty for exporting.  Plug-in creates a new payment method for all international orders.

For the merchant, orders have links to generate UPS i-parcel labels and barcodes from the order details.

== Installation ==

1. Install plug-in from WordPress.org or upload extracted .zip into your "wp-content/plugins" directory.

2. Activate the plugin through the "Plugins" menu in WordPress.

3. Navigate to the "i-parcel" option in your admin's navigation

4. Enter your UPS i-parcel Global Access username and password to fetch your API keys and Script ID.  Save your settings

5. Complete main configuration options and save settings.

6. Navigate to i-parcel > Catalog Sync settings.  Populate your catalog sync options and save your settings.

7. Click the "Upload Catalog" button to sync your WooCommerce catalog to your UPS i-parcel Dashboard.

8. Navigate to i-parcel > Shipping Methods and add the UPS i-parcel shipping method to the zones you want UPS i-parcel to be available in.

9. Navigate to i-parcel > Payment Methods and click "Manage" button on the "UPS i-parcel" payment method.

== Frequently Asked Questions ==

No FAQs yet.  Questions? Visit https://www.i-parcel.com/en/contact-us/

== Screenshots ==

1. main config screen

2. catalog syncing options

3. UPS i-parcel shipping method options

4. UPS i-parcel payment method options

== Changelog ==
1.0.0 - initial plug-in1.2.0 - Updated to include Quote 2.0 API.  Updated catalog sync process to use WooCommerce Product Attributes to pass custom data to i-parcel catalog.
1.2.1 - Added catalog validation to ensure country of origin is provided.  Added more tooltips.
1.2.2 - Updated iparscript.js file to remove/show payment methods depending on selected shipping method.
1.2.3 - Removed timeout call in iparscript.js to remove issue of duplicate welcome links
1.2.4 - Updated tax and duty to be included in shipping costs if woocommerce tax calc is disabled.  Also fixed up iparcel is not defined issue.
1.2.5 - Updated bulk catalog sync to send in chunks of 25 SKUs per request.  Also added option to load JS scripts in header or footer based on user setting.
1.2.6 - Created Daily Catalog Sync option in Catalog settings.  Also fixed missing character issue in iparscript.js file
1.2.7 - Updated Tax and Duty statement in iparscript.js file
1.2.8 - Added OZ and G weight conversions to LB and KG.  Removed ton of white space.  Updated iparscript.js to not do currency sweep on document ready to prevent conflicts with dashboard JS.  Updated handoff function to use different API key.
1.2.9 - Fixed conflict with UPS i-parcel payment method interferring with WooCommerce Endpoints in WordPress Menu Creation admin panel
1.3.0 - Fixed Front-end Script settings not showing correct status after configuration save.  Added ability to send product variant SKUs in quote and setcheckout requests.  Added timeout around currency sweep on ajaxcomplete.
1.3.1 - Added admin warnings to educate merchant on correct tax settings for international shoppers.  Also updated Shop URL and Cancel URL inputs to be select elements which all page URLs in wp-admin
1.3.2 - Updated Quote and SetCheckout requests to send store currency instead of hardcoded USD value.  Also updated iparscript.js to suppress PayPal Express options from cart if selecting UPS i-parcel Shipping Method.
1.3.3 - Include iparscript.js with i-parcel hosted lines in head or footer depending on settings.  Also use parent product weights and dims if left empty in variant.
1.3.4 - Apply fix for SubmitCatalog if product has empty weights or dims (passes "0" instead of "false")
1.3.5 - Applied fix to prevent UPS i-parcel payment method from showing if cart is full of digital downloads or order doesn't have shipping destination.
1.3.6 - Fixed JS error if shipping quote is disabled in cart.  Also updated to hide/show payment methods if shipping options are housed in select element.
1.3.7 - Updated Ship Alone in catalog sync to not be hardcoded but configurable using a WooCommerce Product Attribute.
1.3.8 - Updated Quote call to included any "Handling" values from Quote response1.3.9 - Updated SetCheckout call to remove Discount value getting passed as WooCommerce handles all discounted values pre-handoff.  Also updated GetCheckoutDetails to get order details if shopper changed address details or shipping services on UPS i-parcel handoff page.
1.3.9 - Updated SetCheckout to remove discount line as items are discounted ahead of time in WooCommerce
1.4.0 - Added option to include logging of i-parcel API calls.  Created options to set Order Status before and after handoff page to help identify i-parcel orders.
1.4.1 - Updated line which confirms shipping method from handoff page into the order
1.4.2 - Updated plug-in to use service level names returned from Quote API response.
1.4.3 - Updated plug-in to prevent admin freezing when product variant is created while i-parcel "Auto Catalog Updates:" setting is set to "On Product Save".
1.4.4 - Added section to send out New Order email if GetCheckoutDetails is success.
1.4.5 - Updated Quote to check for error response if parcel contains SKUs not in the dashboard catalog.

== Upgrade Notice ==
A new version of UPS i-parcel E-Commerce & Logistics is available