<?php

add_shortcode( 'dntly_300width_form', 'dntly_build_formjs' );
add_shortcode( 'dntly_formjs', 'dntly_build_formjs' );
function dntly_build_formjs($atts) {
	extract(shortcode_atts(array(
		'account' 			=> null,
		'campaign' 			=> null,
		'fundraiser' 		=> null,
		'css_url' 			=> null,
		'address' 			=> false,
		'phone' 				=> false,
		'comment' 			=> false,
		'iframe_height' => null,
		'iframe_width' 	=> null,
  ), $atts));
	include( DNTLY_PLUGIN_PATH . '/lib/donately-formjs.php');
}
