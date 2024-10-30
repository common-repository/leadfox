=== Leadfox for Wordpress ===
Contributors: Leadfox
Tags: lead generation, marketing automation, email marketing, landing page, popup
Requires at least: 3.7
Tested up to: 6.5.4
Stable tag: 2.1.8
License: GPLv2 or later

Integrate Leadfox tracking codes and sync your contacts with a Leadfox contact list.

== Description ==
Leadfox converts visitors into ripe leads and paying customers: From creating convincing landing pages that capture more leads to running smarter email campaigns that better nurture new customers LeadFox makes deploying your online strategy easy and automatic.

Leadfox's Wordpress plugin integrates the Leadfox tracking codes on your website and sync all your contacts with your Leadfox contact list.

If you don't have an account yet, <a href="https://app.leadfox.co/signup/">sign up for a free account</a>
<a href="https://leadfox.co/">Leadfox website</a>

== Installation ==
1. Upload the plugin files to the '/wp-content/plugins/leadfox' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Plugin Name screen to configure the plugin
4. Enter your API key and secret key to begin syncing your contacts with LeadFox and integrate the LeadFox tracking codes into your website. (You can find the information in your Leadfox account under Manage => API
5. Choose the Leadfox list you want to send your Wordpress contacts.
6. Click confirm. Your Leadfox tracking codes are now integrated on your website!

== Frequently Asked Questions ==
I can't choose any Leadfox list, how can I add a new list ?
Go on your Leadfox account, click on "contacts" and on "Add list" in the top

== Screenshots ==
1. Enter your API key and secret key to begin.
2. Choose the Leadfox list you want to send your Wordpress contacts.
3. Your Leadfox tracking codes are now integrated on your website!

== Changelog ==

= 2.1.8 =
New version: test with wordpress 6.5.4.

= 2.1.7 =
Fix styles registration.

= 2.1.6 =
Internal deploy tests.

= 2.1.5 =
New version

= 2.1.4 =
Internal deploy tests.

= 2.1.3 =
Removed trailing quote.

= 2.1.2 =
Fixed internal release version.

= 2.1.1 =
Fixed version number and readme details.

= 2.1 =
Added support for the new Leadfox tracking code.

= 2.04 =
Fixed a bug with the call to add_action("plugins_loaded", ...)

= 2.03 =
Fixed a bug with configuration options being lost when reloading the options page.

= 2.02 =
Fixed incompatibility with other email plugins.

= 2.01 =
Minor bug fix concerning authentification.

= 2.0 =
Plugin restructuration in order to support the new Leadfox architecture and Leadfox the Rest API.

= 1.0.3 =
Code refactoring and lighter footprint.
Fixed table prefix with table wp_usermeta.

= 1.0.2 =
Checkout fixing

= 1.0.1 =
If the required values i.e. first name, last name and email is not empty then contacts will be synched.

= 1.0 =
Initial plugin launch!
