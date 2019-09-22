=== Deploy Webhook Button ===
Contributors: lukesecomb, robertmarshall, kimhornung, marijoo, riddla
Tags: webhook, netlify, deploy, gatsbyjs, static, build
Requires at least: 4.4.0
Tested up to: 5.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily deploy static sites using Wordpress and Netlify

== Description ==

Easily deploy static sites using Wordpress and Netlify

**Build** Quickly and easily send webhooks to build your Netlify

**Status** Check the status of your latest build to see if it was successful without even leaving Wordpress

= Links =
* [Website](https://github.com/lukethacoder/deploy-webhook-button)
* [Documentation](https://github.com/lukethacoder/deploy-webhook-button)
* [Author](https://lukesecomb.digital)


== Installation ==

From your WordPress dashboard

1. **Visit** Plugins > Add New
2. **Search** for "Deploy Webhook Button"
3. **Activate** Deploy Webhook Button from your Plugins page
4. **Click** on the new menu item "Deploy Webhook Button" and enter your site details/keys
5. **Enter** enter your site_id, webhook POST address, Netlify API Key, and User-Agent
6. **Read** the documentation to [get started](https://github.com/lukethacoder/deploy-webhook-button)


== Screenshots ==

1. Settings Page.


== Changelog ==

= 1.1.3 =
* Translated strings added to php code

= 1.1.2 =
* Added Netlify scheduling settings
* Refactored settings names to allow easier scaling

= 1.1.1 =
* Bug fix for new permission hooks not working for "Webhook Deploy" menu and "Developer Settings" submenu

= 1.1.0 =
* Add Deploy Button and Deploy Status to admin bar
* Add `manage_options` for devs to manage permissions
* Nice comments in `php` code
* Remove un-needed `add_submenu_items()` params

= 1.0.0 =
* Fixed UI
* Seperate Developer Settings and User Build Screen

= 0.1.0 =
* Initial Release

View full changelog: https://github.com/lukethacoder/deploy-webhook-button
