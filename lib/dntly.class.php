<?php
/*

Description:  Donately API integration class
Author:       5ifty&5ifty - A humanitarian focused creative agency
Author URI:   http://www.fiftyandfifty.org/
Contributors: shanaver

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Alex Moss or pleer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

class DNTLY_API {

	var $api_scheme                = array('production' => 'https');//, 'staging' => 'http');//, 'dev' => 'http');
	var $api_domain                = array('production' => 'dntly.com');//, 'staging' => 'dntly-staging.com');//, 'dev' => 'dntly.local:3000');
	var $api_subdomain             = "www";
	var $api_endpoint              = "/api/v1/";
	var $api_methods               = array();
	var $api_runtime_id            = 0;
	var $dntly_account_id          = 0;
	var $dntly_options             = array();
	var $wordpress_upload_dir      = null;
	var $suppress_logging          = false;
	var $remote_results            = null;

	function __construct() {
		if(DNTLY_DEBUG){
			$this->api_scheme['staging'] = 'https';$this->api_scheme['dev'] = 'http';$this->api_domain['staging'] = 'dntly-staging.com';$this->api_domain['dev'] = 'dntly.local:3000';
		}
		$this->dntly_options = get_option('dntly_options');
		if( isset($this->dntly_options['account']) ){
			$this->api_subdomain = ( $this->dntly_options['account'] != '' ? $this->dntly_options['account'] : 'www');
			$this->dntly_account_id = ( isset($this->dntly_options['account_id']) ? $this->dntly_options['account_id'] : 0);
		}
		$this->build_api_methods();
		$this->wordpress_upload_dir = wp_upload_dir();
	}

	function build_api_methods(){
		$this->api_methods = array(
			"root"		            			=>  array("get",  ""),
			"get_session_token"					=>  array("post", "sessions"),
			"donate_without_auth"				=>  array("post", "accounts/" . $this->dntly_account_id . "/donate_without_auth"),
			"create_fundraiser"					=>  array("post", "fundraisers"),
			"create_person"       				=>  array("post", "people"),
			"person_exists"       				=>  array("get",  "public/people/exists"),
			"get_my_accounts"					=>  array("get",  "accounts"),
            "get_account_stats"                 =>  array("get",  "admin/account/stats"),
			"get_person"          				=>  array("get",  "admin/people" . ( $this->api_runtime_id ? '/' . $this->api_runtime_id : '' )),
			"get_all_accounts"					=>  array("get",  "public/accounts"),
			"get_campaigns"       				=>  array("get",  "admin/campaigns"),
			"get_fundraisers"     				=>  array("get",  "admin/fundraisers"),
			"get_donations"       				=>  array("get",  "admin/donations"),
			"get_events"          				=>  array("get",  "admin/events"),
		);
	}

	function return_json_success($data='') {
		print json_encode( array("success" => 'true', "data" => $data) );
	}

	function return_json_error($error='') {
		print json_encode( array("success" => 'false', 'error' => array("message" => $error)) );
	}

	function get_api_token($user, $password, $environment){
		if( isset($environment) ){
			$this->dntly_options['environment'] = $environment;
		}
		unset($this->dntly_options['console_calls']);
		$post_variables = array(
			'email'		=> $user,
			'password'	=> $password
		);
		$session_token = $this->make_api_request("get_session_token", false, $post_variables);
		if($session_token->success != 'true'){
			$this->return_json_error($session_token->error->message);
			die();
		}
		$options = get_option('dntly_options');
		$options['token'] = $session_token->token;
		update_option('dntly_options', $options);
		$this->return_json_success( array('token' => $session_token->token) );
		die();
	}

	function array_to_object($array) {
    if(!is_array($array)) { return stripslashes($array); }
    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
      foreach ($array as $name=>$value) {
         $name = strtolower(trim($name));
         if (!empty($name)) {
            $object->$name = $this->array_to_object($value);
         }
      }
      return $object;
    }
    else { return FALSE; }
	}

	function build_url($api_method){
		$url  = ( !empty($this->dntly_options['environment']) ? $this->api_scheme[$this->dntly_options['environment']] : $this->api_scheme['production'] ) . '://';
		$url .= $this->api_subdomain . '.';
		$url .= ( !empty($this->dntly_options['environment']) ? $this->api_domain[$this->dntly_options['environment']] : $this->api_domain['production'] );
		if($api_method != 'root'){$url .= $this->api_endpoint . $this->api_methods[$api_method][1];}
		return $url;
	}

	function verify_host($url){
		if (!$url) return false;
		$url = array_map('trim', $url);
		$url['port'] = (!!empty($url['port'])) ? 80 : (int)$url['port'];
		if( !fsockopen($url['host'], $url['port'], $errno, $errstr, 3) ) return false;
		return true;
	}

	function do_not_log(){
		if( $this->suppress_logging ){
			return true;
		}
		if( !isset($_REQUEST['action']) ){
			return true;
		}
		if( substr($_REQUEST['action'], 0 ,5) == 'dntly' ){
			return false;
		}
	}

	function make_api_request($api_method, $auth=true, $post_variables=null){
		$url = $this->build_url($api_method);
		if( !empty($this->dntly_options['console_calls']) && !$this->do_not_log() ){
			dntly_transaction_logging("\n" . "api url: " . $url . "\n" . "api post args: " . (sizeof($post_variables) ? print_r($post_variables, true) : '') . "\n", 'print_debug');
		}
		if($auth){
			$session_token = $this->dntly_options['token'];
			$authorization = 'Basic ' . base64_encode("{$session_token}:");
			$headers = array( 'Authorization' => $authorization, 'sslverify' => false );
		}else{
			$headers = array( 'sslverify' => false );
		}
		if( $this->api_methods[$api_method][0] == "post" ){
			$this->remote_results = wp_remote_post($url, array('headers' => $headers, 'body' => $post_variables));
			//$results = wp_remote_post($url, array('headers' => $headers, 'body' => $post_variables));
		}
		else{
			if($post_variables){
				$url .= '?'; foreach($post_variables as $var => $val){ $url .= $var . '=' . $val . '&'; }
			}
			$this->remote_results = wp_remote_get($url, array('headers' => $headers));
			//$results = wp_remote_get($url, array('headers' => $headers));
		}
		if( is_object($this->remote_results) ){
			if( get_class($this->remote_results) == 'WP_Error' ){
				//$this->return_json_error('Wordpress Error - ' . json_encode($results));
				return null;
				//die();
			}
		}
		if($this->remote_results['response']['code'] != '200'){
			$this->return_json_error($this->remote_results['response']['message']);
			return null;
			//die();
		}
		return json_decode($this->remote_results['body']);
	}

	function convert_amount_in_cents_to_amount($amount_in_cents){
		$dollars = substr($amount_in_cents, 0, (strlen($amount_in_cents)-2));
		$cents = substr($amount_in_cents, -2);
		return $dollars . '.' . $cents;
	}

	function get_accounts(){
		$accounts = $this->make_api_request("get_my_accounts");
		if( !$accounts ){
			print '<div class="updated" id="message"><p><strong>Error retrieving Donately accounts!</strong> - ' . print_r($this->remote_results, true) . '</p></div>';
		}
		return $accounts;
	}

    function update_account_stats($stats){
        $existing_stats = get_option('dntly_stats');
        $dntly_stats = array(
            'total_raised'              => ( !@empty($stats['total_raised'])?$stats['total_raised']:$existing_stats['total_raised'] ),
            'total_raised_onetime'      => ( !@empty($stats['total_raised_onetime'])?$stats['total_raised_onetime']:$existing_stats['total_raised_onetime'] ),
            'total_raised_recurring'    => ( !@empty($stats['total_raised_recurring'])?$stats['total_raised_recurring']:$existing_stats['total_raised_recurring'] ),
            'total_donations'           => ( !@empty($stats['total_donations'])?$stats['total_donations']:$existing_stats['total_donations'] ),
            'total_campaigns_count'     => ( !@empty($stats['total_campaigns_count'])?$stats['total_campaigns_count']:$existing_stats['total_campaigns_count'] ),
            'total_fundraisers_count'   => ( !@empty($stats['total_fundraisers_count'])?$stats['total_fundraisers_count']:$existing_stats['total_fundraisers_count'] ),
        );
        update_option('dntly_stats', $dntly_stats);
        return $dntly_stats;
    }

    function get_account_stats(){
        $stats = $this->make_api_request("get_account_stats", true);
        $dntly_stats = array(
            'total_raised'              => $stats->amount_raised,
            'total_raised_onetime'      => $stats->one_time_amount_raised,
            'total_raised_recurring'    => $stats->recurring_amount_raised,
            'total_donations'           => $stats->donations_count
        );
        $all_stats = $this->update_account_stats($dntly_stats);
        dntly_transaction_logging("Synced Account Stats - total_raised: $" . $stats->amount_raised . ", total_donations: " . $stats->donations_count);
        return $all_stats;
    }

	function get_campaigns($referrer=null){
		$count_accounts  = 0;
		$count_campaigns = array('add' => 0, 'update' => 0, 'skip' => 0);
		if($referrer){
			$get_accounts = $this->make_api_request("get_my_accounts", true, array('referrer' => $referrer));
			foreach($get_accounts->accounts as $account){
				$count_accounts++;
				$get_campaigns = $this->make_api_request("get_campaigns", true, array('account_ids' => $account->id));
				foreach($get_campaigns->campaigns as $campaign){
					$count_campaigns = $this->add_update_campaign($campaign, $account->id, $count_campaigns);
				}
			}
		}
		else{
			$count_accounts++;
			$get_campaigns = $this->make_api_request("get_campaigns", true, array('account_ids' => $this->dntly_account_id, 'count' => 100));
			foreach($get_campaigns->campaigns as $campaign){
				$count_campaigns = $this->add_update_campaign($campaign, $this->dntly_account_id, $count_campaigns);
			}
		}

		if( $count_campaigns['add'] || $count_campaigns['update'] || $count_campaigns['skip'] ){
            $this->update_account_stats( array('total_campaigns_count' => ($count_campaigns['add'] + $count_campaigns['update'] + $count_campaigns['skip']) ) );
			dntly_transaction_logging("Synced Campaigns - ".$count_campaigns['add']." added, ".$count_campaigns['update']." updated ".$count_campaigns['skip']." skipped " . ($count_accounts>1?"from {$count_accounts} accounts":""));
		}
		else{
			dntly_transaction_logging('Synced Campaigns Error, no campaigns found', 'error');
		}
		// die();
	}

	function add_update_campaign($campaign, $account_id, $count_campaigns){

		if( $campaign->state == 'archived' ){
			return array( 'add' => $count_campaigns['add'], 'update' => $count_campaigns['update'], 'skip' => ($count_campaigns['skip']+1) );
		}

		$trans_type = null;

		$_dntly_data = array(
			'dntly_id'							=> $campaign->id,
			'account_title'						=> $this->dntly_options['account_title'],
			'account_id'						=> $account_id,
			'campaign_goal'						=> $campaign->campaign_goal,
			'donations_count'					=> $campaign->donations_count,
			'donors_count'						=> $campaign->donors_count,
			'amount_raised'						=> $campaign->amount_raised,
			'amount_raised_in_cents'			=> $campaign->amount_raised_in_cents,
			'percent_funded'					=> $campaign->percent_funded,
			'photo_original'					=> (stristr($campaign->photo->original, 'http') ? $campaign->photo->original : ''),
		);

		// Does this exist in the DB already? If it does, update it.
		$post_exists = new WP_Query(
			array(
			'posts_per_page'	=> 1,
			'post_type'		    => $this->dntly_options['dntly_campaign_posttype'],
			'post_status'     => array( 'publish', 'private', 'draft', 'pending', 'future', 'pending'), // essentially match any not in the trash
			'meta_query'      => array(
				array(
					'key'		      => '_dntly_id',
					'value'	      => $campaign->id
				),
				array(
					'key'         => '_dntly_environment',
					'value'       => $this->dntly_options['environment'],
				)
			))
		);

		if( isset($post_exists->posts[0]->ID) ){
			$post_id = $post_exists->posts[0]->ID;
			$trans_type = "update";
		}
		else{
			$post_params = array(
				'post_type'		  => $this->dntly_options['dntly_campaign_posttype'],
				'post_title'	  => $campaign->title,
				'post_content'	=> $campaign->description,
				'post_status'	  => ($this->dntly_options['sync_to_private']?'private':'publish'),
			);
			$post_id = wp_insert_post($post_params);

			if( (stristr($campaign->photo->original, 'http')) ){
				$image = $campaign->photo->original;
			}else{
				$image = null;
			}

			if( $image ){
				$img_filetype = wp_check_filetype( $image, null );
				$img_name = strtolower( preg_replace('/[\s\W]+/','-', $campaign->title . '-dntly-img') );
				$img_path = $this->wordpress_upload_dir['path'] . "/" . $img_name . "." . $img_filetype['ext'];
				$img_sub_path = $this->wordpress_upload_dir['subdir'] . "/" . $img_name . "." . $img_filetype['ext'];
				$image_file = file_get_contents( $image );
				file_put_contents($img_path, $image_file);
		        $attachment = array(
		         'post_type'      => 'attachment',
		         'post_title'     => 'Donately Campaign - ' . $campaign->title,
		         'post_parent'    => $post_id,
		         'post_status'    => 'inherit',
		         'post_mime_type' => $img_filetype['type'],
		        );
		        $attachment_id = wp_insert_post( $attachment, true );
		        add_post_meta($post_id, '_thumbnail_id', $attachment_id);
		        add_post_meta($attachment_id, '_wp_attached_file', $img_sub_path, true );
			}

			$trans_type = "add";
		}

		update_post_meta($post_id, '_dntly_data', $_dntly_data);
		update_post_meta($post_id, '_dntly_id', $campaign->id );
		update_post_meta($post_id, '_dntly_account_id', $account_id);
		update_post_meta($post_id, '_dntly_environment', $this->dntly_options['environment']);
		update_post_meta($post_id, '_dntly_amount_raised', $campaign->amount_raised);
		update_post_meta($post_id, '_dntly_goal', $campaign->campaign_goal);

		if( !empty($this->dntly_options['console_details']) && !$this->do_not_log() ){
			dntly_transaction_logging("\nCampaign: {$trans_type} {$campaign->title} (dntly_id:{$campaign->id} | local_id:{$post_id})", 'print_debug');
		}

		return array('add' => ($count_campaigns['add']+($trans_type=='add'?1:0)), 'update' => ($count_campaigns['update']+($trans_type=='update'?1:0)), 'skip' => $count_campaigns['skip']);
	}

	function add_update_fundraiser($fundraiser, $account_id, &$count_fundraisers){

		if( $fundraiser->archived ){
			$count_fundraisers['skip']+=1;
			return null;
		}

		$trans_type = null;

		$this->api_runtime_id = $fundraiser->person_id;
		$this->build_api_methods();

		$_dntly_data = array(
			'dntly_id'				=> $fundraiser->id,
			'account_title'			=> $this->dntly_options['account_title'],
			'account_id'			=> $account_id,
			'campaign_id'			=> $fundraiser->campaign_id,
			'goal'                  => $this->convert_amount_in_cents_to_amount($fundraiser->goal_in_cents),
			'permalink'				=> $fundraiser->permalink,
            'public_url'            => $fundraiser->public_url,
            'donor_count'           => $fundraiser->donor_count,
			'amount_raised'			=> $fundraiser->amount_raised,
			'person'				=> $fundraiser->person_full_name_or_email,
			'photo_original'		=> (stristr($fundraiser->photo->original, 'http') ? $fundraiser->photo->original : ''),
		);

		$post_exists = new WP_Query(
			array(
			'posts_per_page'	=> 1,
			'post_type'		    => 'dntly_fundraisers',
			'post_status'     => array( 'publish', 'private', 'draft', 'pending', 'future', 'pending'), // essentially match any not in the trash
			'meta_query'      => array(
				array(
					'key'		      => '_dntly_id',
					'value'	      => $fundraiser->id
				),
				array(
					'key'         => '_dntly_environment',
					'value'       => $this->dntly_options['environment'],
				)
			))
		);

		if( isset($post_exists->posts[0]->ID) ){
			$post_id = $post_exists->posts[0]->ID;
			$trans_type = "update";
		}
		else{

			$post_params = array(
				'post_type'     => 'dntly_fundraisers',
				'post_title'    => $fundraiser->title,
				'post_content'	=> $fundraiser->description,
				'post_status'   => ($this->dntly_options['sync_to_private']?'private':'publish'),
			);
			$post_id = wp_insert_post($post_params);

			if( (stristr($fundraiser->photo->original, 'http')) ){
				$image = $fundraiser->photo->original;
			}else{
				$image = null;
			}

			if( $image ){
				$img_filetype = wp_check_filetype( $image, null );
				$img_name = strtolower( preg_replace('/[\s\W]+/','-', $fundraiser->title . '-dntly-img') );
				$img_path = $this->wordpress_upload_dir['path'] . "/" . $img_name . "." . $img_filetype['ext'];
				$img_sub_path = $this->wordpress_upload_dir['subdir'] . "/" . $img_name . "." . $img_filetype['ext'];
				$image_file = file_get_contents( $image );
				file_put_contents($img_path, $image_file);
		        $attachment = array(
		         'post_type'      => 'attachment',
		         'post_title'     => 'Donately Fundraiser - ' . $fundraiser->title,
		         'post_parent'    => $post_id,
		         'post_status'    => 'inherit',
		         'post_mime_type' => $img_filetype['type'],
		        );
		        $attachment_id = wp_insert_post( $attachment, true );
		        add_post_meta($post_id, '_thumbnail_id', $attachment_id);
		        add_post_meta($attachment_id, '_wp_attached_file', $img_sub_path, true );
			}

			$trans_type = "add";
		}

		update_post_meta($post_id, '_dntly_data', $_dntly_data);
		update_post_meta($post_id, '_dntly_id', $fundraiser->id );
		update_post_meta($post_id, '_dntly_account_id', $account_id);
		update_post_meta($post_id, '_dntly_campaign_id', $fundraiser->campaign_id);
		update_post_meta($post_id, '_dntly_environment', $this->dntly_options['environment']);
		update_post_meta($post_id, '_dntly_amount_raised', $fundraiser->amount_raised);
		update_post_meta($post_id, '_dntly_goal', $this->convert_amount_in_cents_to_amount($fundraiser->goal_in_cents));

		if( !empty($this->dntly_options['console_details']) && !$this->do_not_log() ){
			dntly_transaction_logging("\nFundraiser: {$trans_type} {$fundraiser->title} (dntly_id:{$fundraiser->id} | local_id:{$post_id})", 'print_debug');
		}

		$count_fundraisers['add']+=($trans_type=='add'?1:0);
		$count_fundraisers['update']+=($trans_type=='update'?1:0);

		return $post_id;
	}

	function create_fundraiser(){
		$this->suppress_logging = true;
		$dntly_result = $this->array_to_object($_POST['dntly_result']);
		if( $dntly_result->success ){
			if( isset($dntly_result->fundraiser->id) ){
				$count_fundraisers = array('add' => 0, 'update' => 0);
				$post_id = $this->add_update_fundraiser($dntly_result->fundraiser, $this->dntly_account_id, $count_fundraisers);
				$permalink = get_permalink($post_id);
				print json_encode(array('success' => true, 'url' => $permalink));
			}
			else{
				print json_encode(array('success' => false, 'message' => 'Error finding new fundraiser url' ));
			}
		}
		else{
			print json_encode(array('success' => false, 'message' => $dntly_result->error->message ));
		}
		die();
	}

	function lookup_person(){
		$email = $_POST['email'];
		$this->suppress_logging = true;
		$lookup_person = $this->make_api_request("person_exists", true, array('email' => $email));
		if( isset($lookup_person->success) ){
			if( isset($lookup_person->people[0]) ){
				print json_encode(array('success' => true, 'url' => $lookup_person->people[0]));
			}
			else{
				print json_encode(array('success' => false, 'message' => 'No Match Found'));
			}
		}
		else{
			print json_encode(array('success' => false, 'message' => 'Connection Error'));
		}
		die();
	}

	function get_fundraisers($referrer=null){
		$count  = 100;
		$offset = 0;

		$count_fundraisers = array('add' => 0, 'update' => 0, 'skip' => 0);
		$get_fundraisers 	= $this->make_api_request("get_fundraisers", true, array('count' => $count));
		$all_fundraisers 	= $get_fundraisers->total_count;

		while(  $all_fundraisers > ($count_fundraisers['add'] + $count_fundraisers['update'] + $count_fundraisers['skip']) ){
			foreach($get_fundraisers->fundraisers as $fundraiser){

				$this->add_update_fundraiser($fundraiser, $this->dntly_options['account_id'], $count_fundraisers);
			}
			if( $all_fundraisers > ($count_fundraisers['add'] + $count_fundraisers['update'] + $count_fundraisers['skip']) ){
				$get_fundraisers = $this->make_api_request("get_fundraisers", true, array('count' => $count, 'offset' => ($offset+=$count)) );
			}
		}

		if( $count_fundraisers['add'] || $count_fundraisers['update'] || $count_fundraisers['skip'] ){
            $this->update_account_stats( array('total_fundraisers_count' => ($count_fundraisers['add'] + $count_fundraisers['update'] + $count_fundraisers['skip']) ) );
			dntly_transaction_logging("Synced Fundraisers - ".$count_fundraisers['add']." added, ".$count_fundraisers['update']." updated ".$count_fundraisers['skip']." skipped ");
		}
		else{
			dntly_transaction_logging('Synced Fundraisers Error, no fundraisers found', 'error');
		}
		die();
	}

}
