<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_INIT_INC',true);

function atec_query() { return add_query_arg(null,null); }

function atec_version_compare($a, $b) { return explode(".", $a) <=> explode(".", $b); }

function atec_fixit($dir,$p,$slug,$option=null)
{
	$optName 	= 'atec_fix_it';
	if (!$option) $option = get_option($optName,[]);
	$verName	= 'atec_'.$slug.'_version';
	$ver = wp_cache_get($verName);
	if (atec_version_compare($option[$p]??0,$ver)===-1)
	{ 
		@require($dir.'/fixit.php'); 
		$option[$p]=$ver; 
		update_option($optName,$option); 	
	}
};

function atec_nonce(): string { return atec_get_slug().'_nonce'; }
function atec_get_slug(): string { preg_match('/\?page=([\w_]+)/', atec_query(), $match); return $match[1] ?? ''; }
function atec_get_plugin($dir): string { $plugin=plugin_basename($dir); return substr($plugin,0,strpos($plugin,DIRECTORY_SEPARATOR)); }
function atec_group_page($dir): void { if (!class_exists('ATEC_group')) @require(plugin_dir_path($dir).'includes/atec-group.php'); } 

function atec_wp_menu($dir,$menu_slug,$title,$single=false,$cb=null): void
{ 
	if ($cb==null) { $cb=$menu_slug; }

	$pluginUrl=plugin_dir_url($dir);
	$icon=$pluginUrl . 'assets/img/'.$menu_slug.'_icon_admin.svg';

	if ($single) { add_menu_page($title, $title, 'administrator', $menu_slug, $cb , $icon); }
	else
	{
		global $atec_plugin_group_active;
		$group_slug='atec_group'; 
		
		if (!$atec_plugin_group_active)
		{
			add_menu_page('atec-systems','atec-systems', 'administrator', $group_slug, function() use ($dir) { atec_group_page($dir); }, $pluginUrl . 'assets/img/atec-group/atec_wpa_icon.svg');	
			add_submenu_page($group_slug,'Group', '<span style="width:20px; color:white;" class="dashicons dashicons-sos"></span>&nbsp;Dashboard', 'administrator', $group_slug, function() use ($dir) { atec_group_page($dir); } );
			$atec_plugin_group_active=true;
		}
		// @codingStandardsIgnoreStart
		// Image is not an attachement
		add_submenu_page($group_slug, $title, '<img src="'.esc_url($icon).'">&nbsp;'.$title, 'administrator', $menu_slug, $cb );
		// @codingStandardsIgnoreEnd
	}
}

function atec_admin_debug($name,$slug): void
{
	$slug='atec_'.$slug.'_debug'; $notice=get_option($slug);
	$name=$name==='Mega Cache'?$name:'atec '.$name;
	if ($notice) { atec_admin_notice($notice['type']??'info',$name.': '.$notice['message']??''); delete_option($slug); }
}

function atec_admin_notice($type,$message,$hide=false): void 
{ 
	$hash=$hide?md5($message):'';
	echo '<div ', ($hide?'id="'.esc_attr($hash).'" ':''), 'class="notice notice-',esc_attr($type),' is-dismissible"><p>',esc_attr($message),'</p></div>'; 
	if ($hide) atec_reg_inline_script('admin_notice', 'setTimeout(()=> { jQuery("#'.esc_attr($hash).'").slideUp(); }, 10000);', true);
}
function atec_new_admin_notice($type,$message): void { add_action('admin_notices', function() use ( $type, $message ) { atec_admin_notice($type,$message); }); }
?>