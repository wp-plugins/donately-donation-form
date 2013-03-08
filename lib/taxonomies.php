<?php

function dntly_custom_taxonomies(){
	$labels = array(
		'name' => _x( 'Campaign Categories', 'taxonomy general name' ),
		'singular_name' => _x( 'Campaign Category', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Campaign Categories' ),
		'all_items' => __( 'All Campaign Categories' ),
		'parent_item' => __( 'Parent Campaign Category' ),
		'parent_item_colon' => __( 'Parent Campaign Category:' ),
		'edit_item' => __( 'Edit Campaign Category' ), 
		'update_item' => __( 'Update Campaign Category' ),
		'add_new_item' => __( 'Add New Campaign Category' ),
		'new_item_name' => __( 'New Campaign Category' ),
		'menu_name' => __( 'Campaign Category' )
	); 	

	register_taxonomy(
		'campaign_categories', array('dntly_campaigns'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'campaign-category' ),
	));
}
add_action( 'init', 'dntly_custom_taxonomies' );