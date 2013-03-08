=== Plugin Name ===
Contributors: shanaver, brianburkett2k
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

== Upgrade Notice ==

= 1.0.0 =
Initial release

