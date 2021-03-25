=== Caldera Forms - CiviCRM Redirect Fix ===
Requires at least: 5.6
Tested up to: 5.6
Stable tag: 1.9.4
License: GPLv2
Requires PHP: 5.6

Helper plugin for Caldera Forms to make redirections from a CF form to a CiviCRM form work.

== Description ==

Caldera Forms includes a Redirect Processor that redirects the user to a specified URL on successful submission of the form.  In theory, the redirection URL can be passed to the form as a query parameter but bugs prevent that from working.

== Installation ==

Upload the cf-civicrm-redirect folder to /wp-content/plugins/
Activate the plugin through the 'Plugins' menu in WordPress
No configuration is required.

== Usage ==

Configure your Caldera Form as usual.  Add the Redirect Processor and configure it to use eg '{get:redirect_to}'
When you link to the Caldera Form, include the parameter 'redirect_to=' and the urlencoded CiviCRM.  Eg 'redirect_to=%2Fcivicrm%3Fciviwp%3DCiviCRM%26q%3Dcivicrm%252Fevent%252Fregister%26id%3D7%26reset%3D%1'

== Example ==

An organization wants to use a Caldera Form for extended registration.  When the user goes to a CiviCRM event registration page, if extended registration has not been completed the user is redirected to the Caldera Form including a 'redirect_to=' parameter back to the CiviCRM event registration page. 

== Technical notes ==

Problems fixed by this plugin:
1) Caldera_Forms_Magic_Doer::do_bracket_magic() is passed a $referrer as a string, but the processing for '{get:*}' tags expects an array
2) Caldera_Forms::do_redirect() adds the calling query params to the redirection url.  Not sure what the use case is for that, but when redirecting to CiviCRM url's we don't want the original params, including this redirect
3) Caldera_Forms::do_magic_tags() attempts to expand '{get.*}' magic and then expand field tags.  Since the encoded CiviCRM URL includes '...%...%...', the field expansion mangles the URL.

