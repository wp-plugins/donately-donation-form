<?php

global $post;

$dntly_data = get_post_meta($post->ID, '_dntly_data', true);
$dtnly_id = get_post_meta($post->ID, '_dntly_id', true);
$dntly_account_title = (isset($dntly_data['account_title']) ? $dntly_data['account_title'] : 0 );
$dntly_account_id = get_post_meta($post->ID, '_dntly_account_id', true);
$dntly_environment = get_post_meta($post->ID, '_dntly_environment', true);

$campaign_goal = (isset($dntly_data['campaign_goal']) ? intval($dntly_data['campaign_goal']) : 0 );
$donations_count = (isset($dntly_data['donations_count']) ? intval($dntly_data['donations_count']) : 0 );
$donors_count = (isset($dntly_data['donors_count']) ? intval($dntly_data['donors_count']) : 0 );
$amount_raised = (isset($dntly_data['amount_raised']) ? intval($dntly_data['amount_raised']) : 0 );
?>
<div id="dntly-info">
<table>
	<tr>
		<td>Account:</td>
		<td>
				<?php echo $dntly_account_title; ?> (dId: <?php echo $dntly_account_id; ?>)
		</td>
	</tr>
	<tr>
		<td>Campaign Goal : </td>
		<td>
			$<?php echo number_format($campaign_goal, 2) ?>
		</td>
	</tr>
	<tr>
		<td>Donations Count : </td>
		<td>
			$<?php echo number_format($donations_count, 2) ?>
		</td>
	</tr>
	<tr>
		<td>Donors Count : </td>
		<td>
			<?php echo number_format($donors_count, 0) ?>
		</td>
	</tr>
	<tr>
		<td>Amount Raised : </td>
		<td>
			$<?php echo number_format($amount_raised, 2) ?>
		</td>
	</tr>		

	<tr>
		<td>Environment:</td>
		<td>
			<?php echo ucwords($dntly_environment); ?> (dId: <?php echo $dtnly_id; ?>)
		</td>
	</tr>
	
</table>
</div><!-- #dntly-info -->
