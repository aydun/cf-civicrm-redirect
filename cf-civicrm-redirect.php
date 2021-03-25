<?php
/*
  Plugin Name: Caldera Forms - CiviCRM Redirect Fix
  Description: This fixes a couple of problems that stop the Redirect processor working with CiviCRM links
  Author: Aidan Saunders
  Author URI: http://www.squiffle.uk
  Version: 1.0.0
*/

// Remove the default behaviour
remove_filter('caldera_forms_submit_redirect_complete', array('Caldera_Forms', 'do_redirect'), 10);   

// Add our own handling
add_filter('caldera_forms_submit_redirect_complete', function($referrer, $form, $processid) 
  {
    if (isset($form['processors'])) {
      foreach ($form['processors'] as $processor) {
        if ($processor['type'] == 'form_redirect') {
   
          if (isset($processor['conditions']) && !empty($processor['conditions']['type'])) {
            if (!Caldera_Forms::check_condition($processor['conditions'], $form)) {
              continue;
            }
          }
          if (!empty($processor['config']['url'])) {
 
             // set message
             add_filter('caldera_forms_render_notices', array(
               'Caldera_Forms',
               'override_redirect_notice'
             ), 10, 2);
    
             // Don't mess about with query args - we're redirecting to a civi page with arguments
             // Can't call Caldera_Forms::do_magic_tags because it the encoded url from the '{get:redirect_to}'
             // includes '..%...%..' which gets wrongly interpreted as a magic field.
             // Instead, call Caldera_Forms_Magic_Doer::do_bracket_magic() directly, ... and fix $referer
             // $redirect = Caldera_Forms::do_magic_tags($processor['config']['url']);
             
             // String received, but array expected for get:* tags
             if (!is_array($referrer)) {
               $referrer = parse_url($referrer);
               if (!empty($referrer['query'])) {
                 parse_str($referrer['query'], $referrer['query']);
               }
             }

             $redirect = Caldera_Forms_Magic_Doer::do_bracket_magic($processor['config']['url'], $form, null, null, $referrer);
             return $redirect;
          }
        }
      }
    }
    return $referrer;
  }, 10, 4);


