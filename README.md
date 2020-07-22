## Why this Version (my special Air Fork of it ;))

DEDO is blocked on wordpress plugins directory because of a security vulnerability in jqueryfiletree.php. Latter was already closed, so i integrated the fixes in this plugin.
New features from some of the other forks are quite useful, so i integrated them too in this forks
New File type icons (taken from an open source project and added some types like office and visio xml types, 7Zip and more
Custom file type extended: Description can be given and stored in database, Categories and tags added
language packs german and german formal added.
Documentation updated and put in the project in text form (/documentation.txt)

## Documentation ##

The Delightful Downloads documentation can be found at the root folder of this plugin for further instructions (documentation.txt and documentation-styles.pdf)


## Features ##

* crypted Link for one day pass download of a file copy in admin area and share. File Can be downloaded until end of same day
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


## Bugs

If you find a bug, please raise it [here](https://github.com/svenbolte/delightful-downloads/issues).

## Contributions

Anyone is welcome to contribute to Delightful Downloads. There are various ways to do so:

* Report a bug on GitHub
* Send a Pull Request with your bug fixes and/or new features
* [Translate] as transifex did not work for me I rather like to have xlations at globepress (wordpress l18n repository). Added translations there and put them locally to the project
* Provide feedback and suggestions on enhancements

## Development

Development should be performed on the __develop__ branch. After cloning the __develop__ branch to your local machine you should run:

`npm install`

This will build the required CSS files.

