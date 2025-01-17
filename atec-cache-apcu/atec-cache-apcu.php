<?php
if (!defined('ABSPATH')) { exit; }

/**
* Plugin Name:  atec Cache APCu
* Plugin URI: https://atecplugins.com/
* Description: APCu Object-Cache and the only APCu based page-cache plugin available.
* Version: 2.1.32
* Requires at least: 5.2
* Tested up to: 6.7.1
* Tested up to PHP: 8.4.1
* Requires PHP: 7.4
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-apcu
*/

wp_cache_set('atec_wpca_version','2.1.32');

$atec_wpca_apcu_enabled=extension_loaded('apcu') && apcu_enabled();
$atec_wpca_settings=get_option('atec_WPCA_settings',[]);

function atec_wpca_settings($opt): bool { global $atec_wpca_settings; return $atec_wpca_settings[$opt]??null==true; }

if (is_admin()) 
{
	register_activation_hook(__FILE__, function() { @require_once('includes/atec-wpca-activation.php'); });
	register_deactivation_hook(__FILE__, function() { @require_once('includes/atec-wpca-deactivation.php'); });
	
	if (!defined('ATEC_ADMIN_INC')) @require_once(__DIR__.'/includes/atec-admin.php');
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'atec_plugin_settings' );
	
	if (!defined('ATEC_INIT_INC')) @require_once('includes/atec-init.php');
	add_action('admin_menu', function() 
	{ 
		$error='';
		if (!defined('WP_APCU_KEY_SALT')) $error=esc_attr__('OC is not installed','atec-cache-apcu');
		if ($error==='') { global $atec_wpca_apcu_enabled; if (!$atec_wpca_apcu_enabled) $error=esc_attr__('APCu extension required','atec-cache-apcu').'!'; }
		atec_wp_menu(__FILE__,'atec_wpca',$error===''?'Cache APCu':'<span title="'.$error.'">Cache APCu ❗</span>'); 
	});
	
	global $atec_active_slug;
	if (in_array($atec_active_slug=atec_get_slug(), ['atec_group','atec_wpca'])) @require_once(__DIR__.'/includes/atec-wpca-install.php');

	if ($atec_wpca_apcu_enabled && defined('WP_APCU_KEY_SALT'))
	{ 			
		function atec_wpca_admin_footer_function($content): string
		{
			// @codingStandardsIgnoreStart
			// Image is not an attachement
			$yes = 'dashicons dashicons-yes-alt';
			$style = 'padding-top: 5px; font-size: 16px; color:green;';
			$content.=' | 
			<sub>
				<img alt="Plugin icon" src="'.esc_url(plugin_dir_url(__FILE__).'assets/img/atec-group/atec_wpca_icon.svg').'" style="height: 20px; vertical-align: bottom;"> 
				APCu OCache <span style="'.esc_html($style).'" class="'.esc_attr($yes).'"></span>';
			// @codingStandardsIgnoreEnd
			if (atec_wpca_settings('cache')) $content.=' APCu PCache <span style="'.esc_html($style).'" class="'.esc_attr($yes).'"></span>';
			$content.='</sub>';
			return $content; 
		}
		add_action('admin_footer_text', 'atec_wpca_admin_footer_function');
		
		if (atec_wpca_settings('cache') && atec_wpca_settings('admin'))
		{
			function atec_wpca_admin_bar($wp_admin_bar): void
			{
				// @codingStandardsIgnoreStart
				// Image is not an attachement
				$args = array(
					'id' => 'atec_wpca_admin_bar', 
					'title' => '<span title="'.__('Flush PCache','atec-cache-apcu').'" style="font-size:12px;">
									<img src="'. plugins_url( '/assets/img/atec_wpca_icon_admin.svg', __FILE__ ) .'" 
									style="height:14px; vertical-align: bottom; margin:9px 4px 9px 0;">Flush
								</span>', 
					'href' => get_admin_url().'admin.php?page=atec_wpca&flush=APCu_PCache&nav=Cache&_wpnonce='.esc_attr(wp_create_nonce('atec_wpca_nonce')) );
				// @codingStandardsIgnoreEnd
				$wp_admin_bar->add_node($args);
			}
			add_action('admin_bar_menu', 'atec_wpca_admin_bar', PHP_INT_MAX);	
		}
	}
	
	if (!$atec_wpca_apcu_enabled)
	{
		function atec_wpca_add_action_info($actions) 
		{
			$links = array('<span style="color:red !important;">'.esc_attr__('APCu extension required','atec-cache-apcu').'!</span>');
			return array_merge( $actions, $links );
		}
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'atec_wpca_add_action_info' );
	}

	add_action('init', function() 
	{ 
		if (preg_match('/page=atec_wpca|options\.php/', atec_query())) { @require_once(__DIR__.'/includes/atec-cache-apcu-register_settings.php'); }

		if (atec_wpca_settings('clear'))
		{
			require_once(__DIR__.'/includes/atec-cache-apcu-pcache-tools.php');
			
			add_action( 'after_switch_theme', 'atec_wpca_delete_page_cache_all');
			add_action( 'activated_plugin', 'atec_wpca_delete_wp_cache');
			add_action( 'deactivated_plugin', 'atec_wpca_delete_wp_cache');
			
			
			add_action( 'wp_ajax_edit_theme_plugin_file', 'atec_wpca_delete_page_cache_all');				

			add_action('create_category', 'atec_wpca_delete_category_cache');
			add_action('delete_category', 'atec_wpca_delete_category_cache');
						
			add_action( 'created_term', 'atec_wpca_delete_tag_cache', 10, 3);
			add_action( 'delete_term', 'atec_wpca_delete_tag_cache', 10, 3);
		}
	});
 }
else // not is_admin
{
	if (!defined('WP_APCU_MU_PAGE_CACHE') && $atec_wpca_apcu_enabled && atec_wpca_settings('cache'))
	add_action('init', function() { @require_once(__DIR__.'/includes/atec-cache-apcu-pcache.php'); });
}

if ($atec_wpca_apcu_enabled && atec_wpca_settings('cache')) { @require_once(__DIR__.'/includes/atec-cache-apcu-pcache-cleanup.php'); }
?>