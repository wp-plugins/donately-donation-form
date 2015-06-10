<?php

include_once('dntly.php');

function donately_sanitize_admin_inputs($dntly_options, $dntly_options_post){
	$dntly_defaults = array(
		'user' 						=> null,
		'token' 					=> null,
		'account' 					=> null,
		'environment' 				=> 'production',
		'syncing' 				    => 'manual',
		'console_details' 			=> "0",
		'console_debugger' 			=> "0",
		'console_calls' 			=> "0",
		'thank_you_page' 			=> null,
		'sync_to_private' 			=> "0",
		'dntly_campaign_posttype' 	=> "dntly_campaigns",
		'dntly_get_fundraisers' 	=> "0",
		'fundraiser_sync_to_private'=> "0",
		'dntly_fundraiser_posttype' => "dntly_fundraisers",
		'show_debugging' 			=> "0",
	);
	foreach( $dntly_defaults as $field => $default){
		if( isset($dntly_options_post[$field]) ){
			$dntly_options[$field] = sanitize_text_field( $dntly_options_post[$field] );
		}
		elseif($dntly_options_post) {
			$dntly_options[$field] = $default;
		}

	}
	return $dntly_options;
}

// get the options (if any) stored in the DB
$dntly_options = get_option('dntly_options');

// get any updates to those options sent from the form
$dntly_options_post = isset($_POST['dntly_options']) ? $_POST['dntly_options'] : false;

// overwrite options if needed - also set defaults for options that are not set
$dntly_options = donately_sanitize_admin_inputs($dntly_options, $dntly_options_post);

// if the admin made changes, update the DB
if($dntly_options_post){

	update_option('dntly_options', $dntly_options);

	dntly_transaction_logging('Updated Donately API Settings');
	if(isset($dntly_options_post['token'])){
		dntly_transaction_logging('user=' . $dntly_options_post['user'] . ' & token=' . substr($dntly_options_post['token'], 0, 10) . '[...]' . ' & account=' . ( isset($dntly_options_post['account']) ? $dntly_options_post['account'] : '') . ' & environment=' . ( isset($dntly_options_post['environment']) ? $dntly_options_post['environment'] : '') );
	}
	else{
		dntly_transaction_logging('Reset Donately API Settings');
	}

}

// if we have a Donately token, get the account list
if($dntly_options['token']){
	$dntly_accounts = dntly_get_accounts();
	if( count($dntly_accounts) > 1 ){
		$dntly_account_options = '';
		foreach( $dntly_accounts as $a ){
			if( $a->subdomain == $dntly_options['account'] && isset($dntly_options['account']) && $dntly_options_post ){

				// update the options with the account info if the admin changed it
				$dntly_options['account_title'] = $a->title;
				$dntly_options['account_id']    = $a->id;
				update_option('dntly_options', $dntly_options);

			}
			$dntly_account_options .=	"<option value='{$a->subdomain}' ".selected( $dntly_options['account'], $a->subdomain, false ).">{$a->title}</option>";
		}
	}
	else {
		$dntly_options['account'] = $dntly_accounts[0]->subdomain;
		if( $dntly_options_post ){

			// update the options with the account info if the admin changed it
			$dntly_options['account_title'] = $dntly_accounts[0]->title;
			$dntly_options['account_id']    = $dntly_accounts[0]->id;
			update_option('dntly_options', $dntly_options);

		}
	}
	$excluded_posttypes = array('dntly_log_entries', 'dntly_fundraisers', 'ccpurge_log_entries');
	$post_types=get_post_types(array('public'=>true,'_builtin'=>false,'show_ui'=>true), 'objects');
	$campaign_posttype_options = '';
	foreach( $post_types as $p ){
		if( in_array($p->name, $excluded_posttypes))
			continue;
		$campaign_posttype_options .=	"<option value='{$p->name}' ".selected($dntly_options['dntly_campaign_posttype'], $p->name, false).">Use {$p->labels->name}</option>";
	}
	$excluded_posttypes = array('dntly_log_entries', 'dntly_campaigns', 'ccpurge_log_entries');
	$fundraiser_posttype_options = '';
	foreach( $post_types as $p ){
		if( in_array($p->name, $excluded_posttypes))
			continue;
		$fundraiser_posttype_options .=	"<option value='{$p->name}' ".selected($dntly_options['dntly_fundraiser_posttype'], $p->name, false).">Use {$p->labels->name}</option>";
	}
}
else{
	$dntly_object = new DNTLY_API;
	if( count($dntly_object->api_domain) > 1 ){
		$environment_options = '';
		foreach( $dntly_object->api_domain as $env => $dom ){
			$environment_options .= "<option value='".strtolower($env)."' ".selected( $dntly_options['environment'], strtolower($env), false ).">Donately ".ucwords($env)."</option>";
		}
	}
	else{
		$dntly_options['environment'] = 'production';
	}

}

if( !empty($dntly_options['token']) && stristr($dntly_options['syncing'], 'cron') ){
	dntly_activate_cron_syncing($dntly_options['syncing']);
}
else{
	dntly_deactivate_cron_syncing();
}

?>

<script>
	function show_hide_debug(){
		var show;
		if( jQuery('input[name="dntly_options[show_debugging]"]:checked').val() == '1' ){ show = true; }
		else{ show = false; }
		if( show ){ jQuery('.debugging-block').show(); }
		else{ jQuery('.debugging-block').hide(); }
	}
	function show_hide_fundraisers(){
		var show;
		if( jQuery('input[name="dntly_options[dntly_get_fundraisers]"]:checked').val() == '1' ){ show = true; }
		else{ show = false; }
		if( show ){ jQuery('.fundraiser-block').show(); }
		else{ jQuery('.fundraiser-block').hide(); }
	}
	jQuery(document).ready(function($){
		show_hide_debug();
		show_hide_fundraisers();
		jQuery('input[name="dntly_options[show_debugging]"]').change(function() {
			show_hide_debug();
		});
		jQuery('input[name="dntly_options[dntly_get_fundraisers]"]').change(function() {
			show_hide_fundraisers();
		});
	});
</script>

<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div><h2>Donately API Integration</h2>

	<p style="text-align: left;">
		Donately makes it easy to accept donations on your site.  From there we have powerful tools for maximizing donations & tracking performance.<br />
	</p>

	<div id="dntly-options-form">

	<?php if( empty( $dntly_options['token'] ) ): ?>
		<div class="updated" id="message"><p><strong>Alert!</strong> You must get an Authentication Token from Donately to start<br />If you don't already have a Donately account, you can <a target="_blank" href="https://www.dntly.com/a#/npo/signup">sign up for one here</a></p></div>
	<?php elseif(!$dntly_options['account']): ?>
		<div class="updated" id="message"><p><strong>Alert!</strong> You must identify which Donately Account to Connect to</p></div>
	<?php elseif($dntly_options['environment'] != 'production'): ?>
		<div class="updated" id="message"><p><strong>Note:</strong> Donately is not in Production mode, it's in <?php print ucwords($dntly_options['environment']); ?></p></div>
	<?php endif; ?>

	<form action="" id="dntly-form" method="post">
		<table class="dntly-table">
			<tbody>
			<?php if( isset($environment_options) ): ?>
			<tr>
				<th><label for="category_base">Donately Environment</label></th>
				<td class="col1"></td>
				<td class="col2">
					<?php if( $dntly_options['token'] ): ?>
						<input type="button" value="<?php echo ucwords($dntly_options['environment']); ?>" class="button-secondary"  id="dntly-environment" disabled="disabled" />
						<input type="hidden" value="<?php echo $dntly_options['environment']; ?>" name="dntly_options[environment]">
					<?php else: ?>
						<select name="dntly_options[environment]" id="dntly-environment">
							<?php print $environment_options ?>
						</select>
					<?php endif; ?>
				</td>
			</tr>
			<?php else: ?>
				<input type="hidden" value="<?php echo $dntly_options['environment']; ?>" name="dntly_options[environment]">
			<?php endif; ?>
			<tr>
				<th><label for="category_base">Donately Admin Email Address</label></th>
				<td class="col1"></td>
				<td class="col2">
					<?php if( !empty( $dntly_options['token'] ) ) : ?>
						<input type="text" class="regular-text code disabled" value="<?php echo $dntly_options['user']; ?>" id="dntly-user-name" name="" disabled="disabled">
						<input type="hidden" value="<?php echo $dntly_options['user']; ?>" name="dntly_options[user]">
					<?php else: ?>
						<input type="text" class="regular-text code" value="<?php echo isset( $dntly_options['user'] ) ? $dntly_options['user'] : ''; ?>" id="dntly-user-name" name="dntly_options[user]">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><label for="tag_base">Donately Admin Password</label></th>
				<td class="col1"></td>
				<td class="col2">
					<?php if( !empty( $dntly_options['token'] ) ): ?>
						<input type="text" class="regular-text code disabled" value="***" id="dntly-user-password" name="" disabled="disabled">
					<?php else: ?>
						<input type="password" class="regular-text code" id="dntly-user-password" name="dntly_options[password]">
					<?php endif; ?>
				<td>
			</tr>
			<?php if( !empty( $dntly_options['token'] ) ): ?>
			<tr>
				<th><label for="tag_base">Donately Authentication Token</label></th>
				<td class="col1"></td>
				<td class="col2">
					<input type="text" class="regular-text code disabled" value="<?php echo $dntly_options['token']; ?>" class="regular-text code disabled" disabled="disabled">
					<input type="hidden" value="<?php echo $dntly_options['token']; ?>" name="dntly_options[token]" id="dntly-user-token" />
				</td>
			</tr>

			<tr>
				<th>&nbsp;</th>
				<td class="col1"></td>
				<td class="col2">
					<input type="button" value="Reset Auth Token" id="dntly-reset-token" class="button-secondary" />
				</td>
			</tr>

			<tr>
				<th><label for="tag_base">Donately Account</label></th>
				<td class="col1"></td>
				<td class="col2">
					<?php if( isset($dntly_account_options) ): ?>
					<select name="dntly_options[account]" id="dntly-account">
						<option value="">-- choose account --</option>
						<?php print $dntly_account_options ?>
					</select>
					<?php elseif($dntly_options['account']): ?>
						<h4><?php print $dntly_options['account'] . '.dntly.com' ?></h4>
						<input type="hidden" value="<?php echo $dntly_options['account']; ?>" name="dntly_options[account]">
					<?php else: ?>
						Error retrieving accounts, try refreshing the page.
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><label for="category_base">Donately Syncing</label></th>
				<td class="col1"></td>
				<td class="col2">
					<select name="dntly_options[syncing]" id="dntly-account">
						<option value="manual" <?php selected( $dntly_options['syncing'], 'manual' ); ?>>Manual Syncing</option>
						<option value="cron60" <?php selected( $dntly_options['syncing'], 'cron60' ); ?>>Automated Syncing (every 60 mins)</option>
						<option value="cron30" <?php selected( $dntly_options['syncing'], 'cron30' ); ?>>Automated Syncing (every 30 mins)</option>
					</select> <br />
				</td>
			</tr>
			<tr>
				<th><hr /></th>
				<td colspan="2"><hr /></td>
			</tr>
			<tr>
				<th><label for="category_base">Donately Campaigns</label></th>
				<td class="col1"><a href="#" class="tooltip"><span>Use the default 'Dntly Campaigns' posttype - or your own</span></a></td>
				<td class="col2">
					<select name="dntly_options[dntly_campaign_posttype]" id="dntly-posttype">
						<?php print $campaign_posttype_options ?>
					</select>
					<div class="create_as">
						<input type=radio name="dntly_options[sync_to_private]"  value="1" <?php checked( "1", $dntly_options['sync_to_private']); ?>> Create as 'private'<br />
						<input type=radio name="dntly_options[sync_to_private]"  value="0" <?php checked( "0", $dntly_options['sync_to_private']); ?>> Create as 'public'<br />
					</div>
				</td>
			</tr>
			<tr>
				<th><hr /></th>
				<td colspan="2"><hr /></td>
			</tr>
			<tr>
				<th><label for="category_base">Donately Fundraisers</label></th>
				<td class="col1"><a href="#" class="tooltip"><span>Do you want to copy your fundraisers from Donately?</span></a></td>
				<td class="col2">
					<input type="radio" name="dntly_options[dntly_get_fundraisers]" value="1" <?php checked( $dntly_options['dntly_get_fundraisers'], '1' ); ?>/> Sync Donately Fundraisers
					<div class="create_as">
						<input type="radio" name="dntly_options[dntly_get_fundraisers]" value="0" <?php checked( $dntly_options['dntly_get_fundraisers'], '0' ); ?>/> Ignore Donately Fundraisers
					</div>
				</td>
			</tr>
			<tr class="fundraiser-block">
				<th><label for="category_base">Donately Fundraisers</label></th>
				<td class="col1"><a href="#" class="tooltip"><span>Use the default 'Dntly Fundraisers' posttype - or your own</span></a></td>
				<td class="col2">
					<select name="dntly_options[dntly_fundraiser_posttype]" id="dntly-posttype">
						<?php print $fundraiser_posttype_options ?>
					</select>
					<div class="create_as">
						<input type=radio name="dntly_options[fundraiser_sync_to_private]"  value="1" <?php checked( "1", $dntly_options['fundraiser_sync_to_private']); ?>> Create as 'private'<br />
						<input type=radio name="dntly_options[fundraiser_sync_to_private]"  value="0" <?php checked( "0", $dntly_options['fundraiser_sync_to_private']); ?>> Create as 'public'<br />
					</div>
				</td>
			</tr>
			<?php if(DNTLY_DEBUG): ?>
			<tr>
				<th><hr /></th>
				<td colspan="2"><hr /></td>
			</tr>
			<tr>
				<th><label for="category_base">Debugging</label></th>
				<td class="col1"></td>
				<td class="col2">
					<input type="radio" name="dntly_options[show_debugging]" value="1" <?php checked( $dntly_options['show_debugging'], '1' ); ?>/> Show Debugging Sections
					<span style="width:40px;height:10px;display:inline-block"></span>
					<input type="radio" name="dntly_options[show_debugging]" value="0" <?php checked( $dntly_options['show_debugging'], '0' ); ?>/> Hide Debugging Sections
				</td>
			</tr>
			<tr class="debugging-block">
				<th><label for="category_base">Options</label></th>
				<td class="col1"></td>
				<td class="col2">
					<input type=checkbox name="dntly_options[console_details]"  value="1" <?php checked( $dntly_options['console_details'], '1'); ?>> details to console (debug)<br />
					<input type=checkbox name="dntly_options[console_debugger]"  value="1" <?php checked( $dntly_options['console_debugger'], '1'); ?>> errors to console (debug)<br />
					<input type=checkbox name="dntly_options[console_calls]"  value="1" <?php checked( $dntly_options['console_calls'], '1'); ?>> API calls to console (debug)<br />
				</td>
			</tr>
		<?php endif; ?>
			<tr>
				<th>&nbsp;</th>
				<td class="col1"></td>
				<td class="col2">
					<input type="submit" value="Update / Save" class="button-secondary"/>
				</td>
			</tr>
			<?php else: ?>
				<tr>
					<th>&nbsp;</th>
					<td class="col1"></td>
					<td class="col2">
						<input type="hidden" id="dntly-user-token" name="dntly_options[token]" />
						<input type="button" value="Get Auth Token" id="dntly-get-token" class="button-secondary" />
					</td>
				</tr>
			<?php endif; ?>
				<tr class="debugging-block">
					<th><hr /></th>
					<td colspan="2"><hr /></td>
				</tr>
			</tbody>
		</table>
	</form>

	<?php if( !empty( $dntly_options['token'] ) ) : ?>
	<div style="margin:50px 0">
		<form action="" method="post">
			<table class="dntly-table">
				<tr>
					<th><label for="category_base">Manual Syncing</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type="button" value="Sync Account Stats" id="dntly-sync-account-stats" class="button-primary"/>
						<input type="button" value="Sync Campaigns" id="dntly-sync-campaigns" class="button-primary"/>
						<input type="button" value="Sync Fundraisers" id="dntly-sync-fundraisers" class="button-primary fundraiser-block" />
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?php endif; ?>

	<div id="spinner"></div>

	<div id="dntly_table_logging_container" class="debugging-block">
		<div id="dntly_table_logging"></div>
	</div>

	</div><!-- dntly-form-wrapper -->

	<div style="clear:both;display:block;padding:40px 20px 0px;width:200px"><a href="/wp-admin/edit.php?post_type=dntly_log_entries">Manage Donately Log Entries</a></div>

</div>