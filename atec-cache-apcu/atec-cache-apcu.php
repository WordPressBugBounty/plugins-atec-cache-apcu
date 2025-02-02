<?php
if (!defined('ABSPATH')) { exit; }

/**
* Plugin Name:  atec Cache APCu
* Plugin URI: https://atecplugins.com/
* Description: APCu Object-Cache and the only APCu based page-cache plugin available.
* Version: 2.1.49
* Requires at least: 4.9.8
* Tested up to: 6.7.1
* Tested up to PHP: 8.4.2
* Requires PHP: 7.4
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-apcu
*/

wp_cache_set('atec_wpca_version','2.1.49');

$atec_wpca_apcu_enabled=extension_loaded('apcu') && apcu_enabled();
$atec_wpca_settings=get_option('atec_WPCA_settings',[]);

function atec_wpca_settings($opt): bool { global $atec_wpca_settings; return $atec_wpca_settings[$opt]??null==true; }

if (is_admin()) 
{
	register_activation_hook(__FILE__, function() { @require('includes/atec-wpca-activation.php'); });
	register_deactivation_hook(__FILE__, function() { @require('includes/atec-wpca-deactivation.php'); });
	
	if (!function_exists('atec_plugin_settings')) @require('includes/atec-admin.php');
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'atec_plugin_settings', 10, 2);
	
	if (!function_exists('atec_query')) @require('includes/atec-init.php');
	add_action('admin_menu', function() 
	{ 
		$error='';
		if (!defined('WP_APCU_KEY_SALT')) $error=esc_attr__('OC is not installed','atec-cache-apcu');
		if ($error==='') { global $atec_wpca_apcu_enabled; if (!$atec_wpca_apcu_enabled) $error=esc_attr__('APCu extension required','atec-cache-apcu').'!'; }
		atec_wp_menu(__FILE__,'atec_wpca',$error===''?'Cache APCu':'<span title="'.$error.'">Cache APCu ❗</span>'); 
	});
	
	global $atec_active_slug;
	if (in_array($atec_active_slug=atec_get_slug(), ['atec_group','atec_wpca'])) @require('includes/atec-wpca-install.php');

	if ($atec_wpca_apcu_enabled)
	{ 		
		$oadmin = atec_wpca_settings('ocache') && atec_wpca_settings('oadmin');
		$admin = atec_wpca_settings('cache') && atec_wpca_settings('admin');
			
		if ($oadmin || $admin)
		{
			function atec_wpca_admin_footer_function($content): string
			{
				// @codingStandardsIgnoreStart | Image is not an attachement
				$yes = 'dashicons dashicons-yes-alt';
				$style = 'padding-top: 5px; font-size: 16px; color:green;';
				$content.=' | 
				<sub>
					<img alt="Plugin icon" src="'.esc_url(plugin_dir_url(__FILE__).'assets/img/atec-group/atec_wpca_icon.svg').'" style="height: 20px; vertical-align: bottom;">';
					if (atec_wpca_settings('ocache')) $content.=' APCu OCache <span style="'.esc_html($style).'" class="'.esc_attr($yes).'"></span>';
					if (atec_wpca_settings('cache')) $content.=' APCu PCache <span style="'.esc_html($style).'" class="'.esc_attr($yes).'"></span>';
				$content.='</sub>';
				return $content; 
				// @codingStandardsIgnoreEnd
			}
			add_action('admin_footer_text', 'atec_wpca_admin_footer_function');
		
			function atec_wpca_admin_bar_args($wp_admin_bar,$type,$nav,$action)
			{
				// @codingStandardsIgnoreStart | Image is not an attachement
				$args = array(
					'id' => 'atec_wpca_'.$type.'_admin_bar', 
					'title' => '<span title="'.__('Flush '.$type.'ache','atec-cache-apcu').'" style="font-size:12px;">
								<span class="ab-icon dashicons dashicons-trash"></span>
								<img src="'. plugins_url('/assets/img/atec_wpca_icon_admin.svg', __FILE__ ) .'" style="height:12px; vertical-align: bottom; margin:9px 4px 9px -7px;"> '.$type.
								'</span>',
					'href' => get_admin_url().'admin.php?page=atec_wpca&action='.$action.'&nav='.$nav.'&_wpnonce='.esc_attr(wp_create_nonce('atec_wpca_nonce')) );
				// @codingStandardsIgnoreEnd
				return $args;
			}
			
			if ($oadmin)
			{
				function atec_wpca_oc_admin_bar($wp_admin_bar): void 	{ $wp_admin_bar->add_node(atec_wpca_admin_bar_args($wp_admin_bar,'OC','Cache','WP_Ocache')); }
				add_action('admin_bar_menu', 'atec_wpca_oc_admin_bar', PHP_INT_MAX);
			}
			
			if ($admin)
			{
				function atec_wpca_pc_admin_bar($wp_admin_bar): void { $wp_admin_bar->add_node(atec_wpca_admin_bar_args($wp_admin_bar,'PC','Page_Cache','deleteAll')); }
				add_action('admin_bar_menu', 'atec_wpca_pc_admin_bar', PHP_INT_MAX);
			}
		}
	}
	else
	{
		function atec_wpca_add_action_info($actions) 
		{
			$links = array('<span style="color:red !important;">'.esc_attr__('APCu extension required','atec-cache-apcu').'!</span>');
			return array_merge( $actions, $links );
		}
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'atec_wpca_add_action_info' );
	}

	$atec_query = atec_query();
	// @codingStandardsIgnoreStart
	// This is not a FORM request, it is just a test, whether an options.php request is related to the plugin, thus register-settings must be loaded or otherwise can be skipped
	if (preg_match('/atec_wpca$|atec_wpca&settings-updated|atec_wpca&nav=Settings/', $atec_query)
	|| (str_contains($atec_query,'wp-admin/options.php') && isset($_POST['atec_WPCA_settings'])))		
	@require('includes/atec-cache-apcu-register_settings.php'); 
	// @codingStandardsIgnoreEnd

	add_action('init', function() 
	{ 
		function atec_wpca_delete_tag_cache($term_id, $tt_id, $taxo): void { if ($taxo==='post_tag') atec_wpca_flush_actions('tag'); }
		function atec_wpca_flush_actions($args)
		{
			if (!function_exists('atec_wpca_delete_wp_cache')) @require(__DIR__.'/includes/atec-cache-apcu-pcache-tools.php');
			foreach((array) $args as $arg)
			{
				switch ($arg)
				{
					case 'wp_cache': if (atec_wpca_settings('ocache')) atec_wpca_delete_wp_cache(); break;
					case 'p_cache': if (atec_wpca_settings('cache')) atec_wpca_delete_page_cache_all(); break;			
					case 'all': atec_wpca_delete_page_cache_all(); break;
					case 'cat': atec_wpca_delete_page_cache('','[cf|c]+'); break;
					case 'tag': atec_wpca_delete_page_cache('','[tf|f]+'); break;
					default: break;					
				}
			}		
		}

		if (atec_wpca_settings('ocache') || atec_wpca_settings('cache'))
		{
			add_action('activated_plugin', function() { atec_wpca_flush_actions(['wp_cache','p_cache']); });
			add_action('deactivated_plugin', function() { atec_wpca_flush_actions(['wp_cache','p_cache']); });
			add_action('upgrader_pre_install', function() { atec_wpca_flush_actions(['wp_cache','p_cache']); });
		}
		
		if (atec_wpca_settings('cache'))
		{		
			add_action( 'after_switch_theme', function() { atec_wpca_flush_actions('all'); });
			add_action( 'wp_ajax_edit_theme_plugin_file', function() { atec_wpca_flush_actions('all'); });

			add_action('create_category', function() { atec_wpca_flush_actions('cat'); });
			add_action('delete_category', function() { atec_wpca_flush_actions('cat'); });
					
			add_action( 'created_term', 'atec_wpca_delete_tag_cache', 10, 3);
			add_action( 'delete_term', 'atec_wpca_delete_tag_cache', 10, 3);
		}
	});
 }
else // not is_admin
{
	if (!defined('WP_APCU_MU_PAGE_CACHE') && $atec_wpca_apcu_enabled && atec_wpca_settings('cache'))
	add_action('init', function() { @require(__DIR__.'/includes/atec-cache-apcu-pcache.php'); });
}

if ($atec_wpca_apcu_enabled && atec_wpca_settings('cache')) { @require(__DIR__.'/includes/atec-cache-apcu-pcache-cleanup.php'); }
?>