(function($){

	window.dntly = {};
	var dntly = window.dntly;

	dntly.initialize = function() {
		dntly.setElements();
		dntly.getToken();
		dntly.resetToken();
		dntly.getAccountStats();
		dntly.getCampaigns();
		dntly.getFundraisers();
		jQuery(document).ajaxStart(function() {
			jQuery('#spinner').show();
		})
		jQuery(document).ajaxStop(function() {
			jQuery('#spinner').hide();
		})

	};

	dntly.setElements = function() {
		dntly.elems = {};
		dntly.elems.form = {};
		dntly.elems.form.form = jQuery('#dntly-form');
		dntly.elems.form.username = dntly.elems.form.form.find('#dntly-user-name');
		dntly.elems.form.password = dntly.elems.form.form.find('#dntly-user-password');
		dntly.elems.form.environment = dntly.elems.form.form.find('#dntly-environment');
		dntly.elems.form.account = dntly.elems.form.form.find('#dntly-account');
		dntly.elems.form.token = dntly.elems.form.form.find('#dntly-user-token');
		dntly.elems.form.tokenBtn = dntly.elems.form.form.find('#dntly-get-token');
		dntly.elems.sync_account_stats_btn = jQuery('#dntly-sync-account-stats');
		dntly.elems.sync_campaigns_btn = jQuery('#dntly-sync-campaigns');
		dntly.elems.sync_fundraisers_btn = jQuery('#dntly-sync-fundraisers');
		dntly.elems.account = jQuery('#dntly-account').val();
		dntly.elems.dntly_reset_token_btn = jQuery('#dntly-reset-token');
		dntly.elems.logging_container = jQuery('#dntly_table_logging');

		dntly.properties = {};
	};

	dntly.dntly_transaction_logging = function(message, status) {
		jQuery.ajax({
			'type'  : 'post',
			'url'		: ajaxurl,
			'data'	: {
							'action'	: 'dntly_transaction_logging',
							'message'	: message,
							'status'  : status
						  },
			'success'	: function(response) { dntly.refreshLog(0); },
			'error'		: function(response) { console.log(response); }
		});
	};

	dntly.getToken = function() {
		function getTokenReponse(response){
			var jresponse = jQuery.parseJSON(response);
			if(jresponse.success != "true" && jresponse.success != true){
				if(jresponse.error){
					alert(jresponse.error.message);
				}else{
					alert('An Error Occurred');
				}
				return false;
			}
			else{
				dntly.elems.form.token.val(jresponse.data.token);
				dntly.elems.form.form.submit();
				dntly.dntly_transaction_logging('Retreived new Donately authentication token');
				return true;
			}
		}
		dntly.elems.form.tokenBtn.bind('click', function(e) {
			e.preventDefault();
			env = jQuery('input[name^="dntly_options[environment]"]').val() || jQuery("select[id=dntly-environment] option:selected").val();
			jQuery.ajax({
				'type'  : 'post',
				'url'		: ajaxurl,
				'data'	: {
								'action'	: 'dntly_get_api_token',
								'email'   : dntly.elems.form.username.val(),
								'password'	: dntly.elems.form.password.val(),
								'environment'	: env
							  },
				'success'	: function(response) { getTokenReponse(response); },
				'error'		: function(response) { alert('There was an error, please contact admin@fiftyandfifty.org if this persists.') }
			});
		})
	};

	dntly.updateAccountSelect = function(response) {
		var api = jQuery.parseJSON(response);
		var accounts = '';
		jQuery.each(api.accounts, function(i, val) {
			accounts += val.title + ', ';
		});
		console.log('Found: ' + accounts);
	}

	dntly.getAccountStats = function() {
		dntly.elems.sync_account_stats_btn.bind('click', function(e) {
			e.preventDefault();
			jQuery.ajax({
				'type'  : 'post',
				'url'		: ajaxurl,
				'data'	: {
								'action'	: 'dntly_get_account_stats'
							  },
				'success'	: function(response) { console.log(response); dntly.refreshLog(0); },
				'error'	: function(response) { alert('Error Getting Account Stats'); console.log(response); }
			})
		});
	}

	dntly.getCampaigns = function() {
		dntly.elems.sync_campaigns_btn.bind('click', function(e) {
			e.preventDefault();
			jQuery.ajax({
				'type'  : 'post',
				'url'		: ajaxurl,
				'data'	: {
								'action'	: 'dntly_get_campaigns'
							  },
				'success'	: function(response) { console.log(response); dntly.refreshLog(0); },
				'error'	: function(response) { alert('Error Getting Campaigns'); console.log(response); }
			})
		});
	}

	dntly.getFundraisers = function() {
		dntly.elems.sync_fundraisers_btn.bind('click', function(e) {
			e.preventDefault();
			jQuery.ajax({
				'type'  : 'post',
				'url'		: ajaxurl,
				'data'	: {
								'action'	: 'dntly_get_fundraisers'
							  },
				'success'	: function(response) { console.log(response); dntly.refreshLog(0); },
				'error'	: function(response) { alert('Error Getting Campaigns'); console.log(response); }
			})
		});
	}

	dntly.refreshLog = function(page) {
		jQuery.ajax({
			'type'  : 'post',
			'url'		: ajaxurl,
			'data'	: {
							'action'	: 'dntly_get_table_logging',
							'd_page' : page
						  },
			'success'	: function(response) { dntly.elems.logging_container.html(response); },
			'error'	: function(response) { console.log(response); }
		})
	}

	dntly.resetToken = function() {
		dntly.elems.dntly_reset_token_btn.bind('click', function(e) {
			e.preventDefault();
			dntly.elems.form.username.val('');
			dntly.elems.form.password.val('');
			dntly.elems.form.environment.val('');
			dntly.elems.form.account.val([]);
			dntly.elems.form.token.val('');
			dntly.elems.form.form.submit();
		});
	}

	jQuery(document).ready(function() {
		dntly.initialize();
		dntly.refreshLog(0);
	});


})(jQuery);