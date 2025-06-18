<?php
namespace ATEC;
defined('ABSPATH') || exit;

final class TRANSLATE
{
	public static function load_pll($file, $slug, $domain= '')
	{
		$domain= 'atec-'.$slug;
		$mo_file = plugin_dir_path($file) . 'languages/'.$domain.'-' . str_replace('_formal', '',get_locale()) . '.mo';
		load_textdomain( $domain, $mo_file );
	}

}
?>