<?php

$file = 'BaseHelper.php';
$dir = __DIR__;
$dir_from = $dir.'\\Helper';
$dir_base = str_replace('\vendor\rguj\laracore\src', '', $dir);
$dir_to = $dir_base.'\app\Helper';
$file_from = $dir_from.'\\'.$file;
$file_to = $dir_to.'\\'.$file;

if(!file_exists($dir_to)) {
	mkdir($dir_to);
}

if(!copy($file_from, $file_to))
	throw new exception('Copy failed');

echo 'BaseHelper.php copied successfully!'."\n";

