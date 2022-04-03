=== Delightful Downloads ===
Contributors: A5hleyRich, Sven Bolte, others (best of forks)
Tags: download, manager, downloads, monitor, shortcode, delightful downloads, file, counter, tracking, infobox, fixed filetree,ipflag, chartscodes
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 9.9.50
Stable tag: 9.9.50
Requires at least: 5.1
Tested up to: 5.9.2
Requires PHP: 8.0

A super-awesome downloads manager and statistics tracker for WordPress.

== Description ==

Delightful Downloads is a super-awesome downloads manager for WordPress that allows you to easily add download links, buttons and download information to posts, pages and as shortcode in widget block areas. Download statistics are tracked within the WordPress dashboard.

**Features**

* optional: If chartscodes plugin is installed, country of downloader with flag and country code will be listed (last digit of ip set to 0)
* one day pass are unique per download now (user can download named file until 2400 hrs same day). Direct directory access to file is blocked by .htaccess
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
* Localisation support and german translations.
* IP-Address for downloads is stored shortened with last digit = 0

**Requirements**

To add and manage downloads via the WordPress Administration screens, you must have a [modern web browser](http://browsehappy.com/ "Browse Happy") with JavaScript enabled.

**GitHub, Source Code**

If you would like to contribute to the plugin, you can do so on [GitHub](https://github.com/svenbolte/delightful-downloads).
There are several forks (like this one).

**Documentation**
The Delightful Downloads documentation can be found at the root folder of this plugin (documentation-styles.pdf)
More details here:

------------------------------------  FAQ -------------------------
General
Can I manually upload files via FTP/SFTP?
Yes, simply upload them to your server anywhere within the WordPress file structure (usually wp-content/uploads/). Once uploaded, you can enter the file URL into the Add New Download screen.

How do I increase the maximum file upload size?
Please refer to this tutorial on how to increase the maximum file size in WordPress.

How do I add additional allowed file types to the file uploader?
Please refer to this tutorial on how to add additional file types to WordPress.

Usage
How do I output a single download link?
Use the shortcode [[ddownload id="123"]]. Replace the ID with the desired download ID, which can be found in the All Downloads screen.

How do I output a single download file size?
Use the shortcode [[ddownload_size id="123"]]. Replace the ID with the desired download ID, which can be found in the All Downloads screen.

How do I output the number of times a single file has been downloaded?
Use the shortcode [[ddownload_count id="123"]]. Replace the ID with the desired download ID, which can be found in the All Downloads screen.

How do I output a list of download links?
Use the shortcode [[ddownload_list]].

How do I output the number of times all of my files have been download?
Use the shortcode [[ddownload_total_count]].

Troubleshooting
Why when I click a download link does the download count not update?
This is usually caused by being logged in as an admin. Downloads by admin users are not automatically logged, however you can change this behaviour in the Settings screen under the Statistics tab.

If using the [[ddownload_total_count]] shortcode, the count is also cached by default and will only update every few minutes based on the Cache Duration, which is set in the Settings screen under the Advanced tab.

Why do I receive a 403 Forbidden error when I try to access a file directly?
Delightful Downloads automatically blocks direct access to files stored within the wp-content/uploads/delightful-downloads/ directory. To access the file you must use the [[ddownload id="123"]] shortcode.

Why do my files download with the incorrect file extension?
This is caused when WordPress does not know the file type your are serving to users. Please refer to this tutorial on how to add additional file types to WordPress.

Why do my remote files open directly in the browser?
When serving remote files it is not possible to set the correct headers which force a file to be downloaded, instead the browser will attempt to open them.

Why do my files show a file size of unknown?
This is caused when the file does not exist at the location specified or is inaccessible due to incorrect permissions.


-----------------------------------Shortcodes----------------------
[[ddownload_count]]
You can output the number of times a download has been downloaded using the [[ddownload_count]] shortcode. You must supply a download ID and optional attributes can be supplied to further customise the output. To output a download count using the default settings: [[ddownload_count id="123"]]
You can find the ID of a download in the All Downloads screen.

All Downloads Screen

Attributes
The following are optional attributes that will modify the output.

format
Format the number (10000 becomes 10,000). Default: true.

[[ddownload_count id="123" format="false"]]

-----------------------------------------------
[[ddownload_filesize]]
You can output the file size of a download using the [[ddownload_filesize]] shortcode. You must supply a download ID and optional attributes can be supplied to further customise the output. To output a file size using the default settings: [[ddownload_filesize id="123"]]
You can find the ID of a download in the All Downloads screen.

All Downloads Screen

Attributes
The following are optional attributes that will modify the output.

format
Format the number (100000 becomes 97.66 KB). Default: true.

[[ddownload_filesize id="123" format="false"]]

-----------------------------------------------
[[ddownload_list]]
You can output a list of downloads using the [[ddownload_list]] shortcode. Optional attributes can be supplied to customise the output. To output all published downloads, sorted by title in ascending order: [[ddownload_list]]

Attributes
The following are optional attributes that will modify the output.

cache
Cache the results. The amount of time the results are cached for can be set in the Settings screen under the Advanced tab. Default: true.

categories
Show only downloads within specified categories. Comma separated list of category slugs. Default: blank.

exclude_categories
Exclude downloads within specified categories. Comma separated list of category slugs. Default: blank.

exclude_tags
Exclude downloads within specified tags. Comma separated list of tag slugs. Default: blank.

limit
Limit the number of downloads to display. Set to 0 to show all results. Default: 0.

order
The order in which results are displayed. Set to either ASC or DESC. Default: ASC.

orderby
The option in which results should be sorted by. The available options are title, date, count (Download count), filesize, random. Default: title.

relation
When using both categories and tags together this attribute specifies the relationship between the two. The options are AND (Must be in all), OR (Must be in at least one). Default: AND.

style
The output style that should be used to display the list of downloads. The default options are title, title_date, title_downloads, title_filesize. Custom list styles can also be added, as detailed here.

tags
Show only downloads within specified tags. Comma separated list of tag slugs. Default: blank.


-----------------------------------------------
[[ddownload_total_count]]
You can output the total number of downloads for all files using the [[ddownload_total_count]] shortcode. Optional attributes can be supplied to further customise the output. To output the total download count with the default settings: [[ddownload_total_count]]

Attributes
The following are optional attributes that will modify the output.

cache
Cache the results. The amount of time the results are cached for can be set in the Settings screen under the Advanced tab. Default: true.

[[ddownload_total_count cache="false"]]

days
Show the total download count over the last number of days. Set to 0 for all time. Default: 0.

[[ddownload_total_count days="7"]]

format
Format the number (10000 becomes 10,000). Default: true.

[[ddownload_total_count format="false"]]

-----------------------------------------------
[[ddownload]]
You can easily output a single download using the [[ddownload]] shortcode. You must supply a download ID and optional attributes can be supplied to modify the output. To output a download using the default settings: [[ddownload id="123"]]
You can find the ID of a download in the All Downloads screen.

All Downloads Screen
When no optional attributes are supplied Delightful Downloads will use the default values which can be configured in the Settings screen under the Shortcodes tab.

Settings Screen Shortcodes Tab

Attributes
The following optional attributes will modify the output on a per-download basis.

text
Set the text to display on links/buttons. You may use wildcards to dynamically enter data.

[[ddownload id="123" text="Download my File"]]

style
Set which output style to use. The default output styles are link, button or plain text. Custom output styles can also be added, as detailed here.

[[ddownload id="123" style="link"]]

button
Set which button to use if the style attribute is set to button. The default buttons are black, blue, grey, green, purple, red and yellow. Custom buttons can also be added, as detailed here.

[[ddownload id="123" style="button" button="red"]]

class
Add any custom CSS classes to the output.

[[ddownload id="123" class="custom-class button-large"]]

----------Examples----------------
A link output with the file size displayed:
[[ddownload id="123" style="link" text="Download (%filesize%)"]]

A button output with the download count displayed:
[[ddownload id="123" style="button" text="Download (%count%)"]]

Standard infobox layout:
[[ddownload id="3071"]] - Infobox Layout mit File Icon and grey rounded border

-------------------------- Wildcards --------------------------------------------------
Wildcards allow you to dynamically enter data associated with a download. The available wildcards are:

%adminedit%
display a pencil link next to the downloads for admin use (only displayed if logged in with admin rights and if FA present)

%icon% 
display a filetype icon (from a color png sprite)

%category%
display the first dedo category assigned to the download (with FA symbol if fontawesome present)

%tags%
display a list of all dedo tags assigned to the download (with FA symbol if fontawesome present)

%count%
The number of times the download has been downloaded. (with FA symbol if fontawesome present)

%shortdate%
published date and modified date, mod date is shown, stats and other dates on mouseover (with FA symbol if fontawesome present)

%date%
The date the download was published AND the date it was modified (only shown to logged in users).
and the time since creation and modification - both with calendar symbol
+colored calendar symbols (with FA symbols if fontawesome present)

%datesymbol%
The date the download was published OR the date it was modified nad time since mod or creation.
on mouseover: time since create, modification and in between
+colored calendar symbols

%locked%  
displays a red lock if Download is password protected (with FA symbol if fontawesome present)

%ext%
The file extension. (with FA symbol if fontawesome present)

%filename%
The file name of the download. (with FA symbol if fontawesome present)

%filesize%
The file size of the download. (with FA symbol if fontawesome present)

%downloadtime%
display a clock symbol of fontawesome and download times for typical intern lines (16,25,50,100,200,500,1000MBit)

%id%
The unique identifier of the download.

%mime%
The mime type of the download.

%icon%
displays the file type icon for the extension.

%thumb%
get the post thumb of the download (image, right-aligned, zoom-over)

%title%
The title of the download entered in the Add Download screen.

%description%
The ecxerpt of description of the download entered in the Add Download screen

%url%
The URL to the download file.

Where To Use Them: Wildcards can generate dynamic data in the following places:

The text attribute of the [[ddownload]] shortcode. Examples:

[[ddownload text="Download - %date%"]]

[[ddownload text="Download (%filesize%)"]]

[[ddownload text="Download (Downloaded: %count%)"]]

The Default Text field in the Settings screen under the Shortcodes tab.
Settings Screen Default Text

------------------------- Shortcode styles ----------------------------------------------------------------
ddownload parameter: style=""
Choose the default list type for shortcode: ddownload_list in the admin dashboard:

	 	'infobox'		=> array(
	 	'singlepost'		=> array(
	 	'button'		=> array(
	 	'link'			=> array(
	 	'iconlink'			=> array(
	 	'plain_text'	=> array(

------------------------ Button colors ---------------------------
usage: after style="button" button="accent",  default is "grey"

'accent','black','blue','green','purple','grey','red','yellow'

----------------------- List styles ---------------------------------------------------------------------
ddownload_list parameter: style=""

	 	'title'				=> array(
	 	'title_date'		=> array(
	 	'title_count'		=> array(
	 	'title_filesize'	=> array(
	 	'title_ext_filesize'=> array(
	 	'title_date_ext_filesize'=> array(
	 	'title_ext_filesize_count'=> array(
	 	'icon_title_ext_filesize'=> array(
	 	'icon_title_ext_filesize_count_datesymbol'=> array(
	 	'infoboxlist'=> array(


-------------------- Custom templates ----------------------------
The following list styles are registered by default:
```
<?php
$lists = array(
 	'title' => array(
 		'name'	=> __( 'Title', 'delightful-downloads' ),
 		'format' => '<a href="%url%" title="%title%" rel="nofollow">%title%</a>'
 	),
 	'title_date' => array(
 		'name' => __( 'Title (Date)', 'delightful-downloads' ),
 		'format' => '<a href="%url%" title="%title% (%date%)" rel="nofollow">%title% (%date%)</a>'
 	),
 	'title_count' => array(
 		'name' => __( 'Title (Count)', 'delightful-downloads' ),
 		'format' => '<a href="%url%" title="%title% (Downloads: %count%)" rel="nofollow">%title% (Downloads: %count%)</a>'
 	),
 	'title_filesize' => array(
 		'name' => __( 'Title (Filesize)', 'delightful-downloads' ),
 		'format' => '<a href="%url%" title="%title% (%filesize%)" rel="nofollow">%title% (%filesize%)</a>'
 	)
);
```
To add a new list style to those already registered by Delightful Downloads simply add a new key to the $lists array:
```
<?php
function custom_list( $lists ) {
	$lists['icon_date'] = array(
 		'name' => 'Icon (Date)',
 		'format' => '<i class="fa fa-download"></i><a href="%url%" title="%title%" rel="nofollow">%title% - %date%</a>'
	);
	return $lists;
}
add_filter( 'dedo_get_lists', 'custom_list' );
```
replace list
```
<?php
function custom_list( $lists ) {
	$new_lists['icon_date'] = array(
 		'name' => 'Icon (Date)',
 		'format' => '<i class="fa fa-download"></i><a href="%url%" title="%title%" rel="nofollow">%title% - %date%</a>'
	);
	return $new_lists;
}
add_filter( 'dedo_get_lists', 'custom_list' );
```
Remove List
```
<?php
function custom_list( $lists ) {
	unset( $lists['title_filesize'] );
	return $lists;
}
add_filter( 'dedo_get_lists', 'custom_list' );
```

To add a new button to those already registered by Delightful Downloads simply add a new key to the $buttons array:
```
<?php
function dedo_custom_button( $buttons ) {
    $buttons['custom'] = array(
        'name' => __( 'Custom Button', 'delightful-downloads' ),
        'class' => 'button-custom'
    );
    return $buttons;
}
add_filter( 'dedo_get_buttons', 'dedo_custom_button' );
```
To completely replace the default buttons simply overwrite the $buttons array:
```
<?php
function dedo_custom_button( $buttons ) {
    $custom_buttons['bright_pink'] = array(
        'name' => __( 'Bright Pink', 'delightful-downloads' ),
        'class' => 'button-bright-pink'
    );
    return $custom_buttons;
}
add_filter( 'dedo_get_buttons', 'dedo_custom_button' );
```
Existing buttons can be removed by simply unsetting them. The following snippet will remove the red and yellow buttons:
```
<?php
function dedo_custom_button( $buttons ) {
    unset( $buttons['red'] );
    unset( $buttons['yellow'] );
    return $buttons;
}
add_filter( 'dedo_get_buttons', 'dedo_custom_button' );
```

The following output styles are registered by default:
```
<?php
$styles = array(
 	'button' => array(
 		'name' => __( 'Button', 'delightful-downloads' ),
 		'format' => '<a href="%url%" title="%text%" rel="nofollow" class="%class%">%text%</a>'
 	),
 	'link' => array(
 		'name' => __( 'Link', 'delightful-downloads' ),
 		'format' => '<a href="%url%" title="%text%" rel="nofollow" class="%class%">%text%</a>'
 	),
 	'plain_text' => array(
 		'name' => __( 'Plain Text', 'delightful-downloads' ),
 		'format' => '%url%'
 	)
);
```
To add a new output style to those already registered by Delightful Downloads simply add a new key to the $styles array:
```
<?php

function dedo_custom_output( $styles ) {
	$styles['icon_link'] = array(
 		'name' => __( 'Icon Link', 'delightful-downloads' ),
 		'format' => '<div class="download_container"><i class="fa fa-download"></i><a href="%url%" title="%title%" rel="nofollow">%title%</a></div>'
	);

	return $styles;
}
add_filter( 'dedo_get_styles', 'dedo_custom_output' );
```
To completely replace the default output styles simply overwrite the $styles array:
```
<?php
function dedo_custom_output( $styles ) {
	$new_styles['icon_link'] = array(
 		'name' => __( 'Icon Link', 'delightful-downloads' ),
 		'format' => '<div class="download_container"><i class="fa fa-download"></i><a href="%url%" title="%title%" rel="nofollow">%title%</a></div>'
	);
	return $new_styles;
}
add_filter( 'dedo_get_styles', 'dedo_custom_output' );
```
You use a custom output like so, [ddownload id="123" style="icon_link"].
Existing outputs can be removed by simply unsetting them. The following snippet will remove the plain text output:
```
<?php
function dedo_custom_output( $styles ) {
	unset( $styles['plain_text'] );
	return $styles;
}
add_filter( 'dedo_get_styles', 'dedo_custom_output' );
```

---------------------------- End of documentation ---------------------------------------------------

== Installation ==

1. Upload `delightful-downloads` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Downloads > Settings and configure the options.

Please refer to the [documentation] in this readme.txt.


==================== Changelog ==========================================================================

= 9.9.50 =
updated documentation. cleaned up styles, bugfixes, german translation extended, button in themes accent style added

= 9.9.46 =
%date% and %datesymbol% improved outputs

= 9.9.45 =
Widget removed as you can use shortcode ddownload_list as html block element in widget block areas
added some more styles
german translations updated

= 9.9.44 =
%tags% woldcard displays tags
added symbols to wildcards, (visible if fontawesome 4.7 is installed with theme) 

= 9.9.43 =
documentation in text form replaces pdf file
wp5.9.0 compatibility check

= 9.9.42 =
infobox datesymbol layout shows create and modified dates and agos on mouseover
new files yellow, date and time display homogenized (shows only mod date if present else creation date
catch error if download is created without adding a file (placeholder for file can be added later
downloadtime as wildcard and for infobox and list added. shows download time (200Mbit) and on mouseover 6 typical internet lines dl-time

= 9.9.40 =
rewrite time function ddago(
needs a theme with fontawesome 4.7 like penguin-mod or icons will not displayed

= 9.9.37 =
fixed human time diff preword that was only in german. changed to "ago" in german "her" after time diffs.

= 9.9.36 =
add password flag shortcode to download types

= 9.9.35 =
sort and search download logs, display and count onedaypass dls

= 9.9.34 =
Widget soriert nun auch nach modified date
german translations fixed and extended

= 9.9.33 =
fixed bug with editors blue dropzone preventing dedo uploads via drag n dropzone
wp 5.7.1 compatibility checked

= 9.9.32 =
improved styling in infobox and infoboxlist, image zoom, flexbox and first categorie display %category%

= 9.9.31 =
* Bugfix OnedayPass  Link - it worked even when next day passed. 
* added nextday pass in admin area. If you want to pass your customers the link for today and tomorrow you can pass him two links now 
and neeno need to reissue tomorrow

= 9.9.29 - 30 =
PHP 8 fixes and Wordpress 5.6 tests

= 9.9.28 =
* removing the file in download editor does not remove the file from the server but unlinks it from post
  - added the option in admin downloads list to physically delete file from server (deleted status is marked in list). 
  - added file exist check in list and in onedaypass download. 
* Download logs now show if it was downloaded using onedaypass or by regular download method (in column user agent)  
* added some german translations

= 9.9.26-27 =
* Download and List infobox - human time added to modified date
* Bugfixes and removed deprecated functions

= 9.9.25 =
Admin area modernized, documentation link bugfix

= 9.9.24 =
* changed some styles and defaults to infobox and button default color to grey button, list type to infoboxlist

= 9.9.20 =
* If chartscodes plugin (https://github.com/svenbolte/chartcodes) is installed, ip country flag and country will be displayed in logs

= 9.9.19 =
* IP-Anonymization in logfiles. Last digit will be nulled. This way it is possible to get the country but not the person which complies with german GDPR

= 9.9.18 =
* one day pass are unique per download now (user can download named file until 2400 hrs same day. Own column in admin panel for 1-day-pass-downloads
* German translation completed and added one day downloads translation

= 9.9.17 =
* added one day pass download links (copy to clipboard) in admin panel. File can be downloaded with this crypted link until end of same day

= 9.9.16 =
* Added german and german (formal) translations to language directory
* Security fixes for jQueryFileTree applied and tested. Root file system access is now blocked
* minor bug fixes
* removed some unneccessary stuff

= 9.9.13 = PBMod
* Quicklink column to copy download URL in Downloads admin panel can be enabled
* Wildcards: %icon% and %description% added < displays the file type icon from assets left to the rest
* Editor and comments (Add new download) enabled to allow to display single download on a custom page.
* ddownloads list: switched from list UL LI  to table
* Style 'infobox' added. This displays the download in a infobox with silver border with filetype icon on the left and all other relevant information and Destailes Description of the file

= 1.6.6 =
* New: MailChimp addon released
* Bug fix: Fix undefined error on settings save

= 1.6.5 =
* Improvement: Compatibility with upcoming MailChimp addon

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
