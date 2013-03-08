<?php
global $post;

$dntly_id            = get_post_meta($post->ID, '_dntly_id', true);
$dntly_account_id    = get_post_meta($post->ID, '_dntly_account_id', true);
$dntly_environment   = get_post_meta($post->ID, '_dntly_environment', true);
$dntly_campaign_id   = get_post_meta($post->ID, '_dntly_campaign_id', true);

$dntly_data          = get_post_meta($post->ID, '_dntly_data', true);
$goal = (isset($dntly_data['goal']) ? $dntly_data['goal'] : 0 );
$amount_raised = (isset($dntly_data['amount_raised']) ? $dntly_data['amount_raised'] : 0 );
$dntly_account_title = (isset($dntly_data['account_title']) ? $dntly_data['account_title'] : 0 );

$dntly = new DNTLY_API;
$dntly_accounts = $dntly->get_accounts();
foreach($dntly_accounts as $a){
	if( $a[0] ){
		if( $a[0]->id == $dntly_account_id ){
			$dntly_account = $a[0]->title;
		}		
	}
}
$campaign_post = new WP_Query( array(
	'post_type' => 'dntly_campaigns',
	'meta_query' => array(
		array(
			'key' => '_dntly_id',
			'value' => $dntly_campaign_id,
		)
	)
) );
$dntly_campaign      = $campaign_post->posts[0];
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
		<td>Campaign:</td>
		<td>
			<?php echo $dntly_campaign->post_title; ?> (dId: <?php echo $dntly_campaign_id; ?>)
		</td>
	</tr>
	<tr>
		<td>Goal:</td>
		<td>
			$<?php echo number_format($goal, 2); ?>
		</td>
	</tr>
	<tr>
		<td>Amount Raised:</td>
		<td>
			$<?php echo number_format($amount_raised, 2) ?>
		</td>
	</tr>	
	<tr>
		<td>Environment:</td>
		<td>
				<?php echo ucwords($dntly_environment); ?> (dId: <?php echo $dntly_id; ?>)
		</td>
	</tr>
	<tr>
		<td>Fundraiser ID:</td>
		<td>
			<?php echo $dntly_id; ?>
		</td>
	</tr>

</table>
</div><!-- #dntly-info -->