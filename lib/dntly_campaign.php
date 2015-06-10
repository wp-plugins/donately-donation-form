<?php

global $post;

$dntly_data = get_post_meta($post->ID, '_dntly_data', true);
$dntly_id = get_post_meta($post->ID, '_dntly_id', true);
$dntly_account_title = (isset($dntly_data['account_title']) ? $dntly_data['account_title'] : 0 );
$dntly_account_id = get_post_meta($post->ID, '_dntly_account_id', true);
$dntly_environment = get_post_meta($post->ID, '_dntly_environment', true);

$campaign_goal   = (isset($dntly_data['campaign_goal']) ? intval($dntly_data['campaign_goal']) : 0 );
$donations_count = (isset($dntly_data['donations_count']) ? intval($dntly_data['donations_count']) : 0 );
$donors_count    = (isset($dntly_data['donors_count']) ? intval($dntly_data['donors_count']) : 0 );
$amount_raised   = (isset($dntly_data['amount_raised']) ? intval($dntly_data['amount_raised']) : 0 );
$percent_funded  = (isset($dntly_data['percent_funded']) ? $dntly_data['percent_funded'] : 0 );

?>
<div id="dntly-info">
<table>
	<tr>
		<td style="width:40%">Dntly ID :</td>
		<td style="width:60%">
			<?php echo $dntly_id; ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Account :</td>
		<td style="width:60%">
			<?php echo $dntly_account_title; ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Goal : </td>
		<td style="width:60%">
			$<?php echo number_format($campaign_goal, 2) ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Raised : </td>
		<td style="width:60%">
			$<?php echo number_format($amount_raised, 2) ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Donations : </td>
		<td style="width:60%">
			<?php echo $donations_count; ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Donors : </td>
		<td style="width:60%">
			<?php echo $donors_count; ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Funded : </td>
		<td style="width:60%">
			<?php echo ($percent_funded*100) ?>%
		</td>
	</tr>
	<?php if( $dntly_environment != 'production' ): ?>
	<tr>
		<td style="width:40%">Environment:</td>
		<td style="width:60%">
			<?php echo ucwords($dntly_environment); ?> (ID: <?php echo $dntly_id; ?>)
		</td>
	</tr>
	<?php endif; ?>
</table>
</div><!-- #dntly-info -->
