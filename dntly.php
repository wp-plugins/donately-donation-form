<?php

/*
Plugin Name:  Donately Integration
Plugin URI:   http://www.donately.com
Description:  API Integration with the Donately donation platform
Version:      4.0.0
Author:       5ifty&5ifty
Author URI:   https://www.fiftyandfifty.org/
Contributors: shanaver, bryanmonzon, elzizzo

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Alex Moss or pleer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

define('DNTLY_VERSION', '4.0.0');

/* set to true for testing/debugging in development & staging environments */
//define('DNTLY_DEBUG', true);
if(!defined('DNTLY_DEBUG')){define('DNTLY_DEBUG', false);}

define('DNTLY_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('DNTLY_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define('DNTLY_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once( DNTLY_PLUGIN_PATH . '/lib/posttypes.php');
require_once( DNTLY_PLUGIN_PATH . '/lib/taxonomies.php');
require_once( DNTLY_PLUGIN_PATH . '/lib/shortcodes.php');
require_once( DNTLY_PLUGIN_PATH . '/lib/meta.php');
require_once( DNTLY_PLUGIN_PATH . '/lib/dntly.class.php');
require_once( DNTLY_PLUGIN_PATH . '/lib/widgets.php');
require_once( DNTLY_PLUGIN_PATH . '/lib/donately-helpers.php');

// admin styles & scripts
function dntly_admin_scripts_styles(){
	wp_register_script( 'dntly-scripts', DNTLY_PLUGIN_URL . 'lib/dntly-back.js', array('jquery') );
	wp_enqueue_script( 'dntly-scripts' );
	wp_register_style( 'dntly-style', DNTLY_PLUGIN_URL . 'lib/dntly.css' );
	wp_enqueue_style( 'dntly-style' );
}
add_action('admin_init', 'dntly_admin_scripts_styles');

// front end styles & scripts
function dntly_front_scripts_styles(){
	wp_register_script( 'dntly-scripts', DNTLY_PLUGIN_URL . 'lib/dntly-front.js', array('jquery') );
	wp_localize_script( 'dntly-scripts', 'dntly_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'dntly-scripts' );
}
add_action('wp_enqueue_scripts', 'dntly_front_scripts_styles');

// menu page
function dntly_add_menu_page(){
	function dntly_menu_page(){
		$options_page_url = dirname(__FILE__) . '/dntly-options.php';
		if(file_exists($options_page_url)){
			include_once($options_page_url);
		}
	};
	add_submenu_page( 'options-general.php', 'Donately', 'Donately', 'switch_themes', 'dntly', 'dntly_menu_page' );
};
add_action( 'admin_menu', 'dntly_add_menu_page' );


// Add settings link on plugin page
function dntly_plugin_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=dntly">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
add_filter("plugin_action_links_" . DNTLY_PLUGIN_BASENAME, 'dntly_plugin_settings_link' );


//increase timeout filter
function dntly_timeout_request_time($time){
	$time = 120;
	return $time;
};
add_filter( 'http_request_timeout', 'dntly_timeout_request_time' );


/*  Cron Functions  */

add_filter( 'cron_schedules', 'dntly_cron_schedules');
function dntly_cron_schedules(){
	return array(
		'every_fifteen_minutes' => array(
			'interval' => 60 * 15,
			'display' => 'Four Times Hourly'
		),
		'every_thirty_minutes' => array(
			'interval' => 60 * 30,
			'display' => 'Twice Hourly'
		),
	);
}

// function for syncing everything
function dntly_sync_everything() {
	dntly_get_account_stats();
	dntly_get_campaigns();
	dntly_get_fundraisers();
}
add_action('dntly_syncing_cron', 'dntly_sync_everything');

// function for adding the syncing everything cron
function dntly_activate_cron_syncing($cron) {
	if( !wp_get_schedule('dntly_syncing_cron') ){
		if($cron == 'cron30'){
			wp_schedule_event(time(), 'every_thirty_minutes', 'dntly_syncing_cron');
			dntly_transaction_logging('Donately Plugin - start hourly scheduler - 30 minutes');
		}
		else{
			wp_schedule_event(time(), 'hourly', 'dntly_syncing_cron');
			dntly_transaction_logging('Donately Plugin - start hourly scheduler - 60 minutes');
		}
	}
}

// function for removing the syncing everything cron
function dntly_deactivate_cron_syncing() {
	if( wp_get_schedule('dntly_syncing_cron') ){
		dntly_transaction_logging('Donately Plugin - stop hourly scheduler');
		wp_clear_scheduled_hook('dntly_syncing_cron');
	}
}

/*
	Transaction Logging
*/

function dntly_transaction_logging($message='empty', $status='success') {
	global $wpdb;
	if( isset($_REQUEST['message']) ){ $message = $_REQUEST['message']; }
	if( isset($_REQUEST['status']) ){ $status = $_REQUEST['status']; }
	if($status == 'print_debug'){
		print $message;
	}
	else{
		$total = wp_count_posts( 'dntly_log_entries' );
		$log_entry = array(
		  'post_title' => (strtoupper($status) . ' : ' . strtolower(substr($message, 0, 150))),
		  'post_content' => $message,
		  'post_status' => 'publish',
		  'post_name' => ('dntly-log-' . ($total->publish + 1)),
		  'post_type' => 'dntly_log_entries',
		);
		wp_insert_post( $log_entry );
	}
}
add_action( 'wp_ajax_dntly_transaction_logging', 'dntly_transaction_logging' );

function dntly_get_table_logging($verify=false){
	$limit = "30";
	$d_page = isset($_REQUEST['d_page']) ? $_REQUEST['d_page'] : 0;

	$args = array( 'post_type' => 'dntly_log_entries', 'orderby' => 'ID', 'order' => 'DESC', 'paged' => $d_page, 'posts_per_page' => $limit );
	$log_entries = new WP_Query( $args );

	if( $verify && !$log_entries->have_posts() ){
		return false;
	}

	print "<h3>Donately integration Logging</h3>";
	print "<table>";
	print "<tr><th>ID</th><th>Time</th><th>Message</th></tr>";
	while ( $log_entries->have_posts() ) {
		global $post;
		$log_entries->the_post();
		print "<tr class='{$post->post_title}'><td>" . str_replace ( 'dntly-log-' , '' , $post->post_name) . "</td><td>" . $post->post_date . "</td><td>" . $post->post_content . "</td></tr>";
	}
	print "</table>";
	print "<input id='dntly-prev' onclick='dntly.refreshLog(".($d_page - 1).");' type=button value='Previous {$limit}'/>";
	print "<input id='dntly-next' onclick='dntly.refreshLog(".($d_page + 1).");' type=button value='Next {$limit}'/>";
	die();
}
add_action( 'wp_ajax_dntly_get_table_logging', 'dntly_get_table_logging' );

function dntly_activate() {
	dntly_transaction_logging("Donately Plugin - Activated");
}
register_activation_hook(__FILE__,'dntly_activate');

function dntly_deactivate(){
	dntly_transaction_logging('Donately Plugin - *Deactivated*');
}
register_deactivation_hook(__FILE__,'dntly_deactivate');


/*

Donately Ajax Methods

*/

function dntly_get_api_token(){
	$dntly = new DNTLY_API;
	$email = (isset($_POST['email']) ? $_POST['email'] : '');
	$password = (isset($_POST['password']) ? $_POST['password'] : '');
	$environment = (isset($_POST['environment']) ? $_POST['environment'] : '');
	$dntly->get_api_token($email, $password, $environment);
}
add_action( 'wp_ajax_dntly_get_api_token', 'dntly_get_api_token' );

function dntly_get_accounts(){
	$dntly = new DNTLY_API;
	$dntly_data = $dntly->get_accounts();
	return $dntly_data->accounts;
}
add_action( 'wp_ajax_dntly_get_accounts', 'dntly_get_accounts' );

function dntly_get_campaigns(){
	$dntly = new DNTLY_API;
	$dntly->get_campaigns();
}
add_action( 'wp_ajax_dntly_get_campaigns', 'dntly_get_campaigns' );

function dntly_get_fundraisers(){
	$dntly = new DNTLY_API;
	$dntly->get_fundraisers();
}
add_action( 'wp_ajax_dntly_get_fundraisers', 'dntly_get_fundraisers' );

function dntly_get_account_stats(){
	$dntly = new DNTLY_API;
	$dntly->get_account_stats();
}
add_action( 'wp_ajax_dntly_get_account_stats', 'dntly_get_account_stats' );

function dntly_create_fundraiser(){
	$dntly = new DNTLY_API;
	$dntly->create_fundraiser();
}
add_action('wp_ajax_dntly_create_fundraiser','dntly_create_fundraiser');
add_action('wp_ajax_nopriv_dntly_create_fundraiser','dntly_create_fundraiser');

function dntly_lookup_person(){
	$dntly = new DNTLY_API;
	$dntly->lookup_person();
}
add_action('wp_ajax_dntly_lookup_person','dntly_lookup_person');
add_action('wp_ajax_nopriv_dntly_lookup_person','dntly_lookup_person');

