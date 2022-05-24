<?php

/*
	If you use the standard laravel directory structure, please add .htaccess to the root and add this code:
	
		<FilesMatch "^\.">
			Require all denied
		</FilesMatch>
		<IfModule mod_rewrite.c>
			<IfModule mod_negotiation.c>
				Options -MultiViews -Indexes
			</IfModule>
			RewriteEngine on			
			#ErrorDocument 404 "%{REQUEST_FILENAME}public<br>%{REQUEST_URI}"
			#RewriteRule ^ - [L,R=404]			
			RewriteCond %{REQUEST_URI} !^/public/
			RewriteRule ^(.*)$ /public/$1 [NC,L,QSA]			
		</IfModule>
	
*/

// SETTINGS
$path_index = 0;
$paths = [  // htdocs (xampp) or www/html (ubuntu)
	'C:\\xampp\\htdocs\\',  // windows xampp
	'/var/www/html/',       // ubuntu
];

$ic = isset($index_called) && is_bool($index_called) && $index_called;

// CHECK ROOT DIRECTORY PATH
$proj_root_path = $paths[$path_index];
if(!str_starts_with(__DIR__, $proj_root_path)) {
	die('The project path does not match on the OS root path.');  
}

// REMOVE PUBLIC PATHS FOR LIVE URL
function has_index_uri(string $uri) {
	$arr1 = [
		'/public/index.php',
		'/public',
		'/index.php',
	];
	$opt = [false, $uri];
	foreach($arr1 as $k=>$v) {
		if(str_starts_with($uri, $v)) {
			$opt = [true, substr($uri, strlen($v))];
			break;
		}
	}
	return $opt;
}
$has_index_uri = has_index_uri($_SERVER['REQUEST_URI']);
if($ic && $has_index_uri[0]) {
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
	header('Location: '.$protocol.$_SERVER['HTTP_HOST'].$has_index_uri[1]);
	exit;
}

if(!$ic) {
	die('Check is good.');
}

