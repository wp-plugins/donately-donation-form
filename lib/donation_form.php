<?php

	if( isset($tracking) ){
		$tracking = html_entity_decode(urldecode(str_replace('"', '', $tracking)));
	}

	if( !defined('DNTLY_VERSION') ){
		print "Donately Plugin must be activated.";
		die();
	}

	if( !isset($dntly_options) ){
		$dntly_options = get_option('dntly_options');
	}

	if( !isset($thank_you_url) && isset($dntly_options['thank_you_page']) ){
		$thank_you_url = get_permalink( $dntly_options['thank_you_page'] );
	}

	$dntly = new DNTLY_API;
	$donate_without_auth_url = $dntly->build_url("donate_without_auth");

	if( !isset($dntly->dntly_options['account_id']) ){	
		$donations_disabled = true;
	}
	elseif($campaign_id){
		$campaign_post = new WP_Query(array(
			'meta_key'		=> '_dntly_id',
			'meta_value'	=> $campaign_id,
			'post_type'		=> 'dntly_campaigns',
			'post_status'	=> array('publish', 'private')
		));
		if( isset($campaign_post->posts) ){
			$dntly_environment = get_post_meta($campaign_post->posts[0]->ID, '_dntly_environment', true);
			if( isset($dntly_environment) && $dntly->dntly_options['environment'] != $dntly_environment ){
				$donations_disabled = true;
			}		
		}
	}

	$donations_disabled_js = null;
	if( isset($donations_disabled) ){
		$donations_disabled_js = "alert('This Campaign is not able to accept donations');return false;";
	}

	$layout = isset($_GET['layout']) ? $_GET['layout'] : null; //grab form type from URL

?>
<script type="text/javascript">
	if(!window.console) console = {log: function() {}};
	function handle_response(response, error){
		if(error === undefined){error = false;}
		try{
			var r = JSON.parse(response);
		}
		catch(e){
			var r = response;
		}
		if(error){
			console.log(r);
			alert("Error Connecting\nPlease try again.");
		}
		else{
			if(!r.success){
				alert("Error\n\n" + r.error.message);
			}
			else{
				console.log(r);
				<?php if( $thank_you_url ): ?>
					window.top.location.href = '<?php print $thank_you_url ?>';
					reset_all();
				<?php else: ?>
					alert("Thanks for your Donation!");
					reset_all();
				<?php endif; ?>
			}
		}
		jQuery("#submit_btn", donate_form).text("SUBMIT DONATION");
	}

	function reset_all(){
		jQuery("input[name=recurring]", donate_form).val([]);
		jQuery("input[name=first_name]", donate_form).val('');
		jQuery("input[name=last_name]", donate_form).val('');
		jQuery("input[name=phone]", donate_form).val('');
		jQuery("input[name=email]", donate_form).val('');
		jQuery("input[name=address]", donate_form).val('');
		jQuery("input[name=address2]", donate_form).val('');
		jQuery("input[name=city]", donate_form).val('');
		jQuery("select[name=state]", donate_form).val([]);
		jQuery("input[name=zip_code]", donate_form).val('');
		jQuery("select[name=country]", donate_form).val([]);
		jQuery("input[name=card_number]", donate_form).val('');
		jQuery("input[name=card_code]", donate_form).val('');
		jQuery("input[name=expiration_month]", donate_form).val('');
		jQuery("input[name=expiration_year]", donate_form).val('');
		jQuery("input[name=amount]", donate_form).val('');
	}

	function intialize_donation_form(){
		jQuery.support.cors = true;
		donate_form   = jQuery('form#dntly_donate');
		donate_url    = "<?php print $donate_without_auth_url ?>";
		donate_form.find('fieldset.donation_buttons .donation-preset').bind('click', function(e) {
			e.preventDefault();
			var amt = jQuery(this).attr('donation-preset');
			jQuery("input[name=amount]", donate_form).val(amt);
		})
		donate_form.find('#submit_btn').bind('click', function(e) {
			e.preventDefault();
			<?php print $donations_disabled_js ?>
			jQuery.validator.messages.required = ""; //remove error validation messages
			if(!donate_form.valid()){
				jQuery('label.error').hide(); //hide the error labels fields 
				return false;
			}
			//change donate button to processing
			jQuery("#submit_btn", donate_form).text("Processing...");

			var amount = jQuery("input[name=amount]", donate_form).val();
			var amount_clean = amount.replace("$", ""); amount_clean = amount_clean.replace(",", "");
			if (amount_clean.indexOf(".") >= 0){
				var amount_in_cents = amount_clean.replace(".", "");
			}else{
				var amount_in_cents = Math.round(parseInt(amount_clean * 100, 10));
			}
			var recurring 	    = (jQuery("input[name=recurring]:checked", donate_form).val()>=1?true:false);
			var first_name 	    = jQuery("input[name=first_name]", donate_form).val();
			var last_name 	    = jQuery("input[name=last_name]", donate_form).val();
			var phone_number 		= jQuery("input[name=phone]", donate_form).val();
			var email 			    = jQuery("input[name=email]", donate_form).val();
			var street_address 	= jQuery("input[name=address]", donate_form).val();
			var street_address_2= jQuery("input[name=address2]", donate_form).val();
			var city 				    = jQuery("input[name=city]", donate_form).val();
			var state 			    = jQuery("select[name=state] option:selected", donate_form).val();
			var zip_code 		    = jQuery("input[name=zip_code]", donate_form).val();
			var country 		    = jQuery("select[name=country] option:selected", donate_form).val();
			var number 		      = jQuery("input[name=card_number]", donate_form).val();
			var cvc 				    = jQuery("input[name=card_code]", donate_form).val();
			var exp_month 	    = jQuery("input[name=expiration_month]", donate_form).val();
			var exp_year 		    = jQuery("input[name=expiration_year]", donate_form).val();
			var card_hash 	    = {"number": number, "exp_month": exp_month, "exp_year": exp_year, "cvc": cvc};
			var anonymous		    = false;
			var campaign_id     = "<?php print (isset($campaign_id) ? $campaign_id : ''); ?>";
			var fundraiser_id   = "<?php print (isset($fundraiser_id) ? $fundraiser_id : ''); ?>";
			var dump						= "<?php print (isset($tracking) ? $tracking : ''); ?>";

			var data 						= {
															'email'							: email,
															'amount_in_cents'		: amount_in_cents,
															'card'							: card_hash,
															'recurring'					: recurring,
															'anonymous'					: anonymous,
															'first_name'				: first_name,
															'last_name'					: last_name,
															'phone_number'			: phone_number,
															'street_address'		: street_address,
															'street_address_2'	: street_address_2,
															'city'							: city,
															'state'							: state,
															'zip_code'					: zip_code,
															'country'						: country,
															'campaign_id'				: campaign_id,
															'fundraiser_id'			: fundraiser_id,															
															'dump'							: dump
							 							};
			jQuery.ajax({
				'type'    : 'post',
				'url'     : donate_url,
				'data'    : data,
				'success' : function(response) { handle_response(response); },
				'error'   : function(response) { handle_response(response, true); }
			})
			return false;
		})
	}
	jQuery(document).ready(function() {
		intialize_donation_form();
	});
</script>

<div class="six columns <?php echo $layout; ?>">
	<form action="" class="donate" id="dntly_donate">
		<fieldset class="donation_buttons">
			<a href="javascript:;" class="btn donation-preset" donation-preset="10">$10</a>
			<a href="javascript:;" class="btn donation-preset" donation-preset="25">$25</a>
			<a href="javascript:;" class="btn donation-preset" donation-preset="100">$100</a>
		</fieldset>
		<fieldset class="donation_amount">
			<input type="text" style="width:130px;" name="amount" class="input-small left" placeholder="$">
		
			<div class="left">
				<label class="radio">
					<input type="radio" name="recurring" id="optionsRadios1" class="required" checked="checked" value="0" />
					One Time
				</label>
				<label class="radio">
					<input type="radio" name="recurring" id="optionsRadios2" class="required" value="1" />
					Recurring Monthly
				</label>
			</div>
		</fieldset>
		
		<fieldset class="donor_info left">
			<div class="left" style="clear: both;margin-right: 5px;">
				<label for="">First Name *</label>
				<input type="text" name="first_name" class="input-small required" placeholder="">
			</div>
			<div class="left">
				<label for="">Last Name *</label>
				<input type="text" name="last_name" class="input-medium required" placeholder="">
			</div>

			<div class="left"style="clear: both;margin-right: 5px;">
				<label for="">Phone *</label>
				<input type="text" name="phone" class="input-small required" placeholder="" maxlength="20">
			</div>
			<div class="left" >
				<label for="">Email Address *</label>
				<input type="text" name="email" class="input-medium required email" placeholder="">
			</div>

			<div class="left">
				<label for="" style="clear: both;">Address *</label>
				<input type="text" name="address" class="input-xlarge required" placeholder="">
			</div>
			<div class="left">
				<label for="" style="clear: both;">Address 2</label>
				<input type="text" name="address2" class="input-xlarge" placeholder="">
			</div>

			<div class="left" name="noname" style="margin-right: 5px;">
				<label for="">City *</label>
				<input type="text" name="city" class="input-medium required" placeholder="">
			</div>
			<div class="left">
				<label for="">State *</label>
				<?php if( defined('FF_SELECTS_VERSION') ): ?>
					<?php ff_print_state_select(null, array('class' => 'input-small required', 'placeholder' => 'Select', 'option_display' => 'mix')); ?>
				<?php else: ?>
					<input type="text" name="state" id="state" class="input-medium required" placeholder="">
				<?php endif; ?>
			</div>
			<div class="left" style="margin-right: 5px;">
				<label for="">Zip Code *</label>
				<input type="text" name="zip_code" class="input-small required" placeholder=""  maxlength="10">
			</div>
			<div class="left">
				<label for="">Country *</label>
				<?php if( defined('FF_SELECTS_VERSION') ) : ?>
					<?php ff_print_country_select(null, array('class' => 'input-medium required', 'placeholder' => 'Select', 'option_display' => 'long')) ?>
				<?php else: ?>
				<select name='country' class="input-medium required">
					<option name='US'>United States</option>
				</select>
				<?php endif; ?>
			</div>
		</fieldset>

		<div class="donately-secure-fields left">
			<div class="donately-secure-header">
				<span>Secure Information</span>
			</div>
			<fieldset class="donor_cc">
				<label for="" style="float: left;clear: both;">Card Number *</label>
				<input type="text" name="card_number" class="input-xlarge required creditcard" placeholder="" maxlength="19">
				<div class="left" style="margin-right: 20px;">
					<label for="">CVC *</label>
					<input type="text" name="card_code" class="input-mini required" placeholder="" maxlength="4">
				</div>
				<div class="left">
					<label for="">Expiration Date *</label>
					<input type="text" name="expiration_month" class="input-mini required" placeholder="" maxlength="2">
					<input type="text" name="expiration_year" class="input-mini required" placeholder=""  maxlength="4">
				</div>
			</fieldset>
		</div>

		<fieldset class="donation_submit left">
			<a class="btn donate" class="submit" id="submit_btn"><span>Submit Donation</span></a>
		</fieldset>
		<span class="secure_message left">
			<!-- Begin DigiCert/ClickID site seal HTML and JavaScript -->
			<div id="DigiCertClickID_ZSlhN3Sg" data-language="en_US">
				<a href="http://www.digicert.com/" target="_blank">Sent using a secure connection</a>
			</div>
			<script type="text/javascript">
			var __dcid = __dcid || [];__dcid.push(["DigiCertClickID_ZSlhN3Sg", "5", "s", "black", "ZSlhN3Sg"]);(function(){var cid=document.createElement("script");cid.async=true;cid.src="//seal.digicert.com/seals/cascade/seal.min.js";var s = document.getElementsByTagName("script");var ls = s[(s.length - 1)];ls.parentNode.insertBefore(cid, ls.nextSibling);}());
			</script>
			<!-- End DigiCert/ClickID site seal HTML and JavaScript -->
		</span>
	</form>
</div>
<div class="clear"></div>