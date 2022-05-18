<?php
$dir = str_replace('\vendor\rguj\laracore\src', '', __DIR__) . '\app\Helper';
$file = $dir . '\BaseHelper.php';

if(!file_exists($dir))
	throw new exception('Target directory doesn\'t exists!');

if(!copy('./Helper/BaseHelper.php', $file))
	throw new exception('Copy failed');

echo 'BaseHelper.php copied successfully!';

