<?php
	$dntly_options = get_option('dntly_options');
	$account = isset($dntly_options['account']) ? $dntly_options['account'] : null;
?>
<div class="postbox " id="dntly-tips-box">
	<div title="Click to toggle" class="handlediv"><br></div>
	<h3 class="hndle"><span>Tips & Shortcodes</span></h3>
	<div class="inside">
		<p><strong>Step 1:</strong> Set your token in the <a href="options-general.php?page=dntly">Settings</a></p>
		<p><strong>Step 2:</strong> Sync your campaigns</p>
		<p><strong>Step 3:</strong> Add donation forms to pages/posts</p>
	</div>
	<?php if(!$account): ?>	
		<div class="updated" id="message"><p><strong>Alert!</strong> You must identify which Donately Account to Connect to in the Settings</p></div>
	<?php else: ?>		
	<div class="inside">
		<table>
			<tr><th>Shortcode</th><th>Donation</th></tr>
			<tr><td colspan="2"><strong>iFrame <span style="font-size:.8em">(no SSL needed)</span></strong></td></tr>
			<tr><td>[dntly_300width_form]</td><td>General</td></tr>
			<tr><td>[dntly_300width_form cid=123]</td><td>Campaign</td></tr>
			<tr><td>[dntly_300width_form cid=123 fid=123]</td><td>Fundraiser</td></tr>
		</table>
	<?php endif; ?>	
	</div>
</div>