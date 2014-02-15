=== Delightful Downloads ===
Contributors: A5hleyRich
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=95AQB5DP83XAU
Tags: download, manager, downloads, monitor, shortcode, delightful downloads, file, counter, tracking
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 1.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A super-awesome downloads manager for WordPress.

== Description ==

Delightful Downloads is a super-awesome downloads manager for WordPress that allows you to easily add download links, buttons and download information to posts, pages and widget areas. Download statistics are tracked within the WordPress dashboard.

**Features**

* File upload via the WordPress admin area.
* Shortcodes to display download links, buttons and download information within posts, pages and widget areas.
* Shortcode to list downloads and customise output via attributes.
* Editor button to automatically generate shortcodes.
* Download statistics tracked within the WordPress dashboard.
* Blocks users from accessing files directly.
* Block bots from downloading files.
* Member only downloads with page redirect for non-logged in users.
* Various button styles included with the ability to add custom buttons.
* Developer friendly.
* Localisation support.

**Translations**

* Russian (Credit - dobrodukh)

**Documentation**

The Delightful Downloads documentation can be found [here](http://ashleyrich.com/wordpress/plugins/delightful-downloads/documentation/ "Delightful Downloads Documentation").

== Installation ==

1. Upload `delightful-downloads` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Downloads > Settings and configure the options.

Please refer to the [documentation](http://ashleyrich.com/wordpress/plugins/delightful-downloads/documentation/ "Delightful Downloads Documentation") for further instructions.

== Frequently asked questions ==

Please refer to the [FAQ](http://ashleyrich.com/wordpress/plugins/delightful-downloads/documentation/frequently-asked-questions/ "Delightful Downloads FAQ") section within the [documentation](http://ashleyrich.com/wordpress/plugins/delightful-downloads/documentation/ "Delightful Downloads Documentation").


== Screenshots ==

1. Downloads overview screen.
2. Add new download screen.
3. Logs overview screen.
4. Shortcode generator.
5. Example shortcodes.
6. Settings screen.

== Changelog ==

= 1.3.5 =

* Added action hook when an invalid download is triggered.
* Fixed button text color.
* Fixed text description for [ddownload_list] on Settings screen.
* Fixed a rare bug that would cause a fatal error on activation.

= 1.3.4 =

* Fixed a bug caused by a conflict with the WP Super Cache plugin.

= 1.3.3 =

* Added Russian translation.

= 1.3.2 =

* Code refactor.
* Improved shortcode generator visuals.
* Improved dashboard widget so that only admins and editors can view it by default.
* Improved logs so that only admins and editors can view them by default.
* Removed post visibility from quick edit on the All Downloads screen.
* Removed some error suppression from process-download.php.
* Fixed a bug caused by a conflict with the TwitWord plugin.
* Security fixes.

= 1.3.1.1 =

* Fixed localisation support.

= 1.3.1 =

* Removed Pretty Permalinks option.
* Fixed a bug that caused downloads to stop working.

= 1.3 =

* Added download tags and categories.
* Added folder protection so that files can no longer be accessed directly.
* Added the option to block user agents such as search bots.
* Added [ddownload_list] shortcode to list downloads.
* Added [ddownload_total_filesize] shortcode to output file size of all files.
* Added [ddownload_total_files] shortcode to output the total number of files.
* Added the option to enable/disable logging of downloads by admins.
* Added the option to specify download address and enable/disable pretty permalinks.
* Added Support Screen.
* Added User Agent to logs screen.
* Added filters for developers.
* Added uninstall functionality.
* Improved [ddownload] with additional wildcards.
* Improved Settings Screen.

= 1.2.3 =

* Fixed a bug that caused downloads to become corrupt when the NextGEN Gallery plugin was active.

= 1.2.2 =

* Added caching to the [ddownload_count] shortcode.
* Added language template po and mo files.
* Improved the Add Download screen with more descriptive help text.
* Fixed a bug that would occasionally show an error when saving plugin settings.
* Fixed a bug that caused the Dashboard Statistics widget to incorrectly display counts based on the WordPress timezone settings.
* Fixed a bug that caused the Logs table to incorrectly show the relative time since a download.
* Fixed a bug that caused the Logs table to incorrectly show the author.

= 1.2.1 =

* Improved download links by adding rel="nofollow" to encourage Search Engines not to download files.
* Improved the file browser so that it no longer shows hidden files.
* Fixed a bug that caused menu images to not display.
* Fixed a bug that caused the file browser to not display.
* Fixed a bug that caused a 'file does not exist' error due to subdomain.
* Fixed a bug that caused a 'file does not exist' error when using non-standard port numbers for the host server.
* Removed the option to manually specify an upload directory for the file browser.
* Removed text file error logging in preparation for improved statistics.

= 1.2 =

* Added dashboard widget.
* Added an option to the settings screen to set the directory used in the file browser.
* Added support options to settings screen.
* Improved caching.
* Improved [ddownload_total_count] shortcode.
* Fixed a bug that caused the incorrect time to display in the download logs.

= 1.1.2 =

* Fixes shortcode formatting.
* Fixes a bug that caused the file browser to not load.

= 1.1.1 =

* Fixes headers already sent error.

= 1.1 =

* Downloads by admins are no longer logged or added to the file's download count.
* Added the ability to search for downloads in the shortcode generator window.
* Added ddownload_total_count shortcode button to the shortcode generator window.
* Added download information to the shortcode generator window.
* Added cache duration to settings page.
* Added the ability to manually enter a file URL on the add/edit download screen.
* Added file browser to add/edit download screen.
* Improved Ajax upload functionality.
* Improved performance of ddownload_total_count via caching.
* Improved internal option handling.
* Fixed a bug on the settings page causing the Default CSS Styles option not to deselect.
* Fixed a bug in the download log screen that resulted in not being able to filter by ip address.

= 1.0.1 =

* Fixes a bug that caused the inability to add a download on the post screen.

= 1.0 =

* Initial stable plugin release.

== Upgrade Notice ==

= 1.3.5 =

* Bug fixes.

= 1.3.4 =

* Bug fixes.

= 1.3.3 =

* Updated translations.

= 1.3.2 =

* Security fixes.
* Bug fixes.

= 1.3.1.1 =

* Bug fixes.

= 1.3.1 =

* Bug fixes.

= 1.3 =

* Additional features and general improvements.
