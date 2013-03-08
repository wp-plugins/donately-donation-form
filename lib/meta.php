<?php

function dntly_add_custom_box() {
    add_meta_box( 
      'dntly_campaign',
      'Donately Data',
      'dntly_campaign_extra_meta_fields',
      'dntly_campaigns',
      'side'
    );
    add_meta_box(
      'dntly_fundraiser',
      'Donately Data',
      'dntly_fundraiser_extra_meta_fields',
      'dntly_fundraisers',
      'side'
    );
}
add_action('add_meta_boxes', 'dntly_add_custom_box');

function dntly_campaign_extra_meta_fields(){
	require('dntly_campaign.php');
}

function dntly_fundraiser_extra_meta_fields(){
	require('dntly_fundraiser.php');
}

function dntly_save_postdata( $post_id ) {
    //$data = ( isset($_POST['dntly_data']) ? $_POST['dntly_data'] : null);
    //update_post_meta( $post_id, '_dntly_data', $data );
}
//add_action( 'save_post', 'dntly_save_postdata' );

function dntly_tips_box(){
  include_once('dntly_tips_box.php');
}
function dntly_add_dntly_tips_box(){
  add_meta_box( 
       'dntly-tips-box',
       'Donately Tips',
       'dntly_tips_box',
       'post',
       'side',
       'high'
    );
}
add_action( 'add_meta_boxes', 'dntly_add_dntly_tips_box' );