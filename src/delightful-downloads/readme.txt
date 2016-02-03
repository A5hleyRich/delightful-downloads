=== Delightful Downloads ===
Contributors: A5hleyRich
Tags: download, manager, downloads, monitor, shortcode, delightful downloads, file, counter, tracking
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 1.6.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A super-awesome downloads manager and statistics tracker for WordPress.

== Description ==

Delightful Downloads is a super-awesome downloads manager for WordPress that allows you to easily add download links, buttons and download information to posts, pages and widget areas. Download statistics are tracked within the WordPress dashboard.

**Features**

* File upload via the WordPress admin area. Absolute file paths and remote file URLs are supported via manual entry.
* Categorise and tag downloads.
* Shortcodes to display download links, buttons and download information within posts, pages and widget areas.
* Shortcode to list downloads, optionally filtered by categories and tags. Order by download count, file size, date, title and random.
* Editor button to automatically generate shortcodes.
* Download statistics tracked within the WordPress dashboard area. Specify a grace period so that multiple log entries are not triggered by the same user (more accurate statistics).
* Automatically delete logs older than a specified number of days.
* Blocks users from accessing files directly.
* Block bots from downloading files.
* Members only downloads with page redirect for non-logged in users.
* Password protected files.
* Open in Browser - Allow files such as PDFs and movies to open directly within the browser window and still track download statistics.
* Various button styles included with the ability to add custom buttons.
* Built in caching for excellent performance.
* Developer friendly.
* Localisation support.

**Add-Ons**

* [Customizer](https://delightfuldownloads.com/add-ons/customizer/?utm_source=WordPress&utm_medium=Repo&utm_content=Customizer&utm_campaign=Description) - Button and list styles editor

**Translations**

Thanks to all of the WP-Translations Team Members.

* Français (French (France)),
* Deutsch (German) - [Connie Müller-Gödecke](http://www.webdeerns.de),
* Italiano (Italian),
* Русский (Russian) - dobrodukh,
* Svenska (Swedish) - [Björn Granberg](http://maskinmedia.se/)

Don't see yours yet? Join our community and make it happen! [WP-Translations.org](http://wp-translations.org)

**Documentation**

The Delightful Downloads documentation can be found [here](https://delightfuldownloads.com/documentation/ "Delightful Downloads Documentation").

**Requirements**

To add and manage downloads via the WordPress Administration screens, you must have a [modern web browser](http://browsehappy.com/ "Browse Happy") with JavaScript enabled.

**GitHub**

If you would like to contribute to the plugin, you can do so on [GitHub](https://github.com/A5hleyRich/Delightful-Downloads).

== Installation ==

1. Upload `delightful-downloads` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Downloads > Settings and configure the options.

Please refer to the [documentation](https://delightfuldownloads.com/documentation/installation/ "Delightful Downloads Documentation") for further instructions.

== Frequently asked questions ==

Please refer to the [FAQ](https://delightfuldownloads.com/documentation/delightful-downloads-faqs/ "Delightful Downloads FAQ") section within the [documentation](https://delightfuldownloads.com/documentation/ "Delightful Downloads Documentation").


== Screenshots ==

1. Downloads overview screen
2. Add new download screen - upload file
3. Add new download screen - existing file
4. Edit download screen
5. Logs overview screen
6. Shortcode generator
7. Example shortcodes
8. Settings screen

== Changelog ==

= 1.6.4 =

* Bug fix: Fatal error when activating add-ons

= 1.6.3 =

* New: [Customizer Add-On](https://delightfuldownloads.com/add-ons/customizer/?utm_source=WordPress&utm_medium=Repo&utm_content=Customizer&utm_campaign=Changelogs) released
* New: Add-Ons page added
* Improvement: Automatically copy shortcodes to clipboard on click
* Bug fix: Dashboard widget incorrect styling
* Bug fix: Settings screen tabs incorrect styling

= 1.6.2 =

* Improvement: Code structure improvements
* Improvement: Add-on subscribe form updated

= 1.6.1 =

* Bug fix: 'PHP Notice:  Undefined index: category'

= 1.6 =

* New: Download list widget
* New: Customize upload directory from _Settings_ screen
* Improvement: Admin notices now dismissible

= 1.5.5 =

* Improvement: Add text domain for translate.wordpress.org integration

= 1.5.4 =

* Bug fix: Minified JavaScript files missing

= 1.5.3 =

* New: Pro subscribe form added to _Settings_ screen
* Improvement: Use minified JavaScript
* Improvement: Translations updated
* Bug fix: PHP warning when _Block User Agents_ option empty
* Bug fix: PHP warning when IP address range supplied by server
* Bug fix: `exclude_tags` attribute not working on `[ddownload_list]` shortcode
* Bug fix: Conflict with Divi theme

= 1.5.2 =

* Enhancement: New shortcode generator window.
* Enhancement: The front-end CSS is now only loaded when the [ddownload] shortcode is detected.
* Enhancement: Langauges updated.
* Bug Fix: Dramatic performance increase within the WordPress admin area.
* Bug Fix: Upgrades no longer fire on plugin activation.

= 1.5.1 =

* Bug Fix: Missing closing tag on the _Add/Edit Download_ Screen.
* Bug Fix: Display of popular downloads on some browsers.
* Bug Fix: Modal windows not closing in Opera.

= 1.5 =

* New: Password protected downloads.
* New: Export logs to CSV file.
* New: Import/Export plugin settings.
* New: The _Open in Browser_ option has been added, which allows files to open directly in the browser window.
* New: The _Folder Protection_ option has been added, which allow you to turn on/off the direct access of upload files.
* New: Swedish translation.
* Enhancement: The _Members Only_ option can now be set on a per-download basis.
* Enhancement: Improved the _Add/Edit Download_ screen.
* Enhancement: Improved the _Download Settings_ screen.
* Enhancement: Improved the _All Downloads_ screen to show the _Members Only_ and _Open in Browser_ columns.
* Bug Fix: The dashboard widget no longer breaks the widget layout, when a download has been removed.

= 1.4 =

* Improved statistics and logging with custom database table.
* Improved dashboard widget.
* Added the option to set a grace period when creating new logs, which can be found on the settings screen, under the statistics tab.
* Added the option to automatically delete old logs, which can be found on the settings screen, under the statistics tab.
* Added migrate legacy logs button to the logs screen.
* Added reset default settings button to the settings screen.
* Added empty logs button to the logs screen.
* Added help and support sidebar to the settings screen.
* Added Italian language.
* Merged shortcodes for easier use.
* Fixed deprecated MySQL function.
* Fixed blank and corrupt downloads bug.

= 1.3.8 =

* Cleaned up the Add Downloads screen.
* Added file size column to the All Downloads screen.
* Added the ability to add offsite (remote) files.
* Added the ability to add files using an absolute server path.
* Added %filename%, %ext% and %mime% wildcards.
* Added new ddownload_list output style - Title (Extension, File size).
* Added compatibility with WPML.
* Fixed Settings screen input labels.
* Fixed file not found bug.
* Fixed block user agents bug.
* Removed deprecated functions in favour of core WordPress functions.

= 1.3.7 =

* Removed trunk directory from distribution.
* Fixed undefined variable warning in jQueryFileTree.

= 1.3.6 =

* Shortcodes date output now uses the date format set in the WordPress settings screen.
* Support screen merged into Settings screen.
* Added PHP safe mode check to Support screen.
* Added filter to customise shortcode date format.
* Added Complete Uninstall option to the settings screen under the advanced tab.
* Added French translation.
* Added German translation. 

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

= 1.6 =

* Compatibility with WordPress 4.4
* Compatibility with PHP 7

= 1.5.3 =

* Bug fixes

= 1.5.2 =

* Bug fixes.
* Compatibility with WordPress 4.0.

= 1.5.1 =

* Bug fixes.

= 1.5 =

* Bug fixes.
* General improvements.

= 1.4 =

* Bug fixes.
* General improvements.

= 1.3.8 =

* Bug fixes.
* General improvements.

= 1.3.7 =

* Bug fixes.

= 1.3.6 =

* General improvements.

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
