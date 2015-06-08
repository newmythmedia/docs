<?php

/**
 * Helper script to load and return CSS, JS and images from
 * theme folders.
 */

// Change this is the location needs to change.
$source_path = './src/';


$file   = ! empty($_GET['file']) ? $_GET['file'] : null;
$theme  = ! empty($_GET['theme']) ? $_GET['theme'] : null;

if (empty($file) || empty($theme))
{
	http_send_status(400);
	exit();
}

// Clean it up!
$file = preg_replace('/[^a-zA-A0-9-_\.]/', '', strtolower($file));
$theme = preg_replace('/[^a-zA-A0-9-_\.]/', '', strtolower($theme));


$path = $source_path ."themes/{$theme}/{$file}";

if (! file_exists($path))
{
	http_send_status(404);
	exit();
}

header("Content-type: text/css; charset: UTF-8");
readfile($path);