<?php

$version_checks = array(
	'delightful-downloads.php' => array(
		'@Version:\s+(.*)\n@'                              => 'header',
		'@define\(\s+\'DEDO_VERSION\',\s+\'(.*?)\'\s+\);@' => 'constant',
	),
);