<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$start_time = microtime(true);

require 'vendor/autoload.php';

$config_loc = "./docs.json";

//--------------------------------------------------------------------
// Get things ready to go
//--------------------------------------------------------------------

$config = file_get_contents($config_loc);

if (empty($config))
{
	die('Invalid Configuration file found.');
}

$config = json_decode($config);

$builder = new Myth\Docs\Builder($config);

//--------------------------------------------------------------------
// Display the chosen page
//--------------------------------------------------------------------

$path = ! empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PATH_INFO'];

$output = $builder->buildPage($path);

if (empty($output))
{
	echo "Dang. Nothing to show.";
}

// Display our stats in the view
$end_time = microtime(true);
$elapsed_time = number_format($end_time - $start_time, 5);
$memory = round(memory_get_usage() / 1024 / 1024, 2).'MB';

$output = str_replace('{elapsed_time}', $elapsed_time, $output);
$output = str_replace('{memory_usage}', $memory, $output);

// Show our hard work.
echo $output;
