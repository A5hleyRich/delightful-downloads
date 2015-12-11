<?php

$version_checks = array(
	'delightful-downloads.php' => array(
		'@Version:\s+(.*?)\n@'           => 'header',
		'@\$version\s+\=\s+\'(.*?)\'\;@' => 'variable',
	),
);