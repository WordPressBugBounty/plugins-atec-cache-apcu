<?php
/**
 * ATEC_INIT
 *
 * Static UI helper class for atec Plugins.
 * Handles menu registration, admin notices, and legacy wrappers.
 * Used across all plugins in admin context only.
 */
namespace ATEC;
defined('ABSPATH') || exit;

// ===== Static Toolbox Class =====
final class INIT {

static $require_install = [ 'wpau', 'wpc', 'wpca', 'wpcm', 'wpco', 'wpcr', 'wpd', 'wpds', 'wpf', 'wpfm', 'wpm', 'wppp', 'wps', 'wpsi', 'wpsh', 'wpwp' ];
static $skip_load_check = ['wp4t', 'wpau', 'wpds', 'wpht', 'wpdpp', 'wpll', 'wplu', 'wpmsc', 'wpocb', 'wppp', 'wps', 'wpsi', 'wpsmc', 'wpsr', 'wpsv', 'wpu'];
static $admin_styles_loaded = false;
static $allowed_admin_tags = 
	[	
		'svg' => [ 'class' => true, 'viewBox' => true, 'fill' => true, 'xmlns' => true, 'width' => true, 'height' => true, 'preserveAspectRatio' => true ],
		'g' => [ 'stroke' => true, 'fill' => true, 'transform' => true ],
		'path' => [ 'd' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ],
		'circle' => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
		'div' => [ 'class' => true, 'style' => true ],
		'span' => [ 'class' => true, 'style' => true ],
	];

/* ADMIN */	

public static function admin_bar($wp_admin_bar, $slug= '', $action = null, $nav=null, $id= '', $title= '', $icon=false): void
{
	$id .= $id !== '' ? '_' : '';
	$dash = '';
	if ($icon)
	{
		if (str_starts_with($icon, '#')) $dash = '<span class="ab-icon dashicons-' . str_replace('#', '', $icon) . '" style="margin:0;"></span>';
		else $icon = \ATEC\SVG::plain($slug);
	}
	else $icon = '';
	
	if (str_contains($title, '#'))	// has alt. title 
	{
		$ex = explode('#', $title);
		$title = $ex[0];
		$alt = $ex[1];
	}
	else $alt = '';
		
	$args = 
		[
			'id'	=> 'atec_'.$slug.'_'.$id.'admin_bar',
			'meta'  => $alt !== '' ? [ 'title' => $alt ] : [],
			'title'	=> wp_kses('<div class="atec-admin-bar-row">'. $icon . $dash . $title. '</div>', self::$allowed_admin_tags)
		];
	if ($slug!== '') 
	{
		$args['href'] = esc_url((empty($action) && empty($nav)) ? self::admin_url($slug) : self::build_url($slug, $action, $nav));
	}
	$wp_admin_bar->add_node($args);
}

public static function build_url($una_or_slug, $action = null, $nav = null, $args = []): string
{
	if (is_string($una_or_slug))	// is_string is a slug
	{
		$menu_slug = 'atec_'.$una_or_slug;
		$una = (object)
		array(
			'slug' => $una_or_slug,
			'url' => self::admin_url($una_or_slug),
			'nonce' => wp_create_nonce($menu_slug.'_nonce'),
		);
	}
	else $una = $una_or_slug;

	$nav = $nav ?? ($una->nav ?? '');

	if ($action!==null && str_contains($action, '&')) 	// If $actions is a combined string
	{ parse_str($action, $temp); $action = array_key_first($temp); $args = array_merge($temp, $args); }

	$arr = array_merge([ 'action' => $action, 'nav' => $nav, '_wpnonce' => $una->nonce ], $args);
	$arr = array_filter($arr, fn($v) => $v !== null && $v !== '');

	$query_str = http_build_query($arr);
	$sep = str_contains($una->url, '?') ? '&' : '?';
	return $una->url . $sep . $query_str;
}

public static function site_url(): string
{
	static $cached = null;
	if ($cached === null) $cached = get_site_url();
	return $cached;
}

public static function site_host(): string
{
	static $cached = null;
	if ($cached === null) $cached = wp_parse_url(self::site_url(),PHP_URL_HOST);
	return $cached;
}

public static function admin_url($slug=''): string
{
	static $cached = null;
	if ($cached === null) $cached = self::site_url().'/wp-admin';
	return $cached . ($slug!=='' ? '/admin.php?page=atec_'.$slug : '');
}

public static function admin_bar_option($slug): int
{
	$option_key = 'atec_admin_bar';
	$options  = get_option($option_key);
	return $options[$slug] ?? 0;
}

public static function set_admin_bar_option($slug): void
{
	$option_key = 'atec_admin_bar';
	$options = (array) get_option($option_key,[]);
	
	$set = \ATEC\TOOLS::clean_request('set');
	if ($set === '') unset($options[$slug]);
	else $options[$slug] = self::bool($set);
	update_option($option_key, $options);
	wp_safe_redirect(self::admin_url($slug));
	exit;
}

public static function add_plugin_settings($plugin_file)
{
	add_filter('plugin_action_links_'.plugin_basename($plugin_file), [self::class, 'plugin_settings'], 10, 2);
}

public static function plugin_settings(array $links, $plugin_file): array
{
	$slug = \ATEC\GROUP::slug_by_dir($plugin_file);
	$url = self::admin_url($slug);
	$icon = \ATEC\SVG::styled('wrench', 14);
	array_unshift($links, '<a href="' . esc_url($url) . '" style="vertical-align:sub">' . $icon . '</a>');
	return $links;
}

public static function is_settings_updated(): bool
{
	return isset($_GET['settings-updated']) && $_GET['settings-updated']==true;	// phpcs:ignore
}

/* TOOLS */

public static function error_log($args)
{
	is_scalar($args) ? error_log($args) : error_log(print_r($args,true));		// phpcs:ignore
}

public static function POST($key, $default = '')
{
	if (!isset($_POST[$key])) return $default;									// phpcs:ignore
	return sanitize_text_field(wp_unslash($_POST[$key]));	// phpcs:ignore
}

public static function bool($value): bool { return filter_var($value, FILTER_VALIDATE_BOOLEAN); }

public static function nonce(): string { return self::slug() . '_nonce'; } // build nonce name from current slug

public static function slug(): string
{
	static $cached = null;
	if ($cached === null)
	{
		$query = self::query();
		$pos = strpos($query, '?page=');
		if ($pos !== false) 
		{
			$start = $pos + 6;
			$end = strcspn($query, '&', $start);
			$cached = substr($query, $start, $end);
		}
		else
		{
			return '';
		}
	}
	return $cached;
}

public static function query(): string
{
	static $cached = null;
	if ($cached === null) $cached = $_SERVER['REQUEST_URI'] ?? '';	// phpcs:ignore
	return $cached;
}

public static function trailingdotit($str): string
{ return rtrim($str,'.') . '.'; }

/* IS_? */

public static function is_real_admin(): bool
{
	static $cached = null;
	if ($cached === null)
	{	
		$cached = self::is_interactive() && is_admin();
	}
	return $cached;
}

public static function is_plugins_page(): bool
{
	static $cached = null;
	if ($cached === null)
	{
		$q = strtok(self::query(), '?'); // remove query string
		$needle = '/wp-admin/plugins.php';
		$cached = substr($q, -strlen($needle)) === $needle;
	}
	return $cached;
}

public static function is_ajax(): bool
{ return defined('DOING_AJAX') && DOING_AJAX; }

public static function is_cron(): bool
{ return defined('DOING_CRON') && DOING_CRON; }

public static function is_cli(): bool
{ return defined('WP_CLI') && WP_CLI; }

public static function is_rest_or_cli(): bool
{ return (defined('REST_REQUEST') && REST_REQUEST) || (self::is_cli()); }

public static function is_interactive(): bool
{
	static $cached = null;
	if ($cached !== null) return $cached;

	if ((self::is_ajax()) || (self::is_cron()) || (self::is_rest_or_cli())) return $cached = false;

	return $cached = true;
}

/* INIT TOOLS */

public static function register_activation_deactivation_hook($plugin_file, $activate = -1, $deactivate = 0, $slug = '')
{
	register_activation_hook($plugin_file, function () use ($plugin_file, $slug, $activate) 
	{ 
		self::integrity_check(self::plugin_by_dir($plugin_file));
		if ($activate === 1) require dirname($plugin_file) . '/includes/atec-' . $slug . '-activation.php'; 
	});

	if ($deactivate === 1 && $slug !== '') 
	{
		register_deactivation_hook($plugin_file, function () use ($plugin_file, $slug) 
		{ require dirname($plugin_file) . '/includes/atec-' . $slug . '-deactivation.php'; });
	}
}

public static function maybe_register_settings($dir, $slug, $noNav = false, $custom = '')
{
	$option_page = $_POST['option_page'] ?? '';	// phpcs:ignore
	$is_option_page = strpos($option_page, 'atec_' . strtoupper($slug)) !== false;

	$require = $is_option_page;
	
	if (!$require)
	{
		$query = self::query();
		$is_plugin_page = strpos($query, 'admin.php?page=atec_' . $slug) !== false;
		if ($is_plugin_page)
		{
			$require = 
			($noNav || preg_match('/nav=[^&]*Settings/', $query)) ||
			($custom === '' ? true : strpos($query, $custom) !== false) ||
			(strpos($query, 'settings-updated=true') !== false);
		}
	}
	
	if ($require) require "$dir/includes/atec-$slug-register-settings.php";
}

public static function integrity_check($plugin): void // only on activation or when agreed
{
	if (get_option('atec_allow_integrity_check',false))
	{
		$domain = rawurlencode(get_bloginfo('url'));
		wp_remote_get("https://atecplugins.com/WP-Plugins/activated.php?plugin={$plugin}&domain={$domain}");
	}
}

/* SETTINGS */

public static function get_settings($slug, $option=null)
{
	$settings = get_option('atec_'.strtoupper($slug).'_settings',[]);
	
	if ($option === null || !is_array($settings) || empty($settings)) return $settings;
	
	if (!array_key_exists($option, $settings)) return null;
	
	$bool = filter_var($settings[$option], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	if ($bool !== null) return $bool;

	return $settings[$option];
}

public static function update_settings($slug, $settings): void
{ update_option('atec_'.strtoupper($slug).'_settings', $settings); }

public static function delete_settings($slug): void
{ delete_option('atec_'.strtoupper($slug).'_settings'); }

public static function set_version($slug, $version)
{ wp_cache_set("atec_{$slug}_version", $version, 'atec_np'); }

/* PLUGINS */

public static function plugin_fixed_name($p) 
{
	$p = ucwords(str_replace('-', ' ', $p));
	return trim(str_ireplace(
		['atec', 'apcu', 'webp', 'svg', 'htaccess', 'oc benchmark'],
		['atec', 'APCu', 'WebP', 'SVG', 'HTaccess', 'OC Benchmark'],
		$p));
}

public static function plugin_prefix($p): string { return in_array($p, ['mega-cache', 'cache-tune']) ? '' : 'atec-'; }

public static function normalize_path(string $path): string
{
	$path = str_replace('\\', '/', $path);												// Replace backslashes with forward slashes
	$path = preg_replace('|(?<!:)/+|', '/', $path);									// Remove redundant slashes (except double-slash at start for network shares)
	if (isset($path[1]) && $path[1] === ':') $path = ucfirst($path);		// Uppercase Windows drive letter if present
	return $path;
}

public static function plugin_by_dir($dir): string
{
	$dir = self::normalize_path($dir);
	$pluginDir = self::normalize_path(WP_PLUGIN_DIR);

	if (!$dir || !$pluginDir || strpos($dir, $pluginDir) !== 0) return '';

	$relative = substr($dir, strlen($pluginDir)); // Strip base dir
	$parts = explode('/', ltrim($relative, '/'));

	return $parts[0] ?? '';
}

public static function plugin_url($plugin) : string
{ return WP_PLUGIN_URL . '/' . $plugin; }

public static function plugin_url_by_dir($dir) : string		// required by self::menu, reg_script, reg_style
{ return self::plugin_url(self::plugin_by_dir($dir)); }

/* MENU */

public static function group_page($plugin): void
{ \ATEC\DASHBOARD::init($plugin); }

public static function group_callback($plugin): callable
{ return function () use ($plugin) { self::group_page($plugin); 	}; }

public static function dashboard_callback($plugin, $slug): callable
{ return function () use ($plugin, $slug) { require WP_PLUGIN_DIR . '/' . $plugin . "/includes/atec-{$slug}-dashboard.php"; }; }

// restrict certain plugins to post/page editors
public static function current_user_can($role): bool
{
	static $cached = null;

	if ($cached === null)
	{
		$cached = 
			[	
				'admin'  => current_user_can('manage_options'),
				'editor' => current_user_can('edit_posts') || current_user_can('edit_pages')
			];
	}

	return $cached[$role] ?? false;
}

public static function admin_head_styles()
{
	self::$admin_styles_loaded = true;
	add_action('admin_head', function () 
	{
	?>
	<style>
		.toplevel_page_atec_group .wp-menu-image img { max-width: 20px !important; max-height: 20px !important; }
		#toplevel_page_atec_group .wp-submenu .atec-svg-icon 
		{ display: inline-flex; max-width: 20px !important; text-align: center; vertical-align: middle; margin: 0 6px 0 -3px; }

		#wpadminbar .atec-admin-bar-row { display: flex; gap: 5px; align-items: center; }
		#wpadminbar .atec-admin-bar-row svg 
		{ display: block; flex: none; object-fit: contain; width: 18px; height: 18px; max-height: 18px; 	}
	</style>
	<?php
	});
}

public static function menu($dir, $slug, $title, $css=[], $js=[]): bool
{
	if (!self::$admin_styles_loaded) self::admin_head_styles();
	
	static $style_loaded = null;
	static $atec_group_active = null;
	static $single_slug = ['wpmc', 'wpct'];

	$single = in_array($slug, $single_slug);
	$menu_slug = 'atec_'.$slug;
	$plugin = self::plugin_by_dir($dir);

	if (!self::current_user_can('admin')) return false;
		
	$current = self::slug();
	if ($current==='atec_group') { if ($style_loaded===null) { \ATEC\TOOLS::load_atec_style($dir, ['style']); $style_loaded=true; } }
	elseif ($current===$menu_slug)
	{
		if (!$style_loaded) $css[]= 'style';
		if (!in_array($slug, self::$skip_load_check)) { $css[]= 'check'; $js[]= 'check'; }
		add_action('admin_enqueue_scripts', function () use ($dir, $css, $js) 
		{
			if (!empty($css)) \ATEC\TOOLS::load_atec_style($dir, $css);
			if (!empty($js)) \ATEC\TOOLS::load_atec_script($dir, $js); 
		});
		if (in_array($slug, self::$require_install)) require "$dir/includes/atec-{$slug}-install.php";
	}
	
	$callback = self::dashboard_callback($plugin, $slug);
	$group_callback = self::group_callback($plugin);

	if ($single) add_menu_page($title, $title, 'manage_options', $menu_slug, $callback, \ATEC\SVG::base64($slug)); // standalone menu
	else
	{
		$group_slug = 'atec_group';
		if ($atec_group_active===null) // add top-level group if not already present
		{
			$icon_url = self::plugin_url_by_dir($dir). '/includes/ATEC/SVG.php?logo=true';
			add_menu_page('atec-systems', 'atec-systems', 'manage_options', $group_slug, $group_callback, $icon_url);
			add_submenu_page($group_slug, 'Group', '<span class="wp-menu-image dashicons-before dashicons-sos" style="margin: 0 8px 0 -5px; "></span>Dashboard', 'manage_options', $group_slug, $group_callback);
			$atec_group_active = true;
		}

		// Add submenu if plugin does not have an admin bar link like atec-updates
		add_submenu_page($group_slug, $title, '<span style="margin-right: 2px;">' . \ATEC\SVG::plain($slug,'atec-svg-icon') . '</span>' . $title, 'manage_options', $menu_slug, $callback);	//in_array($slug, $hidden_slug) ? 'atec-hidden-menu' : 
	}

	return true;
}

/* NOTICE */

public static function build_notice(array &$notice, string $type= '', string $str= ''): void
{
	if (!isset($notice)) $notice = [];
	if ($type=== '') $type = 'warning';
	$str = self::trailingdotit($str);
	$message = $notice['message'] ?? '';
	$message .= ($message === '' ? '' : ' ') . $str;
	if (!empty($notice['type']) && $notice['type'] !== 'info') { $type = $notice['type']; } // if $type is more important than "info", like "warning"
	$notice['type'] = $type;
	$notice['message'] = $message;
}

private static $admin_debug_cache = null;

public static function get_admin_debug(): array
{
	if (self::$admin_debug_cache === null) { self::$admin_debug_cache = get_option('atec_admin_debug', []); }
	return self::$admin_debug_cache;
}

public static function set_admin_debug($slug, $notice = []): void 
{
	$arr = self::get_admin_debug();
	
	if (empty($notice)) unset($arr[$slug]);
	else $arr[$slug] = $notice;
	
	$option_key = 'atec_admin_debug';
	if (empty($arr)) delete_option($option_key);
	else update_option($option_key, $arr, false);
}

public static function delete_admin_debug($slug): void 
{
	self::set_admin_debug($slug, []);
}

public static function is_admin_debug($slug): bool
{
	$arr = self::get_admin_debug();
	return isset($arr[$slug]);
}

public static function admin_debug($slug, $dir = ''): void 
{
	$arr = self::get_admin_debug();
	if (!isset($arr[$slug])) return;

	$notice = $arr[$slug];
	if (empty($notice) || !isset($notice['message'])) return;

	if ($dir) self::add_admin_notice_action($dir, $notice['type'], $notice['message']);
	else self::admin_notice($slug, $notice['type'], $notice['message']);
}

public static function admin_debug_all(): void 
{
	$arr = self::get_admin_debug();
	if (empty($arr)) return;
	foreach($arr as $key => $notice)
	{
		$type = $notice['type'];
		$msg = $notice['message'];
		add_action('admin_notices', function () use ($key, $type, $msg) { self::admin_notice($key, $type, $msg); });
	}
}

public static function admin_notice($slug, $type= '', $msg= ''): void 
{
	if ($type=== '') $type = 'warning';	// ['info', 'warning', 'error', 'success']

	$plugin = \ATEC\GROUP::plugin_by_slug($slug);
	if ($slug=== 'wpu') $plugin_name = 'Updates';
	else
	{
		$prefix = self::plugin_prefix($plugin);
		$plugin_name = self::plugin_fixed_name(($prefix !=='' ? $prefix.' ' : '') . $plugin);
	}

	$id = uniqid();
	echo 
	'<div id="atec_notice_' . esc_attr($id) . '" class="notice notice-', esc_attr($type), ' is-dismissible" role="alert" aria-live="assertive">
		<p>', esc_html($plugin_name.': '.self::trailingdotit($msg)), '</p>
		<button type="button" class="notice-dismiss" aria-label="Dismiss this notice" data-slug="' . esc_attr($slug) . '" data-id="' . esc_attr($id) . '"></button>
	</div>';

	add_action('admin_footer', function() 
	{
		?>
		<script type="text/javascript">
			jQuery(document).on('click', '.notice-dismiss', function() 
			{
				const id = jQuery(this).parent().attr('id');
				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'atec_admin_notice_dismiss',
						slug: jQuery(this).data('slug'),
						id: id
					},
					success: function(response) 
					{
						if (response.success) jQuery('#'+id).slideUp();
					}
				});
			});
		</script>
		<?php
	}, 10 ,0);
}

public static function add_admin_notice_action($dir, $type= '', $msg= ''): void 
{
	$slug = \ATEC\GROUP::slug_by_dir($dir);
	add_action('admin_notices', function () use ($slug, $type, $msg) { self::admin_notice($slug, $type, $msg); });	// display on next admin load
}

}
?>