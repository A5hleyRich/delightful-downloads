=== Delightful Downloads ===
Contributors: A5hleyRich
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=95AQB5DP83XAU
Tags: download, manager, downloads, monitor, shortcode, delightful downloads, file, counter, tracking
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A super-awesome downloads manager for WordPress.

== Description ==

Delightful Downloads is a super-awesome downloads manager for WordPress. Easily add download links/buttons to posts and track download statistics.

= Features =
+ File upload via WordPress admin.
+ Settings page to set default options.
+ Member only downloads with page redirect for non-logged in users.
+ Shortcode to display text link or button.
+ Shortcode to display downloads count and file size.
+ Shortcode to display total blog downloads.
+ Editor button to automatically generate shortcodes.
+ Various button styles included with the ability to customise through CSS.
+ Add download buttons to sidebar widgets.
+ Localization support.

== Installation ==

1. Upload `delightful-downloads` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Downloads > Settings and configure the options.

== Frequently asked questions ==

= How do I add a download link? =

Use the shortcode [ddownload id=*], which can be generated using the shortcode generator.

= How do I display a download file size? =

Use the shortcode [ddownload_size id=*], which can be generated using the shortcode generator.

= How do I display a download file count? =

Use the shortcode [ddownload_count id=*], which can be generated using the shortcode generator.

= How do I display my total blog downloads? =

Use the shortcode [ddownload_total_count].

== Screenshots ==

1. Downloads overview screen.
2. Add new download screen.
3. Logs overview screen.
4. Shortcode generator.
5. Example shortcodes.
6. Settings screen.

== Changelog ==

= 1.2.1 =
+ Improved download links by adding rel="nofollow" to encourage Search Engines not to download files.
+ Improved the file browser so that it no longer shows hidden files.
+ Fixed a bug that caused menu images to not display.
+ Fixed a bug that caused the file browser to not display.
+ Fixed a bug that caused a 'file does not exist' error due to subdomain.
+ Fixed a bug that caused a 'file does not exist' error when using non-standard port numbers for the host server.
+ Removed the option to manually specifiy an upload directory for the file browser.
+ Removed text file error logging in preperation for improved statistics.

= 1.2 =
+ Added dashboard widget.
+ Added an option to the settings screen to set the directory used in the file browser.
+ Added support options to settings screen.
+ Improved caching.
+ Improved [ddownload_total_count] shortcode.
+ Fixed a bug that caused the incorrect time to display in the download logs.

= 1.1.2 =
+ Fixes shortcode formatting.
+ Fixes a bug that caused the file browser to not load.

= 1.1.1 =
+ Fixes headers already sent error.

= 1.1 =
+ Downloads by admins are no longer logged or added to the file's download count.
+ Added the ability to search for downloads in the shortcode generator window.
+ Added ddownload_total_count shortcode button to the shortcode generator window.
+ Added download information to the shortcode generator window.
+ Added cache duration to settings page.
+ Added the ability to manually enter a file URL on the add/edit download screen.
+ Added file browser to add/edit download screen.
+ Improved Ajax upload functionality.
+ Improved performance of ddownload_total_count via caching.
+ Improved internal option handling.
+ Fixed a bug on the settings page causing the Default CSS Styles option not to deselect.
+ Fixed a bug in the download log screen that resulted in not being able to filter by ip address.

= 1.0.1 =
+ Fixes a bug that caused the inability to add a download on the post screen.

= 1.0 =
+ Initial stable plugin release.