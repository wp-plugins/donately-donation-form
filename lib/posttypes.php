<?php

function dntly_custom_post_types(){
	$dntly_options = get_option('dntly_options');
	register_post_type( 'dntly_campaigns',
	    array(
	        'labels' => array(
	            'name' => __( 'Dntly Campaigns' ),
	            'singular_name' => __( 'Campaign' ),
	            'add_new' => __( 'Add New Campaign' ),
	            'add_new_item' => __( 'Add New Campaign' ),
	            'edit_item' => __( 'Edit Campaign' ),
	            'new_item' => __( 'Add New Campaign' ),
	            'view_item' => __( 'View Campaign' ),
	            'search_items' => __( 'Search Campaigns' ),
	            'not_found' => __( 'No campaigns found' ),
	            'not_found_in_trash' => __( 'No campaigns found in trash' )
	        ),
	      'public' => true,
	      'supports' => array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail', 'revisions'),
	      'capability_type' => 'post',
	      'hierarchical' => true,
	      'taxonomies' => array('category', 'post_tag', 'campaign_categories'),
	      'rewrite' => array('slug' => "campaign"),
	      'menu_position' => '25',
	      'has_archive' => true,
	      'show_in_menu' => ($dntly_options['dntly_campaign_posttype']=='dntly_campaigns'?true:false),
	    )
	);	
	register_post_type( 'dntly_fundraisers',
	    array(
	        'labels' => array(
	            'name' => __( 'Dntly Fundraisers' ),
	            'singular_name' => __( 'Fundraiser' ),
	            'add_new' => __( 'Add New Fundraiser' ),
	            'add_new_item' => __( 'Add New Fundraiser' ),
	            'edit_item' => __( 'Edit Fundraiser' ),
	            'new_item' => __( 'Add New Fundraiser' ),
	            'view_item' => __( 'View Fundraiser ' ),
	            'search_items' => __( 'Search Fundraisers' ),
	            'not_found' => __( 'No fundraisers found' ),
	            'not_found_in_trash' => __( 'No fundraisers found in trash' )
	        ),
	      'public' => true,
	      'supports' => array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail', 'revisions'),
	      'capability_type' => 'post',
	      'hierarchical' => true,
	      'taxonomies' => array('category', 'post_tag'),
	      'rewrite' => array("slug" => "fundraiser"),
	      'menu_position' => '26',
	      'show_in_menu' => false,
	    )
	);
	register_post_type( 'dntly_log_entries',
	    array(
      	'labels' => array(
          'name' => __( 'Dntly Logging' ),
          'singular_name' => __( 'Log Entry' ),
          'add_new' => __( 'Add New Log Entry' ),
          'add_new_item' => __( 'Add New Log Entry' ),
          'edit_item' => __( 'Edit Log Entry' ),
          'new_item' => __( 'Add New Log Entry' ),
          'view_item' => __( 'View Log Entry' ),
          'search_items' => __( 'Search Log Entries' ),
          'not_found' => __( 'No log entries found' ),
          'not_found_in_trash' => __( 'No log entries found in trash' )
      ),
      'public' => true,
      'supports' => array('editor'),
      'capability_type' => 'post',
      'hierarchical' => false,
      'rewrite' => array("slug" => "dntly_log_entries"),
      'show_in_menu' => false,
	    )
	);	
};
add_action( 'init', 'dntly_custom_post_types' );

add_filter('manage_dntly_campaigns_posts_columns', 'dntly_add_column_campaigns', 10, 2);
function dntly_add_column_campaigns($posts_columns, $post_type) {
	$posts_columns['environment'] = 'Environment';
	return $posts_columns;
}

add_action('manage_dntly_campaigns_posts_custom_column', 'dntly_display_column_campaigns', 10, 2);
function dntly_display_column_campaigns($column_name, $post_id) {
	if ('environment' == $column_name) {
		print ucwords(get_post_meta( $post_id, '_dntly_environment', true ));
	}
}

add_filter( 'manage_edit-dntly_campaigns_sortable_columns', 'dntly_views_column_register_sortable_campaigns' );
function dntly_views_column_register_sortable_campaigns( $columns ) {
	$columns['environment'] = 'Environment';
	return $columns;
}


add_filter('manage_dntly_fundraisers_posts_columns', 'dntly_add_column_fundraisers', 10, 2);
function dntly_add_column_fundraisers($posts_columns, $post_type) {
	$posts_columns['environment'] = 'Environment';
	$posts_columns['campaign']    = 'Campaign';
	return $posts_columns;
}

add_action('manage_dntly_fundraisers_posts_custom_column', 'dntly_display_column_fundraisers', 10, 2);
function dntly_display_column_fundraisers($column_name, $post_id) {
	if ('environment' == $column_name) {
		print ucwords(get_post_meta( $post_id, '_dntly_environment', true ));
	}
	if ('campaign' == $column_name) {
		$c = get_post_meta( $post_id, '_dntly_campaign_id', true );
		$campaign_post = new WP_Query( array(
			'post_type' => 'dntly_campaigns',
			'meta_query' => array(
				array(
					'key' => '_dntly_id',
					'value' => $c,
				)
			)
		) );
		print $campaign_post->posts[0]->post_title;
	}	
}

add_filter( 'manage_edit-dntly_fundraisers_sortable_columns', 'dntly_views_column_register_sortable_fundraiser' );
function dntly_views_column_register_sortable_fundraiser( $columns ) {
	$columns['environment'] = 'Environment';
	$columns['campaign']    = 'Campaign';
	return $columns;
}

