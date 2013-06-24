<?php
  $dntly = new DNTLY_API;
  $form_js_url = ( isset($dntly->dntly_options['environment']) ? $dntly->api_scheme[$dntly->dntly_options['environment']] : $dntly->api_scheme['production'] ) . '://';
  $form_js_url .= $dntly->api_subdomain . '.' . ( isset($dntly->dntly_options['environment']) ? $dntly->api_domain[$dntly->dntly_options['environment']] : $dntly->api_domain['production'] ) . '/assets/js/v1/form.js';
?>
<script class="donately-formjs" src='<?php print $form_js_url ?>' type='text/javascript' async='async' 
  data-donately-id='<?php print (isset($account)?$account:$dntly->dntly_options['account_id']) ?>' 
  data-donately-campaign-id='<?php print (isset($campaign)?$campaign:'0') ?>' 
  data-donately-fundraiser-id='<?php print (isset($fundraiser)?$fundraiser:'0') ?>' 
  data-donately-css-url='<?php print (isset($css_url)?$css_url:'') ?>' 
  data-donately-iframe-height='<?php print (isset($iframe_height)?$iframe_height:'') ?>' 
  data-donately-iframe-width='<?php print (isset($iframe_width)?$iframe_width:'') ?>' 
  data-donately-email='<?php print (isset($email)?$email:'') ?>' 
  data-donately-amount='<?php print (isset($amount)?$amount:'') ?>' 
  data-donately-ssl='<?php print (isset($ssl)?(bool)$ssl:'false') ?>' 
  data-donately-address='<?php print (isset($address)?(bool)$address:'false') ?>' 
  data-donately-phone='<?php print (isset($phone)?(bool)$phone:'false') ?>' 
  data-donately-anonymous='<?php print (isset($anonymous)?(bool)$anonymous:'false') ?>' 
  data-donately-comment='<?php print (isset($comment)?(bool)$comment:'') ?>' 
  data-donately-onbehalf='<?php print (isset($onbehalf)?(bool)$onbehalf:'') ?>' 
  data-donately-tracking-codes='<?php print (isset($tracking_codes)?$tracking_codes:'') ?>' 
  data-donately-dont-send-receipt-email='<?php print (isset($dont_send_receipt_email)?(bool)$dont_send_receipt_email:'false') ?>' >
</script>