<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<script>
jQuery(document).ready(function() {		
	jQuery("#GetKey").click(function () {
		var DashRequest = new Object();
		DashRequest.username = jQuery('input#ipar_DashUsername').val();
		DashRequest.password = jQuery('input#ipar_DashPassword').val();
	
		jQuery.ajax({
			url: 'https://globalaccess.i-parcel.com/api/PublicKeys',
			type: 'POST',
			dataType: 'json',
			data: DashRequest,
			success: function (data, textStatus, xhr) {
				jQuery('#ipar_form_config').show();
				jQuery('.APIusernamePassword').hide();
				jQuery('#ipar_form_config input#ipar_APIKey').val(data['Results'][0]['PublicKey']);
				jQuery('#ipar_form_config input#ipar_APIPrivateKey').val(data['Results'][0]['PrivateKey']);
				jQuery('#ipar_form_config input#ipar_ScriptID').val(data['Results'][0]['ScriptId']);
				jQuery('.iparResponse').html('<div class="updated notice"><p><b>SUCCESS!</b>  Your keys and script ID have been added to your settings.  Be sure to click <b>Save Changes!</b></p></div>');
			},
			error: function (xhr, textStatus, errorThrown) {
				jQuery('.iparResponse').html('<div class="error notice"><p>'+ xhr['responseJSON']['Error'] +'</p></div>');
			}
		});
	});
	var ipar_SubmitParcelVal = "<?php echo esc_attr( get_option('ipar_SubmitParcel') ); ?>";
	var ipar_enableLinesVal = "<?php echo esc_attr( get_option('ipar_enableLines') ); ?>";
	var ipar_linesLocaleVal = "<?php echo esc_attr( get_option('ipar_linesLocale') ); ?>";
	var ipar_unwrapCurr = "<?php echo esc_attr( get_option('ipar_unwrapCurr') ); ?>";
	var ipar_intlEnabledVal = "<?php echo esc_attr( get_option('ipar_intlEnabled') ); ?>";
	var ipar_taxModeVal = "<?php echo esc_attr( get_option('ipar_taxMode') ); ?>";
	var ipar_shopPageURL = "<?php echo esc_attr( get_option('ipar_shopURL') ); ?>";
	var ipar_cancelPageURL = "<?php echo esc_attr( get_option('ipar_cancelURL') ); ?>";
	var ipar_logging = "<?php echo esc_attr( get_option('ipar_logging') ); ?>";
	jQuery('select#ipar_SubmitParcel').find('option[value="'+ ipar_SubmitParcelVal +'"]').attr('selected','selected');
	jQuery('select#ipar_enableLines').find('option[value="'+ ipar_enableLinesVal +'"]').attr('selected','selected');
	jQuery('select#ipar_linesLocale').find('option[value="'+ ipar_linesLocaleVal +'"]').attr('selected','selected');
	jQuery('select#ipar_unwrapCurr').find('option[value="'+ ipar_unwrapCurr +'"]').attr('selected','selected');
	jQuery('select#ipar_intlEnabled').find('option[value="'+ ipar_intlEnabledVal +'"]').attr('selected','selected');
	jQuery('select#ipar_shopURL').find('option[value="'+ ipar_shopPageURL +'"]').attr('selected','selected');
	jQuery('select#ipar_cancelURL').find('option[value="'+ ipar_cancelPageURL +'"]').attr('selected','selected');
	if(ipar_logging != '') {
		jQuery('input#ipar_logging').attr('checked', 'checked'); 
	} else {
	   jQuery('input#ipar_logging').removeAttr('checked'); 
	}
});
</script>
<div class="wrap">
<h2>i-parcel Global Access Configuration</h2>
<?php if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.') ?></strong></p>
    </div>
<?php } ?>
	<div id="poststuff">
    	<div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
				<?php settings_fields( 'iparconfig-group' ); ?>
                <?php do_settings_sections( 'iparconfig-group' ); ?>
                <?php if ( esc_attr( get_option('ipar_APIKey') ) === '') { ?>
					<div class="iparResponse"></div>
					<!-- Dashboard Username and Password -->
					<div class="postbox APIusernamePassword">
						<h3 class="hndle"><span>i-parcel Dashboard Credentials</span></h3>
						<div class="inside">
							<div style="padding:0px 0px 10px 0px;">
								<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Username:</label><input name="ipar_DashUsername" id="ipar_DashUsername" value="" type="text" style="display:inline-block;width:300px;" />
							</div>
							<div style="padding:0px 0px 10px 0px;">
								<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Password:</label><input name="ipar_DashPassword" id="ipar_DashPassword" value="" type="password" style="display:inline-block;width:300px;" />
							</div>
							<div>
								<div class="button button-primary" id="GetKey">Get Your API Key</div>
							</div>
						</div>
					</div>
					<form name="ipar_form_config" id="ipar_form_config" method="post" action="options.php" style="display:none;">
						<?php settings_fields( 'iparconfig-group' ); ?>
         				<?php do_settings_sections( 'iparconfig-group' ); ?>
						<!-- API Key -->
						<div class="postbox">
							<h3 class="hndle"><span>API Key</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Public API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Public API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIKey" id="ipar_APIKey" value="" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIPrivateKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Private API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Private API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIPrivateKey" id="ipar_APIPrivateKey" value="" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_ScriptID" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Script ID: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">The ID used for front-end scripts of the website.</span></div></label><input name="ipar_ScriptID" id="ipar_ScriptID" value="" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
						</div>
						<!-- Save Changes Button -->
						<div>
							<?php submit_button('Save Changes', 'primary', '', false); ?>
						</div>
					</form>
					<!-- Fire off quest to get API key -->
				<?php } else { ?>
					<form name="ipar_form_config" id="ipar_form_config" method="post" action="options.php">
						<?php settings_fields( 'iparconfig-group' ); ?>
          				<?php do_settings_sections( 'iparconfig-group' ); ?>
						<!-- API Key -->
						<div class="postbox">
							<h3 class="hndle"><span>API Keys and Script ID</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Public API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Public API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIKey" id="ipar_APIKey" value="<?php echo esc_attr( get_option('ipar_APIKey') ); ?>" type="text" style="display:inline-block;width:300px;" />
								</div>
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIPrivateKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Private API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Private API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIPrivateKey" id="ipar_APIPrivateKey" value="<?php echo esc_attr( get_option('ipar_APIPrivateKey') ); ?>" type="text" style="display:inline-block;width:300px;" />
								</div>
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_ScriptID" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Script ID: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">The ID used for front-end scripts of the website.</span></div></label><input name="ipar_ScriptID" id="ipar_ScriptID" value="<?php echo esc_attr( get_option('ipar_ScriptID') ); ?>" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
						</div>
						<div class="postbox">
						<h3 class="hndle"><span>Front-end Settings</span></h3>
						<div class="inside">
							<div style="padding:0px 0px 10px 0px;">
								<label for="ipar_enableLines" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Enable Front-end Scripts: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Place the two lines of code from the UPS i-parcel Dashboard to enable a variety of front-end features.</span></div></label><select name="ipar_enableLines" id="ipar_enableLines" style="display:inline-block;"><option value="0">Yes</option><option value="1">No</option></select>
							</div>
							<div style="padding:0px 0px 10px 0px;">
															
								<label for="ipar_linesLocale" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Scripts Location: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Should two lines be placed the header or in the footer of the website?</span></div></label><select name="ipar_linesLocale" id="ipar_linesLocale" style="display:inline-block;"><option value="0">Header</option><option value="1">Footer</option></select>
							</div>
							<div style="padding:0px 0px 10px 0px;">
								<label for="ipar_unwrapCurr" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Unwrap Currency Symbols: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Some themes wrap the currency symbol in spans which prevents our script from converting prices.  Enabling this helps the script sweep currencies.</span></div></label><select name="ipar_unwrapCurr" id="ipar_unwrapCurr" style="display:inline-block;"><option value="0">Yes</option><option value="1">No</option></select>
							</div>
						</div>
						</div>
						<!-- Sale settings -->
						<div class="postbox">
							<h3 class="hndle"><span>Sales</span></h3>
							<div class="inside">
								<!-- <div style="padding:0px 0px 10px 0px;">
									<label for="ipar_suppressEmails" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Suppress Transactional e-mails:</label><select name="ipar_suppressEmails" id="ipar_suppressEmails" style="display:inline-block;"><option value="0">Yes</option><option value="1">No</option></select>
								</div>  -->
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_suppressEmails" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Notification URL: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Copy and Paste this URL in your UPS i-parcel Dashboard so we can send the payment notification to your WooCommerce website.  This setting is found in the Dashboard under "Notifications"</span></div></label><input type="" disabled="disabled" value="<?php echo bloginfo('url'); ?>/?ordernotification=ipar" style="display:inline-block;width:600px;border:0px;color:#000;" />
								</div>
							</div>
						</div>
						<!-- Handoff settings -->
						<div class="postbox">
							<h3 class="hndle"><span>Cart Handoff Settings</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_shopURL" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Store Page: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">The main page of your WooCommerce store.</span></div></label>
									<select name="ipar_shopURL" id="ipar_shopURL" style="display:inline-block;width:600px;"> 
										<option value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
										<?php 
										$pages = get_pages(); 
										foreach ( $pages as $page ) {
											$option = '<option value="' . get_page_link( $page->ID ) . '">';
											$option .= $page->post_title;
											$option .= '</option>';
											echo $option;
										}
										?>
									</select>
								</div>
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_cancelURL" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Cancel Page: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">The page that we redirect to if shopper cancels on the UPS i-parcel handoff page.</span></div></label>
									<select name="ipar_cancelURL" id="ipar_cancelURL" style="display:inline-block;width:600px;"> 
										<option value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
										<?php 
										$pages = get_pages(); 
										foreach ( $pages as $page ) {
											$option = '<option value="' . get_page_link( $page->ID ) . '">';
											$option .= $page->post_title;
											$option .= '</option>';
											echo $option;
										}
										?>
									</select>
								</div>
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_imageURL" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Logo Image URL: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">URL of logo which displays on the i-parcel handoff page.</span></div></label><input name="ipar_imageURL" id="ipar_imageURL" value="<?php echo esc_attr( get_option('ipar_imageURL') ); ?>" type="text" style="display:inline-block;width:600px;" />
								</div>
							</div>
						</div>
						<!-- Advanced settings -->
						<div class="postbox">
							<h3 class="hndle"><span>Advanced Settings</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_shopURL" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Enable Logging: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Enable/Disable logging of the API calls by our plug-in.</span></div></label>
									<input type="checkbox" name="ipar_logging" id="ipar_logging" value="true" />
								</div>
							</div>
						</div>
						<!-- Save Changes Button -->
						<div>
							<?php submit_button('Save Changes', 'primary', '', false); ?>
						</div>
					</form>
				<?php } ?>
            </div>
            <div id="postbox-container-1">
                <div class="postbox">
					<h3 class="hndle"><span>Need Help?</span></h3>
                	<div class="inside">
                    	<p>If you need help with any part of the boarding process or are running into issues, please contact us.</p>
						<p>You can log into your UPS i-parcel Dashboard at anytime at <a href="https://globalaccess.i-parcel.com/" target="_blank">globalaccess.i-parcel.com</a>.</p>
                        <p>You can contact our IT department by visiting <a href="https://www.i-parcel.com/en/it-helpdesk/" target="_blank">i-parcel.com/en/it-helpdesk</a></p>
                    </div>
                </div>
            </div>
   		</div>        
    </div>
</div>