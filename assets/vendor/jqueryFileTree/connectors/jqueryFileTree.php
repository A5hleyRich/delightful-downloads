<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.1.1
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Modified by Ashley Rich (http://www.ashleyrich.com)
// Do not show hidden unix files.
//
//
// History:
//
// 1.1.1 - SECURITY: forcing root to prevent users from determining system's file structure (per DaveBrad)
// 1.1.0 - adding multiSelect (checkbox) support (08/22/2014)
// 1.0.2 - fixes undefined 'dir' error - by itsyash (06/09/2014)// 1.02 - implemented security fix
// 1.0.1 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.0.0 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

header('Access-Control-Allow-Origin: *');


/**
 * filesystem root - USER needs to set this!
 * -> prevents debug users from exploring system's directory structure
 * ex: $root = $_SERVER['DOCUMENT_ROOT'];
 */
//$root = null;
$root = $_SERVER['DOCUMENT_ROOT'];
if( !$root ) exit("ERROR: Root filesystem directory not set in jqueryFileTree.php");

$postDir = rawurldecode($root.(isset($_POST['dir']) ? $_POST['dir'] = urldecode($_POST['dir']) : null ));
//  $_POST['dir'] = urldecode($_POST['dir']);

if( file_exists($_POST['dir']) ) {
	$files = scandir($_POST['dir']);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($_POST['dir'] . $file) && strpos($file, '.') !== 0 ) {
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($_POST['dir'] . $file) && strpos($file, '.') !== 0 ) {
				$ext = preg_replace('/^.*\./', '', $file);
				echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
			}
		}
		echo "</ul>";	
	}
}

?>