<?php
defined('ABSPATH') || exit;

if (defined('ATEC_PC_KEY_SALT')) return;

(function()
{
	$unique_base = (defined('AUTH_KEY') ? AUTH_KEY : '')."\0".(defined('DB_NAME') ? DB_NAME : '');
	$unique_key = $unique_base!=="\0" ? $unique_base : (string) get_option('blogname');
	define('ATEC_PC_KEY_SALT', hash('crc32', "pc\0".$unique_key, false));
})();