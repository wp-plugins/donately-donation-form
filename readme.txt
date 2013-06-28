=== Donately Donation Form ===
Contributors: shanaver, bryanmonzon, elzizzo
Tags: donations, donately, donate.ly, dntly, transactions, widget, stripe, authorize.net, non-profit
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple, Secure Online Donation Form.

== Description ==

Non-profit groups can now take secure donations from any page on their site.  Include the widget in a sidebar or use the shortcode to drop it in a page or post.

== Installation ==

1. Upload dntly folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create a free Donately account at http://www.dntly.com
1. Enter your Donately admin user email address & password on the plugin settings page
1. Place `<?php do_action('dntly_300width_form'); ?>` in your templates to create a secure donation form

== Frequently Asked Questions ==

= Do I need to have a Donately account to use this plugin =

Yes, setting up a Donately account is free and can take less than 5 minutes.

= What processors/gateways can I use? =

Donately is integrated with Stripe & Authorize.net - you can use an existing account, or create a new one.

= Can I take recurring donations? =

Yes.  You will need to enable CIM if you are using Authorize.net to process for you.

= Is there any fees to take donations? =

There are no monthly fees from Donately, but there is a small (under 2% average) per transaction fee.  You gateway will also charge a small transaction fee (around 3%) for processing credit cards.  Authorize.net charges a $20/monthly fee for enabling CIM (needed for recurring transactions).

== Screenshots ==

1. Settings
2. Widget
3. form in action

== Changelog ==

= 1.0.0 =
* Initial Wordpress.com version

= 1.0.1 =
* Readme Tweaks

= 1.0.2 =
* Banner fix

= 1.0.3 =
* Title Change

= 1.1.0 =
* Add custom account & fundraiser IDs

= 1.2.0 =
* Add more form.js options

= 1.2.1 =
* Add debug option for development testing

= 1.2.2 =
* Better alert on get_account error

= 1.2.3 =
* Add option for using your own custom posttype instead of the default 'Dntly Campaigns'

= 2.0.0 =
* Add ability to sync Donately Fundraisers to wordpress
* Add option for using your own custom posttype instead of the default 'Dntly Fundraisers'
* Add another sync option (30 minutes & 60 minutes now available)
* Add logging to settings page & include options to hide all debugging settings (note: must set DNTLY_DEBUG=true in dntly.php)

= 2.0.1 =
* Backwards compatibility fix for pre PHP 5.3 - thanks BShurilla09

= 2.0.2 =
* Hide Debugging Switch

= 2.0.3 =
* sniff for undefined constant

= 2.0.4 =
* fix widget bug

= 3.0.0 =
* New Donately Donation form functionality: social sharing & tracking on after-donation screen
* add anonymous option
* add on behalf of field
* add tracking code field
* simplify options page
* simplify form script
* namespace all css classes
* fixed missing closing tag bug

= 3.0.1 =
* simplify fundraiser create urls
* fix bug in widget variable

== Upgrade Notice ==

= 1.0.0 =
Initial release

