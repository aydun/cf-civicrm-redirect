<?php
/*
  Plugin Name: Caldera Forms - CiviCRM Redirect Fix
  Description: This fixes a couple of problems that stop the Redirect processor working with CiviCRM links
  Author: Aidan Saunders
  Author URI: http://www.squiffle.uk
  Version: 1.0.0
*/

// Remove the default behaviour
// Don't know whether there is a better way...
// Need to remove the default 'do_redirect' filter, and needs to be after CF is loaded.
// We need the class instance to remove it.  Looks like get_instance() is how we should get hold of that.
// But we can't use 'caldera_forms_core_init' since that is in _construct() so get_instance() loops
// Might be easier if 'caldera_forms_core_init passed $this to the action ... but it doesn't.
// So use 'plugins_loaded'
add_action( 'plugins_loaded', function() {
  $instance = Caldera_Forms::get_instance();
  remove_filter('caldera_forms_submit_redirect_complete', array($instance, 'do_redirect'), 10);
});

// Add our own handling
add_filter('caldera_forms_submit_redirect_complete', function($referrer, $form, $processid) {
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
}, 10, 3);

// Define our own magic tag of '{login_redirect_url}' using the redirect_to param
add_filter('caldera_forms_do_magic_tag', function($value, $tag) {
  if ($tag == '{login_redirect_url}') {
    $redir = $_GET['redirect_to'] ?? '';
    $redir = Caldera_Forms_Sanitize::sanitize($redir);
    $value = wp_login_url($redir);
  }
  return $value;
}, 10, 2);


// Bypass %field% magic processing if value contains 'redirect_to' so is a URL
// Needed for {login_redirect_url}
add_filter('caldera_forms_pre_do_field_magic', function($default, $value, $matches, $entry_id, $form) {
  if (strpos($value, 'redirect_to') !== FALSE) {
    return $value;
  }
  else {
    return $default;
  }
}, 10, 5);
