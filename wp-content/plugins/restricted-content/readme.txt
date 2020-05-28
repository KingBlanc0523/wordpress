=== Restricted Content ===
Contributors: tickera
Donate link: https://tickera.com
Tags: restricted content, restrict content, restrict access, protect content, protected content logged in, role, capability, product, ticket, tickera, woocommerce
Requires at least: 4.3
Tested up to: 5.2.1

Restrict content easily to logged in users, users with a specific role or capability, Tickera or WooCommerce users...

== Description ==

With the Restrict Content plugin you can easily control access to your content of posts, pages and other custom post types.

You can restrict / show content to:

* Everyone
* Logged in users
* Users with a specific role (administrator, editor, author, subscriber, etc)
* Users with a specific [capability](https://codex.wordpress.org/Roles_and_Capabilities "WordPress Roles and Capabilities")
* Tickera users (who purchased any ticket, any ticket for a specific event or a specific ticket type)
* WooCommerce users (who made a purchase or who purchased a specific product)

= Integration =

This plugin is integrated with following WordPress plugins:

* [Tickera Event Ticketing System for WordPress](https://tickera.com/ "Event Ticketing System for WordPress")
* [WooCommerce](https://wordpress.org/plugins/woocommerce/ "WooCommerce is a powerful, extendable eCommerce plugin that helps you sell anything. Beautifully.") users
* More to come ;)

= Documentation =

Stuck? Check out the [plugin documentation](https://tickera.com/tickera-documentation/restricted-content/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=documentation "Restricted Content Documentation")


== Installation ==

1. Install plugin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Restricted Content to set up different restriction messages
4. Add restrictions to your posts, pages or various custom post types

== Screenshots ==
1. Content availability metabox which is visible in a post / page / custom post screen in the admin panel
2. Content availability metabox for Tickera users
3. Content availability metabox for WooCommerce users
4. Plugin settings

== Changelog ==

= 1.0.3 =
* Removed restricted content from tc_order custom post type
* Added new hook for developers (rsc_skip_post_types)
* Fixed wp_localize_script var

= 1.0.2 =
* Updated language file

= 1.0.1 =
* Fixed issue with empty restriction messages
* Removed content availability box from Tickera ticket type screen in the admin

= 1.0 =
* First release
