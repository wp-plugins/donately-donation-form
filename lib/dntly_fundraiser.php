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

$donor_count = (isset($dntly_data['donor_count']) ? $dntly_data['donor_count'] : 0 );
$public_url = (isset($dntly_data['public_url']) ? $dntly_data['public_url'] : '' );

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
	'post_type' => $dntly->dntly_options['dntly_campaign_posttype'],
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
		<td style="width:40%">Campaign :</td>
		<td style="width:60%">
			<?php echo $dntly_campaign->post_title; ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Goal :</td>
		<td style="width:60%">
			$<?php echo number_format($goal, 2); ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Donations :</td>
		<td style="width:60%">
			$<?php echo number_format($amount_raised, 2) ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">Donors :</td>
		<td style="width:60%">
			<?php echo $donor_count; ?>
		</td>
	</tr>
	<tr>
		<td style="width:40%">URL :</td>
		<td style="width:60%; word-wrap: break-word;">
			<?php echo $public_url; ?>
		</td>
	</tr>

</table>
</div><!-- #dntly-info -->