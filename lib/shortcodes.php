<?php

add_shortcode( 'dntly_custom_form', 'dntly_build_custom_form' );
function dntly_build_custom_form( $atts ){
	$dntly_options = get_option('dntly_options');
	extract( shortcode_atts( array(
		'account' => null,
		'cid' => null,
		'fid' => null,
		'tracking' => null,
		'layout' => null,
		'thank_you_url' => null		
	), $atts ) );
	$account           = $account       != '' ? sanitize_text_field($account) : null;
	$campaign_id       = $cid           != '' ? sanitize_text_field($cid) : null;
	$fundraiser_id     = $fid           != '' ? sanitize_text_field($fid) : null;
	$tracking          = $tracking      != '' ? sanitize_text_field($tracking) : null;
	$layout            = $layout	    != '' ? sanitize_text_field($layout) : null;
	$thank_you_url     = $thank_you_url != '' ? sanitize_text_field($thank_you_url) : null;
	$account_subdomain = isset($account) ? $account : $dntly_options['account'];
	include_once( DNTLY_PLUGIN_PATH . '/lib/donation_form.php');
}


add_shortcode( 'dntly_300width_form', 'dntly_build_sidebar_form' );
function dntly_build_sidebar_form($atts) {
	extract(shortcode_atts(array(
		'account' => null,
		'campaign' => null,
		'fundraiser' => null,
		'css_url' => null, //DNTLY_PLUGIN_URL . 'lib/formjs-wide.css',
		'address' => false,
		'phone' => false,
  ), $atts));
	include( DNTLY_PLUGIN_PATH . '/lib/donately-formjs.php');
}