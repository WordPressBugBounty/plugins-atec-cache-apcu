<?php
if (!defined('ABSPATH')) { exit(); }
define('ATEC_INIT_INC',true);	// downward comp. Feb 25, remove later

function atec_query() { return add_query_arg(null,null); }
function atec_nonce(): string { return atec_get_slug().'_nonce'; }
function atec_get_slug(): string { preg_match('/\?page=([\w_]+)/', atec_query(), $match); return $match[1] ?? ''; }
function atec_group_page($dir): void { if (!class_exists('ATEC_group')) @require(plugin_dir_path($dir).'includes/atec-group.php'); } 

function atec_wp_menu($dir,$menu_slug,$title,$single=false,$cb=null)
{ 
	global $atec_cuc_cache;
	if (empty($atec_cuc_cache))
	{
		$atec_cuc_cache=[];
		$atec_cuc_cache['edit_posts']=current_user_can('edit_posts');
		$atec_cuc_cache['edit_pages']=current_user_can('edit_pages');
		$atec_cuc_cache['manage_options']=current_user_can('manage_options');		
	}
	
	if (in_array($menu_slug,['atec_wpc','atec_wpdpp','atec_wpm','atec_wppo'])) { if (!($atec_cuc_cache['edit_posts'] || $atec_cuc_cache['edit_pages'])) return false; }
	elseif (!$atec_cuc_cache['manage_options']) return false;
	
	if ($cb==null) { $cb=$menu_slug; }

	$pluginUrl=plugin_dir_url($dir);
	$icon=$pluginUrl . 'assets/img/'.$menu_slug.'_icon_admin.svg';

	if ($single || $menu_slug==='atec_wpmc') { add_menu_page($title, $title, 'administrator', $menu_slug, $cb , $icon); }
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
		// @codingStandardsIgnoreStart | Image is not an attachement
		add_submenu_page($group_slug, $title, '<img src="'.esc_url($icon).'">&nbsp;'.$title, 'administrator', $menu_slug, $cb );
		// @codingStandardsIgnoreEnd
	}
	return true;
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