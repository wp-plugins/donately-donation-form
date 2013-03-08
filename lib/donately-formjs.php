<?php
  $dntly = new DNTLY_API;
  $form_js_url  = 'https://' . $dntly->api_subdomain . '.' . ( isset($dntly->dntly_options['environment']) ? $dntly->api_domain[$dntly->dntly_options['environment']] : $dntly->api_domain['production'] ) . '/assets/js/v1/form.js';
?>
<script class='donately-form' ></script>
<script src='<?php print $form_js_url ?>'  
  data-donately-id='<?php print $dntly->dntly_options['account_id'] ?>' 
  data-donately-campaign-id='<?php print (isset($campaign)?$campaign:'0') ?>' 
  data-donately-css-url='<?php print (isset($css_url)?$css_url:'0') ?>'
  data-donately-address='<?php print (isset($address)?(bool)$address:'false') ?>' 
  data-donately-phone='<?php print (isset($phone)?(bool)$phone:'false') ?>' 
</script>
<script>
  jQuery(function() {
    jQuery('script').bind('donately.success', function(e, resp){
      console.log('donately.success')
      console.log(resp);
      setTimeout(function(){
        window.top.location.href = '<?php print $dntly->dntly_options['thank_you_page'] ?>';
      }, 300);
    });
    jQuery('script').bind('donately.loaded', function(e, resp){
      console.log('donately.loaded');
      attach_secure_message();
    });
  });
</script>