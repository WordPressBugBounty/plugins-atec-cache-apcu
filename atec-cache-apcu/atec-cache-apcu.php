<?php
if (!defined('ABSPATH')) { exit; }

/**
* Plugin Name:  atec Cache APCu
* Plugin URI: https://atecplugins.com/
* Description: Super fast APCu-Object-Cache and the only APCu based page-cache plugin available.
* Version: 2.1.87
* Requires at least:4.9
* Tested up to: 6.7
* Tested up to PHP: 8.4.2
* Requires PHP: 7.4
* Requires CP: 1.7
* Premium URI: https://atecplugins.com
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-apcu
*/

	// wp_cache_delete('alloptions','options');
	// wp_cache_delete('users');

if (str_contains(add_query_arg(null,null),'&flushWPCA')) wp_cache_flush();

function atec_wpca_settings($opt): bool { global $atec_wpca_settings; return (bool) filter_var($atec_wpca_settings[$opt]??0,258); }

$atec_wpca_apcu_enabled	= extension_loaded('apcu') && apcu_enabled();
$atec_wpca_settings 	= get_option('atec_WPCA_settings',[]);

wp_cache_set('atec_wpca_version','2.1.87');

if (is_admin()) 
{
	register_activation_hook(__FILE__, function() { require('includes/atec-wpca-activation.php'); });
	register_deactivation_hook(__FILE__, function() { require('includes/atec-wpca-deactivation.php'); });
	
	if (!function_exists('atec_plugin_settings')) require('includes/atec-admin.php');
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), [ATEC_Admin::class, 'plugin_settings'], 10, 2);
	
	if (!function_exists('atec_query')) require('includes/atec-init.php');
	
	(function() {

		global $atec_wpca_apcu_enabled; 
		$atec_wpca_ocache = atec_wpca_settings('ocache');
		$atec_wpca_pcache = atec_wpca_settings('cache');
		add_action('admin_menu', function() use ($atec_wpca_apcu_enabled,$atec_wpca_ocache,$atec_wpca_pcache)
		{ 			
			$oadmin = $atec_wpca_ocache && atec_wpca_settings('oadmin');
			$admin = $atec_wpca_pcache && atec_wpca_settings('admin');

			$error = $atec_wpca_apcu_enabled?'':esc_attr__('APCu extension required','atec-cache-apcu').'!';
			if ($error==='') $error = defined('WP_APCU_KEY_SALT')?'':esc_attr__('OC is not installed','atec-cache-apcu').'!';
			if (atec_wp_menu(__FILE__,'atec_wpca','<span '.($error===''?'':' title="'.$error.'"').'>Cache&nbsp;APCu'.($error===''?'':'❗').'</span>')!==false)
			{
				if ($atec_wpca_apcu_enabled)
				{ 						
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
					
						function atec_wpca_admin_bar_args($wp_admin_bar,$short,$nav,$type)
						{
							// @codingStandardsIgnoreStart | Image is not an attachement
							$args = array(
								'id' => 'atec_wpca_'.$short.'_admin_bar', 
								'title' => '<span title="'.__('Flush '.$short.'ache','atec-cache-apcu').'" style="font-size:12px;">
											<span class="ab-icon dashicons dashicons-trash"></span>
											<img src="'. plugins_url('/assets/img/atec_wpca_icon_admin.svg', __FILE__ ) .'" style="height:12px; vertical-align: bottom; margin:9px 4px 9px -7px;"> '.$short.
											'</span>',
								'href' => get_admin_url().'admin.php?page=atec_wpca&action=flush&type='.$type.'&nav='.$nav.'&_wpnonce='.esc_attr(wp_create_nonce('atec_wpca_nonce')) );
							// @codingStandardsIgnoreEnd
							return $args;
						}
						
						if ($oadmin)
						{
							function atec_wpca_oc_admin_bar($wp_admin_bar): void { $wp_admin_bar->add_node(atec_wpca_admin_bar_args($wp_admin_bar,'OC','Cache','WP_Ocache')); }
							add_action('admin_bar_menu', 'atec_wpca_oc_admin_bar', PHP_INT_MAX);
						}
						
						if ($admin)
						{
							function atec_wpca_pc_admin_bar($wp_admin_bar): void { $wp_admin_bar->add_node(atec_wpca_admin_bar_args($wp_admin_bar,'PC','Page_Cache','PCache')); }
							add_action('admin_bar_menu', 'atec_wpca_pc_admin_bar', PHP_INT_MAX);
						}
					}			
				}
				else
				{
					function atec_wpca_add_action_info($actions) { return array_merge($actions, array('<span style="color:red !important;">'.esc_attr__('APCu extension required','atec-cache-apcu').'!</span>')); }
					add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'atec_wpca_add_action_info' );
				}
								
				$atec_query = atec_query();
				// @codingStandardsIgnoreStart | This is not a FORM request, it is just a test, whether an options.php request is related to the plugin.
				if (preg_match('/atec_wpca$|atec_wpca&settings-updated|nav=Settings|&flushWPCA/', $atec_query)
				|| (str_contains($atec_query,'wp-admin/options.php') && isset($_POST['atec_WPCA_settings'])))		
				require('includes/atec-cache-apcu-register_settings.php'); 
				// @codingStandardsIgnoreEnd
			
			}
		});
		
		if ($atec_wpca_apcu_enabled)
		{
			if ($atec_wpca_ocache || $atec_wpca_pcache)
			{
				function atec_wpca_delete_tag_cache($term_id, $tt_id, $taxo): void { if ($taxo==='post_tag') atec_wpca_flush_actions('tag'); }
				function atec_wpca_flush_actions($args)
				{
					if (!function_exists('atec_wpca_delete_page_cache_all')) require(__DIR__.'/includes/atec-cache-apcu-pcache-tools.php');
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
			
				add_action('activated_plugin', function() { atec_wpca_flush_actions(['wp_cache','p_cache']); });
				add_action('deactivated_plugin', function() { atec_wpca_flush_actions(['wp_cache','p_cache']); });
				add_action('upgrader_pre_install', function() { atec_wpca_flush_actions(['wp_cache','p_cache']); });
			}
			
			if ($atec_wpca_pcache)
			{		
				add_action( 'after_switch_theme', function() { atec_wpca_flush_actions('all'); });
				add_action( 'wp_ajax_edit_theme_plugin_file', function() { atec_wpca_flush_actions('all'); });
			
				add_action('create_category', function() { atec_wpca_flush_actions('cat'); });
				add_action('delete_category', function() { atec_wpca_flush_actions('cat'); });
						
				add_action( 'created_term', 'atec_wpca_delete_tag_cache', 10, 3);
				add_action( 'delete_term', 'atec_wpca_delete_tag_cache', 10, 3);
			}
		}
					
	})();

	if (in_array($atec_active_slug=atec_get_slug(), ['atec_group','atec_wpca'])) require('includes/atec-wpca-install.php');
}
else // not is_admin
{
	if ($atec_wpca_apcu_enabled && !defined('WP_APCU_MU_PAGE_CACHE') && atec_wpca_settings('cache'))
		add_action('init', function() { require(__DIR__.'/includes/atec-cache-apcu-pcache.php'); }, -1);
}

if ($atec_wpca_apcu_enabled && atec_wpca_settings('cache')) { require(__DIR__.'/includes/atec-cache-apcu-pcache-cleanup.php'); }

if (defined('WP_APCU_KEY_SALT'))
{	
	if (!defined('ATEC_APCU_OC_VERSION') || ATEC_APCU_OC_VERSION!=='1.0.21')
	{
		require(__DIR__.'/includes/atec-wpca-set-object-cache.php'); 
		atec_wpca_set_object_cache(array('ocache'=>true));
	}

	if (function_exists('wp_cache_wpc_counts'))
	{
		function atec_wpca_oc_stats() 
		{
			$key = WP_APCU_KEY_SALT.':atec_wpca_oc_stats';
			$stats = apcu_fetch($key);
			$current = wp_cache_wpc_counts();
			if (!$stats) { $stats = $current; $stats['count']=1; $stats['ts']=time(); }
			else { $stats['count']++; $stats['hits']+=$current['hits']; $stats['misses']+=$current['misses']; $stats['sets']+=$current['sets']; }
			apcu_store($key, $stats);
		}
		register_shutdown_function('atec_wpca_oc_stats');
	}
}
?>