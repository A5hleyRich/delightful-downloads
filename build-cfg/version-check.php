<?php
$version_checks = array(
	'delightful-downloads.php' => array(
		'@Version:\s+(.*)\n@'                              => 'header',
		'@define\(\s+\'DEDO_VERSION\',\s+\'(.*?)\'\s+\);@' => 'constant',
	),
	'Gruntfile.js'             => array(
		'@package_version:\s+\'(.*)\'@' => 'Gruntfile.js',
	),
	'package.json'             => array(
		'@\"version\":\s+\"(.*)\",@' => 'package.json',
	),
);