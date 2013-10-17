<?php

/**
 * used inside the loop
 */

function dntly_total_raised(){
  global $post;
  if( !isset($post->ID) ){ return null; }
  $total_raised = dntly_get_total_raised( $post->post_type, $post->ID );
  return $total_raised;
}

function dntly_goal(){
  global $post;
  if( !isset($post->ID) ){ return null; }
  $total_raised = dntly_get_goal( $post->ID );
  return $total_raised;
}

function dntly_donations_count(){
  global $post;
  if( !isset($post->ID) ){ return null; }
  $donations_count = dntly_get_donations_count( $post->ID );
  return $donations_count;
}

function dntly_donors_count(){
  global $post;
  if( !isset($post->ID) ){ return null; }
  $meta = get_post_meta($post->ID, '_dntly_data', true);
  $donors_count = ( isset($meta['donors_count'])?$meta['donors_count']:null );
  return $donors_count;
}

function dntly_percent_funded(){
  global $post;
  if( !isset($post->ID) ){ return null; }
  $meta = get_post_meta($post->ID, '_dntly_data', true);
  if( !empty($meta['percent_funded']) ){
    $percent_funded = round(($meta['percent_funded']*100 ), 2);
  }
  elseif( isset($meta['amount_raised']) && isset($meta['goal']) ){
    $percent_funded = round(($meta['amount_raised'] / $meta['goal'] * 100), 2);
  }
  else{
    $percent_funded = 0;
  }
  return $percent_funded;
}



/**
 * used anywhere
 */

function dntly_get_meta($ID=null){
  if( $ID ){
    $meta = get_post_meta($ID, '_dntly_data', true);
  }
  else{
    $dntly_stats = get_option('dntly_stats');
    $dntly_options = get_option('dntly_options');
    $meta = array_merge($dntly_stats, $dntly_options);
  }
  return $meta;
}


function dntly_get_total_raised($type='account', $ID=null){
  if( $type == 'account' ){
    $meta = dntly_get_meta();
    $total_raised = ( isset($meta['total_raised'])?$meta['total_raised']:0 );
  }
  else{
    $meta = dntly_get_meta($ID);
    $total_raised = ( isset($meta['amount_raised'])?$meta['amount_raised']:0 );
  }
  return $total_raised;
}

function dntly_get_goal($ID=null){
  $meta = dntly_get_meta($ID);
  $goal = ( isset($meta['goal'])?$meta['goal']:null );
  if( !is_numeric($goal) ){
    $goal = ( isset($meta['campaign_goal'])?$meta['campaign_goal']:0 );
  }
  return $goal;
}

function dntly_get_campaigns_count(){
  $meta = dntly_get_meta();
  $campaigns_count = ( isset($meta['total_campaigns_count'])?$meta['total_campaigns_count']:0 );
  return $campaigns_count;
}

function dntly_get_fundraisers_count(){
  $meta = dntly_get_meta();
  $fundraisers_count = ( isset($meta['total_fundraisers_count'])?$meta['total_fundraisers_count']:0 );
  return $fundraisers_count;
}

function dntly_get_donations_count($ID=null){
  $meta = dntly_get_meta($ID);
  $total_donations = ( isset($meta['total_donations'])?$meta['total_donations']:null );
  if( !is_numeric($total_donations) ){
    $total_donations = ( isset($meta['donations_count'])?$meta['donations_count']:0 );
  }
  return $total_donations;
}
