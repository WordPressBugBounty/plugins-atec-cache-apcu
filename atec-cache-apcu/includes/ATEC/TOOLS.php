<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\PRO;

final class TOOLS
{

// STRINGS

public static function str_istarts_with(string $haystack, string $needle): bool 
{
	return strncasecmp($haystack, $needle, strlen($needle)) === 0;
}

public static function stri_contains(string $haystack, string $needle): bool 
{
	return stripos($haystack, $needle) !== false;
}

// TOOLS

public static function on_off($bool, $reverse = false): string 
{
	$label = $bool ? 'On' : 'Off';
	if ($reverse) { $class = $bool ? 'red' : 'green'; }
	else { $class = $bool ? 'green' : 'red'; }

	return '<span class="atec-'.$class.'">'.$label.'</span>';
}

public static function gmdate($ts, $format= 'm/d H:i') 
{
	if (!is_numeric($ts)) $ts = strtotime($ts); // Convert date string to timestamp
	return gmdate($format, $ts); 
}

public static function progress_percent($id, $percent = null)
{
	if ($percent === null)
	{
		echo
		'<div class="atec-border atec-percent-block" style="width:260px; background:rgb(250, 250, 250);">',
			'<div class="atec-dilb atec-fs-12">Progress</div><div class="atec-dilb atec-float-right atec-fs-12">100%</div><br>',
			'<div class="atec-percent-div" style="width:250px; background: rgb(235, 235, 235);">',
				'<span id="atec_progress_percent_', esc_attr($id), '" style="display:inline-block;height:12px;width:0;background-color:lightgreen;"></span>',
			'</div>',
		'</div>';
		self::flush();
	}
	else
	{
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Inline script required for real-time progress
		printf(
			'<script>%s</script>',
			"jQuery('#atec_progress_percent_" . esc_js($id) . "').css('width','" . esc_js($percent) . "%');jQuery(document.currentScript).remove();"
		);
		self::flush();
	}
}

public static function lazy_require_class(string $dir, string $path, string $class= '', ...$args): bool
{
	$full_path = "$dir/$path";
	if (is_file($full_path)) 
	{
		require $full_path;
		if ($class !== '')
		{
			if (!str_contains($class, 'ATEC_')) $class = 'ATEC_'.$class;	// This will add class name or use full class if passed like "ATEC_WPC"
			if (class_exists($class)) 
			{
				if (method_exists($class, 'init')) $class::init(...$args);
				else new $class(...$args);
				return true; 
			}
		}
	}
	if (str_ends_with($path,'-pro.php')) self::pro_missing();
	return false;
}

public static function lazy_require(string $dir, string $path, ...$args): void
{
	$full_path = "$dir/$path";
	if (is_file($full_path)) 
	{
		$callable = require $full_path;
		if (is_callable($callable)) { $callable(...$args); }
	}
}

private static function br($str): void
{
	$str = str_replace('<br>', "\n", $str);
	$ex = explode("\n", $str);
	$last = count($ex) - 1;

	foreach ($ex as $index => $t)
	{
		echo wp_kses_post($t);
		if ($index < $last) echo '<br>';
	}
}

public static function percent_format($value, $decimals = 1) 
{
	return sprintf("%.{$decimals}f", $value) . '<small> %</small>';
}

public static function td_size_format($bytes, $decimals = 0, $class = '') 
{
	if (!$bytes) { echo '<td>-/-</td>'; return; }
	echo '<td class="atec-nowrap atec-right', ($class !== '' ? ' '.esc_attr($class) : ''), '">';
		echo wp_kses_post(self::size_format($bytes, $decimals));
	echo '</td>';
}

public static function size_format($bytes, $decimals = 0) 
{
	$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
	$bytes = max(0, $bytes);

	$pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
	$pow = min($pow, count($units) - 1);

	$value = $bytes / pow(1024, $pow);

	// Format number with fixed decimal places
	$formatted = number_format($value, $decimals);

	// Wrap unit in <small> tag
	return $formatted . ' <small>' . esc_html($units[$pow]) . '</small>';
}

public static function abspath()
{
	static $cached = null;
	if ($cached === null) 
	{ 
		if (! function_exists('get_home_path')) require_once ABSPATH . 'wp-admin/includes/file.php';
		$cached = get_home_path();
	}
	return $cached;
}

public static function clear($count=1)
{ 
	for ($i=0; $i<$count; $i++) echo '<br class="atec-clear">'; 
}

public static function hr()
{ self::clear(); echo '<hr>'; self::clear(); }

public static function una($dir, $nav_default = '')
{
	$nonce = INIT::nonce();
	$nav = self::clean_request('nav', $nonce);

	if ($nav_default === '') $nav_default = 'Dashboard';
	if ($nav === '') $nav = $nav_default;
	$navs = ($nav_default === 'Dashboard') ? ['#admin-home Dashboard'] : (($nav_default === 'Settings') ? ['#admin-generic Settings'] : []);

	$action_raw = self::clean_request('action', $nonce);
	$id = self::clean_request('id', $nonce);

	// Extract id from action param if present
	if (preg_match('/(?:^|&)id=([^&]*)/', $action_raw, $m))
	{
		$id = isset($m[1]) ? $m[1] : '';
	}

	// Remove id=... from action string
	$action = preg_replace('/(&)?id=[^&]*/', '', $action_raw);
	$action = trim($action, '&');

	return (object) [
		'dir'		=> dirname($dir),
		'url'		=> self::url(),
		'nonce'	=> wp_create_nonce($nonce),
		'action'	=> $action,
		'nav'		=> $nav,
		'navs'	=> $navs,
		'id'		=> $id,
		'slug'		=> str_replace('atec_', '', INIT::slug())
	];
}

public static function enabled($enabled, $active=false): void
{
	echo
	'<span style="color:', ($enabled?($active?'black':'green'):'red'), '" title="', $enabled ? 'Enabled' : 'Disabled', '" ',
		'class="', esc_attr(self::dash_class($enabled?'yes-alt':'dismiss')), '">',
	'</span>';
}

public static function random_string($length, $lower=false): string
{
	$charset = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ'; $string = '';
	while(strlen($string)<$length) { $string .= substr($charset,random_int(0,61),1); }		// phpcs:ignore
	return $lower?strtolower($string):$string;
}

public static function short_string($str, $len=128): string
{
	if ($str== '') return $str;
	return strlen($str)>$len?substr($str, 0, $len).' ...':$str;
}

public static function format_duration($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime();
	$dtT->setTimestamp((int) $seconds);
	$diff = $dtF->diff($dtT);

	$days = (int) $diff->format('%a');
	$time = $diff->format('%H:%I');

	if ($days > 0)
	{
		$label = $days === 1 ? 'day' : 'days';
		return "$days $label, $time";
	}

	return $time;
}

private static $url_whitelist = ['page', 'action', 'id', 'tab', 'section', 'nav', 'updated', 'settings-updated', '_wpnonce'];

public static function url( $page = null ): string
{
	static $cached = [];
	if ( $page === null ) $page = INIT::_GET('page');
	if ( isset( $cached[ $page ] ) ) return $cached[ $page ];
	return $cached[ $page ] = INIT::admin_url() . 'admin.php?page=' . urlencode( $page );
}

// PROGRESS

public static function loader_div($id='', $str=''): void
{
	if ($str === '')
	{
		self::reg_inline_script('wpx_loader', 'jQuery("#'.esc_attr($id).'").fadeOut();', true);
	}
	else
	{
		echo '<div id="', esc_attr($id), '" class="atec-badge atec-bg-w atec-bold atec-mt-10 atec-mb-5">', esc_html($str), ' '; 
			self::loader_dots(); 
		echo '<br></div>';
		self::flush();
	}
}

public static function loader_dots(int $count = 9): void
{
	if ($count<1) TOOLS::reg_inline_script('wpx_processing', 'jQuery(".atec-loader-dots").remove();');
	else
	{
		echo '<div class="atec-loader-dots atec-dilb">';
		for ($i = 1; $i <= $count; $i++) echo '<span style="--i:', esc_attr($i), '"></span>';
		echo '</div>';
		self::flush();
	}
}

private static function progress_div(): void {}		// OUTDATED: 250628 | CLEANUP: Delete
public static function progress(): void { }			// OUTDATED: 250628 | CLEANUP: Delete

public static function flush(): void
{
	while (true)
	{
		if (ob_get_level() <= 0) break;

		$status = ob_get_status();
		if (!empty($status['name']) && str_contains($status['name'], 'gzhandler')) break;

		if (!@ob_end_flush()) break;
	}
	@flush();
}

public static function ob_end_clean(): void
{
	if (ob_get_level() > 0) @ob_end_clean();
}

// PROGRESS AREA START

// DASH AREA START

private static $dashArr = ['admin-comments', 'admin-generic', 'admin-home', 'admin-plugins', 'admin-settings', 'admin-site', 'admin-tools', 'analytics', 'archive', 'awards', 'backup', 'businessman', 'clipboard', 'code-standards', 'controls-play', 'cover-image', 'database', 'database-add', 'edit', 'editor-code', 'editor-removeformatting', 'editor-table', 'feedback', 'format-gallery', 'forms', 'groups', 'hourglass', 'info', 'insert', 'list-view', 'performance', 'trash', 'translation', 'update'];

public static function dash_class($icon, $class = ''): string
{ return 'dashicons dashicons-' . $icon . ($class !== '' ? ' '.$class : ''); }

public static function dash_span(string $dash, string $class = '', string $style = ''): void
{
	$classes = trim("dashicons dashicons-{$dash} {$class}");
	echo '<span class="', esc_attr($classes), '"';
		if ($style !== '') echo ' style="', esc_attr($style), '"';
	echo '></span>';
}

private static function dash_and_button(string $dnb): object
{
	preg_match('/#([\w\-]+)\s?(.*)/i', $dnb, $matches);
	$dash = $matches[1] ?? '';
	$button = isset($matches[2]) ? trim($matches[2]) : $dnb;
	return (object) ['dash' => $dash, 'button' => $button];
}

private static function dash_and_button_div($dnb, string $class = ''): void
{
	if (!empty($dnb->dash)) 
	{
		self::dash_span($dnb->dash);
		if ($dnb->button=== '') return;
	}
	if (isset($dnb->button))
	{
		if ($dnb->button===true || (int)$dnb->button===1) { self::dash_span('yes-alt', 'atec-green'); }
		elseif ($dnb->button=== '' || $dnb->button===false || $dnb->button===0) { self::dash_span('dismiss', 'atec-red'); }
		elseif ($dnb->button !== '') echo '<span',  ($class !== '' ? ' '.esc_attr($class) : ''), '>', esc_html($dnb->button), '</span>';
	}
}


private static function dash_yes_no($yes) 
{ return '<span class="'.TOOLS::dash_class($yes?'yes-alt' : 'dismiss', 'atec-'.($yes?'green' : 'red')).'"></span>'; }

// DASH AREA START

// PRO AREA START

public static function integrity_banner($dir) : void
{
	$plugin = INIT::plugin_by_dir($dir);
	$link_yes = INIT::build_url('group', '', '', ['integrity' => true, 'plugin' => $plugin]);
	$link_no= str_replace('true', 'false', $link_yes);
	echo
	'<div class="button atec-sticky-left">',
		'<div class="atec-dilb" style="line-height:8px; padding-bottom: 2px;" title="One time connection on activation.">',
			'<span class="atec-fs-8">Allow connection to</span><br><span class="atec-fs-10">atecplugins.com</span>',
		'</div>',
		'<div class="atec-row atec-ml-5">',
			'<a style="background: rgba(0, 180, 0, 0.5);" class="atec-integritry" href="', esc_url($link_yes), '">YES</a>',
			'<a style="background: rgba(180, 0, 0, 0.5); margin-left: -5px;" class="atec-integritry" href="', esc_url($link_no), '">NO</a>',
		'</div>',
	'</div>';
}

public static function pro_license($slug=null): bool
{
	if (class_exists('ATEC\PRO')) return PRO::pro_check_license(null, null, $slug);
	return false;
}

private static function pro_banner($slug): bool
{
	$license_ok = self::pro_license($slug);
	$href = INIT::build_url($slug, '', 'License');
	echo
	'<div id="atec_pro_banner" class="atec-sticky-right">
		<a class="button atec-', ($license_ok ? 'green' : 'blue') ,'" href="', esc_url($href), '" style="', ($license_ok ? ' border: var(--border-lightgrey);' : ''), '">';
			self::dash_span('awards', 'atec-'.($license_ok ? 'green' : 'blue'));
			echo
			'<span>',
				$slug=== 'wpmc' ? 'MC ' : ($slug=== 'wpct' ? 'CT4W ' : ''),
				($license_ok ? '‘PRO’ version' : 'Upgrade to ‘PRO’'),
			'.</span>',
		'</a>
	</div>';
	return $license_ok;
}

public static function pro_feature($una, $desc= '', $small=false, $license_ok=null, $break=false): bool
{
	
	if (is_null($license_ok)) $license_ok= self::pro_license($una->slug)===true;
	if (!$license_ok)
	{
		$href = INIT::build_url($una, '', 'License');
		echo
		'<div class="', ($desc !== '' ? 'atec-dilb' : ''), '">
			<a class="atec-dilb atec-nodeco atec-blue" href="', esc_url($href), '">';
			if ($small)
			{
				echo
				'<div class="atec-badge atec-blue atec-fs-12" style="background: #f9f9ff; border: solid 1px #dde; margin: 0; padding: 4px 5px;">',
					'<div class="atec-col atec-vat" style="max-width: 20px;">'; self::dash_span('awards', 'atec-blue atec-fs-14', 'padding-top: 2px;'); echo '</div>',
					'<div class="atec-col atec-vat">Upgrade to ‘PRO’', 
						str_starts_with($desc,'<br>') ? '.' :' '; 
						self::br(INIT::trailingdotit($desc)); 
					echo 
					'</div>',
				'</div>';
				$desc= '';
			}
			else self::msg('blue','‘PRO’ feature - please upgrade');
			echo 
			'</a>
		</div>';
		if ($desc!== '') { echo '<div class="atec-pro-box"><h4 class="atec-mt-0">'; self::br(INIT::trailingdotit($desc)); echo '</h4></div>'; 	}
		if ($break) self::clear();
	}
	return $license_ok; // phpcs:ignore
}

public static function pro_block($una, $str = ''): void
{
	$href = INIT::build_url($una, '', 'License');
	$str =
		$str === '' ?
		'This is a ‘PRO’ ONLY plugin.<br>A license is required to use the basic functions.' :
		'Please upgrade to ‘PRO’ version '.INIT::trailingdotit($str);

	echo
	'<div class="atec-df atec-pro-box">',
		'<div class="atec-df1 atec-vat" style="max-width: 22px;">'; 
			self::dash_span('awards', 'atec-blue atec-fs-14', 'padding-top: 2px;'); 
		echo 
		'</div>',
		'<div class="atec-df1 atec-vat atec-nowrap">',
			'<a class="atec-nodeco atec-blue" href="', esc_url($href), '">';
			self::br($str);
			echo
			'</a>',
		'</div>',
	'</div>';
}

public static function pro_only($una, $break=false): void
{ 
	self::pro_block($una);
	if ($break) self::clear();
}

public static function pro_missing($class= ''): void
{
	if ($class !== '' && class_exists($class)) return;
	echo
	'<div class="atec-badge atec-dilb" style="background: #fff0f0;">
		<div class="atec-dilb" style="width:20px; margin-right:5px;">'; self::dash_span('dismiss'); echo '</div>
		<div class="atec-dilb atec-vam">A required class-file ('.esc_html($class).') is missing – please ';
		if (is_plugin_active('atec-deploy/atec-deploy.php')) echo 'use <a href="', esc_url(admin_url().'admin.php?page= atec_wpdp'), '">';
		else echo 'download/activate <a href="https://atecplugins.com/WP-Plugins/atec-deploy.zip">';
		echo 'atec-deploy</a> to install the ‘PRO’ version of this plugin.
		</div>
	</div>';
}

// PRO AREA END

// NAV AREA START

public static function add_nav(&$una, $option, $nav)
{
	if (is_array($nav)) $una->navs = array_merge($una->navs, $nav);
	elseif (INIT::bool($option)) $una->navs[] = $nav;
}

public static function nav_tab_dashboard($una): void
{ $una->navs=['#admin-home Dashboard']; self::nav_tab($una); }

private static function single_nav_tab($una, $act_nav, $icon, $button, $license_ok=false, $break=false, $single=false) : void
{
	$href = INIT::build_url($una, '', $act_nav);
	echo
	'<div class="atec-dilb">',
		'<div class="atec-db atec-pro" style="height:15px; padding-left:10px;">', 
			(!$license_ok && $break ? 'PRO' : '&nbsp;'), 
		'</div>',
		'<a href="', esc_url($href), '" class="nav-tab', ($single ? ' nav-tab-single' : ''), ($una->nav=== $act_nav ? ' nav-tab-active' : ''), '">';
			if (!empty($icon))
			{
				if (in_array($icon,self::$dashArr)) self::dash_span($icon,'atec-blue');
				else \ATEC\SVG::echo($icon);
			}
			if (!empty($button)) { echo ' ', esc_attr($button); }
		echo
		'</a>
	</div>';
}

public static function nav_tab($una, $break=999, $license_ok=null, $about=false, $update=false, $debug=false): void
{
	$margin_top = $license_ok ? '-15' : '-5';
	echo
	'<h2 class="nav-tab-wrapper" style="padding: 0; margin: '.esc_attr($margin_top).'px 0 5px 0;">';
		$c = 0;
		foreach($una->navs as $a)
		{
			$c++;
			$dnb = self::dash_and_button($a);
			$nice = str_replace(['(', ')'], '', str_replace([' ', '.', '-', '/'], '_', $dnb->button));
			if (!$license_ok && $c-1 === $break) echo '<div class="atec-dilb atec-mr-10"></div>';
			self::single_nav_tab($una, $nice, $dnb->dash, $dnb->button, $license_ok, $c > $break, false);
		}

		echo
		'<div class="atec-dilb atec-float-right atec-ml-10 atec-mr-10">';
			if ($debug) self::single_nav_tab($una, 'Debug', 'code-standards', '', false, false, true);
			if ($update) self::single_nav_tab($una, 'Update', 'update', '', false, false, true);
			if ($about) self::single_nav_tab($una, 'About', 'clipboard', '', false, false, true);
			self::single_nav_tab($una, 'License', 'awards', '', false, false, true);
			if (!str_contains($una->url, 'atec_group')) self::single_nav_tab($una, 'Info', 'info', '', false, false, true);
		echo
		'</div>
	</h2>';
}

// NAV AREA END

// TABLE AREA START

public static function ul($title = '', $lis = [], $class = ''): void
{
	if ($title !== '') echo '<u><b>', esc_html($title), '</b>:</u>';
	echo '<ul class="atec-ul', esc_html($class !== '' ? ' '.$class : ''), '">';

	foreach ($lis as $li)
	{
		echo 
		'<li>',
			wp_kses($li, self::$allowed_tr),
		'</li>';
	}

	echo '</ul>';
}

public static function div($arg, $str='', $border=false): void
{
	if (is_numeric($arg))
	{
		if ($arg === 0) echo '</div><div>';
		elseif ($arg<0)
		{
			for($i=0; $i<absint($arg); $i++) echo '</div>';
		}
	}
	else
	{
		if (str_starts_with($arg,'g'))	// Grid
		{
			echo '<div class="atec-g atec-', esc_attr($arg), '">';
				echo '<div>';
		}
	
		if (str_contains($arg, 'border'))
		{
			if (!empty($str)) TOOLS::little_block($str);
			echo '<div class="atec-border-white">';
		}
		
		if ($arg === 'box')
		{
				echo '<div class="atec-box-white">';
				if (!empty($str)) echo '<p>', wp_kses_post(INIT::trailingdotit($str)), '</p>';
		}
		elseif ($arg === 'btn')
		{
			echo
			'<div class="atec-btn-div">';
		}
		elseif ($arg === 'row')
		{
			echo
			'<div class="atec-row">';
		}
	}
}

public static function table_header($tds = [], $id = '', $class = ''): void
{
	$class = str_replace(['summary'], ['atec-table-td-bold-first atec-table-td-right-not-first'], $class);
	echo '<table', esc_attr(!empty($id) ? ' id="'.$id.'"' : ''), ' class="atec-table atec-table-tiny atec-fit ', esc_attr($class), '">';
	if (!empty($tds))
	{
		echo '<thead>';
		self::table_tr($tds, 'th');
		echo '</thead>';
	}
	echo '<tbody>';
}

public static function table_footer(): void
{ echo '</tbody></table>'; }

private static $allowed_tr = 
	[
		'small'	=> [],
		'b' 		=> [],
		'a'			=> ['href' => [], 'target'=> [] ],
		'span'	=> ['class' => [], 'style' => [] ],
		'img'		=> ['src' => [], 'style' => [] ],
		'hr'		=> ['class' => [] ] 
	];

// NEW: introduced 250710 | CLEANUP: remove table_tr
public static function tr($tds = [], $tag = 'td', $class = ''): void
{ self::table_tr($tds, $tag, $class); }

public static function table_tr($tds = [], $tag = 'td', $class = ''): void
{
	if (empty($tds)) 
	{
		echo 
		'<tr class="empty_tr"><td colspan="99"></td></tr>
		<tr class="empty_tr"><td colspan="99"></td></tr>';
		return;
	}
	
	$class = str_replace('bold', 'atec-table-tr-bold', $class);
	
	$tag = $tag === '' ? 'td' : $tag; // fallback
	echo '<tr', esc_html($class !== '' ? ' class="'.$class.'"' : ''), '>';

	$dash_reg = '/#([\-|\w]+)\s?(.*)/i';
	foreach ($tds as $td)
	{
		
		// Check for leading number followed by '@' to set colspan
		if (preg_match('/^(\d+)@(.*)$/', $td, $colMatches)) { $colspan = (int) $colMatches[1]; $td = $colMatches[2]; }
		else $colspan = 1;

		// Check for leading # to set dashicon
		preg_match($dash_reg, $td, $matches);
		if (!empty($matches[1])) { $dash = $matches[1]; $td = isset($matches[2]) ? $matches[2] : ''; }
		else $dash = '';
		
		if (!empty($td))
		{
			$td_plain = wp_strip_all_tags($td);
			$right = 
				preg_match('/^\s*[\d\.,]+\s*$/', $td_plain) // pure numbers
				|| preg_match('/^\s*[\d\.,]+\s*(B|KB|MB|GB|TB|ms|s|%)\s*$/i', $td_plain); // size_format style
		}
		else $right = false;

		echo 
		'<', 
			esc_attr($tag), 
			($colspan > 1 ? ' colspan="' . esc_attr($colspan) . '"' : ''), 
			($right ? ' class="atec-right atec-nowrap"' : ''),
		'>';
			
			if (!empty($dash)) { self::dash_span($dash); echo (!empty($td)) ? ' ' : ''; }
			echo wp_kses($td, self::$allowed_tr);
		
		echo 
		'</', esc_attr($tag), '>';
	}
	echo '</tr>';
}

// NEW: introduced 250710 | CLEANUP: remove table_td
public static function td($td = '', $class= '')
{ self::table_td($td, $class); }

public static function table_td($td = '', $class= '')
{
	if (preg_match('/^(\d+)@(.*)$/', $td, $colMatches)) { $colspan = (int) $colMatches[1]; $td = $colMatches[2]; }
	else $colspan = 1;
	echo '<td colspan="'.esc_attr($colspan).'"', ($class !== '' ? ' class="'.esc_attr($class).'"' : ''), '>', wp_kses_post($td), '</td>'; 
}

// TABLE AREA END

private static function action_params($input) 
{
	if (empty($input)) return ['', []];

	$parts = explode('&', $input);
	$action = array_shift($parts);
	parse_str(implode('&', $parts), $params);
	return [$action, $params];
}

public static function button_confirm($una, $action, $nav, $button): void
{
	static $injected = false;
	[$action, $args] = self::action_params($action);
	$href = INIT::build_url($una, $action, $nav, $args);

	echo 
	'<div class="atec-btn-confirm">';

		if (!$injected)
		{
			$injected = true;
			self::load_inline_script('btn-confirm', '
				function atec_confirm_click(el)
				{
					let $el = jQuery(el);
					if ($el.hasClass("atec-confirm-ready")) return true;
					jQuery(".atec-confirm-btn").removeClass("atec-confirm-ready");
					$el.addClass("atec-confirm-ready");
					return false;
				}
			', true);
		}
	
		echo 
		'<a href="', esc_url($href), '" data-href="', esc_url($href), '" ',
			'class="button button-secondary atec-confirm-btn"',
			'onclick="return atec_confirm_click(this)">',
				'<div class="atec-confirm-inner">';
					self::dash_and_button_div(self::dash_and_button($button));
				echo 
				'<div class="atec-confirm-label"></div>',
			'</div>',
		'</a>';

	echo 
	'</div>';
}

//	self::button($una, $action, $nav, $button, false, true); 
public static function button($una, $action, $nav = '', $button = '', $primary = false, $confirm = false, $disabled = false): void
{
	if ($confirm) 
	{
		self::button_confirm($una, $action, $nav, $button);
		return;
	}
	
	[$action, $args] = self::action_params($action);
	$href = INIT::build_url($una, $action, $nav, $args);

	echo '<a', $disabled ? ' disabled' : '', ' href="', esc_url($href), '" class="button button-', ($primary ? 'primary' : 'secondary'), '">';
		self::dash_and_button_div(self::dash_and_button($button));
	echo '</a>';
}

public static function button_confirm_td($una, $action, $nav, $button): void
{
	echo '<td class="atec-nowrap">';
		self::button($una, $action, $nav, $button, false, true);
	echo '</td>';
}

public static function dash_button($una, $action, $nav, $dash, $enabled, $id, $primary=false): void
{
	if ($enabled) $href = INIT::build_url($una, $action, $nav, ['id' => $id]);
	else $href = '';
	
	echo 
	'<a ', ($enabled ? '' : 'disabled '), ($href !== '' ? 'href="'.esc_url($href ).'" ' : ''),
		'class="button ', esc_attr(self::dash_class($dash, 'button-'.($primary ? 'primary' : 'secondary'))), '">',
	'</a>';
}

public static function dash_button_td($una, $action, $nav, $dash, $enabled, $id, $primary=false): void
{ 
	echo '<td>'; 
		self::dash_button($una, $action, $nav, $dash, $enabled, $id, $primary); 
	echo '</td>'; 
}

// MSG AREA START

public static function badge($ok, $str_success, $str_failed = '', $margin = false): void
{
	$bg_color = $ok === 'blue' ? '#f9f9ff' : ($ok === 'info' ? '#fff' : ($ok === 'warning' ? 'rgba(255, 253, 253, 0.95)' : ($ok ? 'var(--bg-success)' : 'var(--bg-error)')));
	$border = $ok === 'blue' ? 'button' : ($ok === 'info' ? 'lightgrey' : ($ok === 'warning' ? 'warning' : ($ok ? 'success' : 'error')));
	$icon = $ok === 'blue' ? 'awards' : ($ok === 'info' ? 'info-outline' : ($ok === 'warning' ? 'warning' : ($ok ? 'yes-alt' : 'dismiss')));
	$color_class = 'atec-' . ($ok === 'blue' ? 'blue' : ($ok === 'info' ? 'black' : ($ok === 'warning' ? 'orange' : ($ok ? 'green' : 'red'))));

	// Optional tag logic
	if (strpos($str_success, '#') !== false)
	{
		$ex = explode('#', $str_success);
		if (isset($ex[1]))
		{
			$str_success = $ex[0].' '.$ex[1];
			$str_failed = $ex[0] . ' ' . $str_failed;
		}
	}

	$str = $ok ? $str_success : $str_failed;
	$str = INIT::trailingdotit($str);

	echo 
	'<div class="atec-badge', ($margin ? ' atec-mr-5 atec-mb-5' : ''), '" style="background:', esc_attr($bg_color), '; border: var(--border-', esc_attr($border), ');">',
		'<div class="atec-col atec-vat ', esc_attr(self::dash_class($icon, $color_class)), '" style="max-width:20px"></div>',
		'<div class="atec-col atec-anywrap ', esc_attr($color_class), '">';
			self::br($str);
		echo 
		'</div>',
	'</div>';
}

public static function p_info($str, $class = '', $warning = false): void
{
	$str = INIT::trailingdotit($str); 
	echo '<div class="atec-badge atec-box-info">';
		echo '<div>', $warning ? '🔸':'🔹', '</div>';
		echo '<div ', ($class !== '' ? ' class="'.esc_attr($class).'"' : ''), '>', wp_kses_post(INIT::trailingdotit($str)), '</div>';
	echo '</div>';
}

// NEW: 250705
// CLEANUP: replace ($class !== '' ? ' class="'.esc_attr($class).'"' : '') with this method
private static function add_class(string $class, $attr=false): string
{ 
	$tmp = $class !== '' ? ' ' . $class : '';
	if ($tmp === '') return '';
	if ($attr) $tmp = ' class="'.trim($tmp).'"';
	return $tmp;
}

public static function p($str = '', $class = ''): void
{ echo '<p class="atec-mb-10', ($class !== '' ? ' '.esc_attr($class) : ''), '">', esc_html($str!== '' ? INIT::trailingdotit($str) : '&nbsp;'), '</p>'; }

public static function p_bold($title, $str = '', $class = ''): void
{
	echo '<p', wp_kses_post(self::add_class($class, true)), '><b>', wp_kses_post($title), '</b>';
		if ($str !== '') echo ': ', wp_kses_post($str);
	echo '</p>';
}

public static function p_title($text, $class = ''): void
{
	self::p_bold($text, '', 'atec-p-title'.self::add_class($class));
}

public static function h($c, $str, $class= ''): void
{
	echo '<h', esc_attr($c), ($class !== '' ? ' class="'.esc_attr($class).'"' : ''), '>', wp_kses_post($str) ,'</h', esc_attr($c),'>';
}

public static function msg($ok, $str, $before=false, $after=false): void
{
	if ($before) echo '<br>';
	self::badge($ok, $str, $str);
	if ($after) echo '<br>';
}

// NEW: 250710
public static function capture(callable $fn): string
{
	ob_start();
	$fn();
	return ob_get_clean();
}

public static function help($title, $str, $warning = false): void
{
	$id = uniqid('atec_help_');

	echo 
	'<div class="atec-help-popover-wrap">',
		'<button type="button" class="atec-help-toggle ' , ($warning ? 'atec-warning' : '') , '" data-popover="' , esc_attr($id) , '">',
			esc_attr(self::dash_span($warning ? 'editor-help' : 'info', $warning ? 'atec-warning' : '')),
			esc_html($title),
		'</button>',

		'<div id="' , esc_attr($id) , '" class="atec-help-popover-content" hidden>',
			'<div>', wp_kses_post($str), '</div>',
		'</div>',
	'</div>';

	self::inject_popover_script();
}

// NEW: 250710
public static function maybe_help($license_ok, $title, callable $callback): void
{
	if (!$license_ok)
	{
		self::div('box');
			$callback();
		self::div(-1);
	}
	else
	{
		self::help($title, self::capture($callback));
	}
}

protected static function inject_popover_script(): void
{
	static $done = false;
	if ($done) return;
	$done = true;

	self::reg_inline_script('help-popover',
		'document.addEventListener("click", function(e) 
		{
			const toggle = e.target.closest(".atec-help-toggle");
			const isPopover = e.target.closest(".atec-help-popover-content");
			const popovers = document.querySelectorAll(".atec-help-popover-content");

			if (!toggle && !isPopover) 
			{
				popovers.forEach(function(el) { el.hidden = true; });
				return;
			}

			if (toggle) 
			{
				const id = toggle.getAttribute("data-popover");
				const target = document.getElementById(id);
				if (!target) return;

				const isVisible = !target.hidden;
				popovers.forEach(function(el) { el.hidden = true; });
				if (isVisible) return;

				target.hidden = false;
				target.style.left = "0";
				target.style.right = "auto";

				const rect = target.getBoundingClientRect();
				if (rect.right > window.innerWidth - 20) 
				{
					target.style.left = "auto";
					target.style.right = "0";
				}

				e.stopPropagation();
			}
		});'
	);
}

// MSG AREA END

// FORM AREA START

// CHANGED: Form_header($una = []) for options introduced on 250706
// CLEANUP: Replace <form class="atec-form" method="post" action="options.php">
public static function form_header($una = [], $action= '', $nav= '', $id= '', $class= '')
{
	if ($una === [])
	{
		// Form is a settings form -> options.php
		echo '<form class="atec-form" method="post" action="options.php">';
	}
	else
	{
		$href = INIT::build_url($una, $action, $nav, ['id' => $id]);
		echo
		'<form class="atec-form', ($class !== '' ? ' '.esc_attr($class) : ''), '" method="post" action="'.esc_url($href).'">';
			self::form_add_fields($una, $action, $nav, $id);
	}
}

public static function form_footer()
{ echo '</form>'; }

public static function form_add_fields($una, $action = null, $nav = null, $id = null)
{
	$fields = [	'_wpnonce' => $una->nonce, 'action' => $action, 'nav' => $nav, 'id' => $id ];
	foreach ($fields as $name => $value)
		{ if (!is_null($value)) { echo '<input type="hidden" name="', esc_attr($name), '" value="', esc_attr($value), '">'; } }
}

public static function form_fake_check($str)
{
	echo
	'<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">', esc_html($str), '</th>
				<td>
					<div class="atec-ckbx">
						<label class="switch">
							<input type="checkbox" disabled value="0">
							<div class="slider round"></div>
						</label>
					</div>
				</td>
			</tr>
		</tbody>
	</table>';
}

public static function submit_button($button= '', $inline=false)
{
	$button = $button ?: '#editor-break Save';
	$dnb = self::dash_and_button($button);
	if (!$inline) echo '<p>';
	echo '<button id="submit" type="submit" name="submit" class="button button-primary">'; self::dash_and_button_div($dnb); echo '</button>';
	if (!$inline) echo '</p>';
}

public static function safe_redirect($una, $action=null, $nav=null, $args = []): void
{ 
	wp_safe_redirect(INIT::build_url($una, $action, $nav)); 
}

public static function redirect($una, $action=null, $nav=null, $args = []): void
{
	self::reg_inline_script('redirect', 'window.location.assign("'.INIT::build_url($una, $action, $nav, $args).'");');
}

public static function history($slug): void
{
	self::reg_inline_script($slug.'_history','window.history.replaceState({}, "", window.location.pathname + "?page=atec_'.$slug.'");');		// prevent re-saving
}

public static function clean_request_bool($key) : bool
{ return INIT::bool(self::clean_request($key)); }

public static function clean_request( $key, $nonce = '', $type = 'text' )
{
	$nonce_to_check = ( $nonce !== '' ) ? $nonce : INIT::nonce();
	if ( isset( $_POST[ $key ] ) ) 
	{
		if ( ! check_admin_referer( $nonce_to_check ) ) return '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = wp_unslash( $_POST[ $key ] );	// Sanitizing before return 
	}
	elseif ( isset( $_GET[ $key ] ) ) 
	{
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), $nonce_to_check ) ) return '';		
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = wp_unslash( $_GET[ $key ] );		// Sanitizing before return 
	}
	else 
	{
		return '';
	}

	return $type === 'textarea' ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
}


// FORM AREA END

// ENQUEUE style and script

public static function load_atec_style($dir, $styles = [])
{
	static $versions = ['style' => '1.0.3', 'check' => '1.0.3'];
	foreach($styles as $style) 	
	{
		$version = $versions[$style] ?? '1.0.1';
		self::load_style($style, $dir, 'atec-'.$style.'.min.css', $version);
	}
}

public static function load_atec_script($dir, $scripts = [])
{
	static $versions = [ 'check' => '1.0.3' ];
	foreach($scripts as $script)
	{
		$version = $versions[$script] ?? '1.0.1';
		self::load_script($script, $dir, 'atec-'.$script.'.min.js', $version);
	}
}

// ENQUEUE NEW VERSION 250704

public static function prefix_id(&$id): void 
{ $id = 'atec-' . $id; }

public static function load_style($id, $dir, $css, $ver, $deps = []): void
{
	static $cached = null;
	if ($cached === null) $cached = INIT::is_atec_dev_mode() ? '.min' : '';
	if ($cached !== '') $css = str_replace($cached, '', $css);

	self::prefix_id($id);
	$url = INIT::plugin_url_by_dir($dir).'/assets/css/'.$css;

	wp_enqueue_style($id, $url, $deps, $ver);
}

public static function load_script($id, $dir, $js, $ver, $deps = []): void
{
	static $cached = null;
	if ($cached === null) $cached = INIT::is_atec_dev_mode() ? '.min' : '';
	if ($cached !== '') $js = str_replace($cached, '', $js);

	self::prefix_id($id);
	$url = INIT::plugin_url_by_dir($dir).'/assets/js/'.$js;

	wp_enqueue_script($id, $url, $deps, $ver, true);
}

public static function load_script_localized($id, $dir, $js, $ver, $var_name, array $data, $deps = [], $lazy = false): void
{
	static $cached = null;
	if ($cached === null) $cached = INIT::is_atec_dev_mode() ? '.min' : '';
	if ($cached !== '') $js = str_replace($cached, '', $js);

	self::prefix_id($id);
	$url = INIT::plugin_url_by_dir($dir).'/assets/js/'.$js;

	wp_register_script($id, $url, $deps, $ver, true);
	wp_localize_script($id, $var_name, $data);

	if (!$lazy) wp_enqueue_script($id);
}

// Avoid using self::nonce() here – we use explicit $slug to stay context-safe.
public static function load_ajax_script(string $slug, string $dir, string $ver='1.0.1', array $deps = [], bool $lazy = false, array $extraData = []): void
{
	$id				= 'atec-' . $slug . '-ajax-script';
	$var_name	= 'atec_' . $slug . '_ajax';
	$nonce			= INIT::nonce_key($slug);
	$filename		= 'atec-' . $slug . '-ajax.js';

	if (INIT::is_atec_dev_mode()) $filename = str_replace('.js', '.min.js', $filename);

	$data = array_merge([
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce($nonce),
	], $extraData);

	self::load_script_localized($id, $dir, $filename, $ver, $var_name, $data, $deps, $lazy);
}

public static function load_inline_style($id, $css_safe): void
{
	self::prefix_id($id);
	wp_register_style($id, false, [], '1.0.0');
	wp_enqueue_style($id);
	wp_add_inline_style($id, $css_safe);
	self::flush();
}

public static function load_inline_script($id, $js_safe, $jquery = false): void
{
	self::prefix_id($id);
	wp_register_script($id, false, $jquery ? ['jquery'] : [], '1.0.0', true);
	wp_enqueue_script($id);
	wp_add_inline_script($id, $js_safe);
	self::flush();
}

public static function reg_style(...$args)				{ self::load_style(...$args); }
public static function reg_script(...$args)				{ self::load_script(...$args); }
public static function reg_inline_style(...$args)		{ self::load_inline_style(...$args); }
public static function reg_inline_script(...$args)	{ self::load_inline_script(...$args); }

// HEADER AREA START

public static function header($una): bool
{
	static $admin_bar_slugs = ['wpci', 'wpd', 'wpdp'];
	
	$plugin 			= INIT::plugin_by_dir($una->dir);
	$approved 		= \ATEC\GROUP::is_plugin_approved($una->slug);
	$wordpress		= 'https://wordpress.org/support/plugin/';

	$supportLink = 
		$approved
		? $wordpress . $plugin 
		: ($una->slug === 'wpmc' ? 'wpmegacache' : 'atecplugins') . '.com/contact/';

	$version		= wp_cache_get('atec_'.$una->slug.'_version', 'atec_np');
	$license_ok 	= self::pro_banner($una->slug);
	if (is_null(get_option('atec_allow_integrity_check',null))) self::integrity_banner($una->dir);

	echo
	'<div class="atec-header">',
		'<div class="atec-m-auto atec-mb-5 atec-row" style="align-items: center; gap: 5px;">';

			if ($una->slug=== 'wpmc') 
			{
				\ATEC\SVG::echo('wpmc');
				echo 
				'<h1 class="atec-dilb">
					<span style="color:#2340b1;">Mega</span>
					<span style="color:#fe5300; margin-left: -5px;">Cache</span>
				</h1>';
			}
			else
			{
				\ATEC\SVG::echo('wpa');
				echo 
				'<span class="atec-logo-text">atec</span>', 
				'<h1 class="atec-dilb">',
					esc_html(str_replace('atec','',INIT::plugin_fixed_name($plugin))),
				'</h1>';
				\ATEC\SVG::echo($una->slug);
			}
			echo '<div class="atec-fs-10 atec-vab">v', esc_attr($version), '</div>';

		echo
		'</div>';

		echo
		'<div class="atec-row atec-header-box">',

			'<a class="button atec-btn-small" href="', esc_url($supportLink), '" target="_blank">',
				'<span class="', esc_attr(self::dash_class('sos')), '"></span>Plugin support',
			'</a>';
			
			if (in_array($una->slug, $admin_bar_slugs))	// Plugins with admin switch
			{
				$option = INIT::admin_bar_option($una->slug);

				$href = INIT::build_url($una, 'admin_bar', '', ['set' => $option ? 0 : 1]);
				$id = uniqid();
				echo
				'<div class="atec-mini-switch-box" title="Toggle admin bar display.">',
						'<span class="', esc_attr(self::dash_class('dashboard','font-size: 20px;')), '"></span>',
						'<div class="atec-ckbx atec-dilb atec-ckbx-mini">',
							'<label class="switch" for="check_', esc_attr($id), '" onclick="location.href=\'', esc_url($href), '\'">',
								'<input name="check_', esc_attr($id), '" type="checkbox" value="', esc_attr($option), '"', checked($option,true,true), '>',
								'<div class="slider round"></div>',
							'</label>',
						'</div>',
				'</div>';
			}

			if ($approved)
			{
				echo
				'<a class="button atec-btn-small" href="', esc_url($wordpress.$plugin.'/reviews/#new-post'), '" target="_blank">',
					'<span class="', esc_attr(self::dash_class('admin-comments')), '"></span>Post a review',
				'</a>';
			}
			
		echo '
		</div>
		
	</div>';
	self::flush();
	return $license_ok;
}

public static function little_block($str, $class = '', $info= ''): void
{
	$str = str_replace(['<s>', '</s>'], ['<span class="atec-small">', '</span>'], $str);
	echo 
	'<div class="atec-db">
		<div class="atec-dilb atec-head', ($class !== '' ? ' '.esc_attr($class) : ''), '">',
			'<h3 class="atec dilb">', wp_kses_post($str), '</h3>',
		'</div>'; 
		if ($info!== '')
		{
			echo '<div class="atec-dilb atec-ml-10">';
				self::p_info($info);
			echo '</div>';
		}
	echo
	'</div>'; 
}

public static function little_block_multi($una, $str, $abpArr, $nav= '', $infoArr=[]): void
{
	echo
	'<div class="" style="padding-bottom:0;">',
	
		'<div class="atec-dilb atec-mr-10 atec-head"><h3>', esc_html($str), '</h3></div>';

		if (!empty($abpArr))
		{
			foreach($abpArr as $key => $value)
			{
				echo '<div class="atec-dilb atec-vat atec-ml-10">'; 
					self::button($una, $key, $nav, $value); 
				echo '</div>';
			}
		}

		if (!empty($infoArr))
		{
			echo
			'<div class="atec-dilb atec-vat atec-float-right">';
				foreach ($infoArr as $key => $value)
				{
					echo
					'<div class="atec-dilb atec-border-tiny atec-ml-5 atec-bg-w85 atec-nowrap" style="padding: 5px 5px 2px 5px;">';
						self::dash_and_button_div(self::dash_and_button($key),'atec-bold');
						echo '<span class="atec-dilb atec-mr-5">:</span>';
						self::dash_and_button_div(self::dash_and_button($value));
					echo
					'</div>';
				}
			echo
			'</div>';
		}

	echo
	'</div>';
}

public static function active_no_cfg($str, $ok=true, $noCfg=true): void
{
	$bg_color 	= $ok ? 'var(--bg-success)' : 'var(--bg-error)';
	$border		= $ok ? 'var(--border-success)' : 'var(--border-error)';
	$color_class	= $ok ? 'green' : 'red';
	echo
	'<div class="atec-badge atec-box-nocfg atec-db" style="background:', esc_attr($bg_color), '; border: ', esc_attr($border), ';">
		<div class="atec-mb-5 atec-fs-16">'; 
			self::dash_span('plugins-checked', 'atec-'.$color_class); 
			echo ' atec <b>Just Works</b> Series<span class="atec-fs-14">', ($noCfg ? ' · No configuration required — it just works.' : ''), '</span>',
		'</div>
		<div class="atec-', esc_attr($color_class), '">', esc_html($str), '.</div>
	</div>';
}

public static function page_header($una, $break=999, $about=false, $update=false, $debug=false)
{
	echo
	'<div class="atec-page">';
		$license_ok = self::header($una);

		//if ($break<0) $break = $license_ok ? 999 : abs($break);

		echo
		'<div class="atec-main">';
			self::nav_tab($una, $break, $license_ok, $about, $update, $debug);

			echo
			'<div class="atec-g atec-border">';
				self::flush();

				$short_circuit = false;
				switch ($una->nav)
				{
					case 'Info':
						\ATEC\INFO::init($una);
						$short_circuit = true;
						break;
						
					case 'License':
						\ATEC\LICENSE::init($una);
						$short_circuit = true;
						break;
				}
			
				if ($short_circuit) 
				{
					self::page_footer();
					return null;
				}

	return $license_ok;
}

public static function page_footer($slug= ''): void
{
			echo
			'</div>
		</div>
	</div>';
	self::footer($slug= '');
	self::flush();
}

// HEADER AREA END

// FOOTER AREA START

public static function footer($slug= '')
{
	$loadTime	= round((microtime(true) - INIT::_SERVER('REQUEST_TIME_FLOAT'))*1000);	// phpcs:ignore
	$domain		= $slug=== 'wpmc' ? 'wpmegacache' : 'atecplugins';
	$href			= INIT::admin_url('group');
	echo
	'<div class="atec-footer atec-center atec-fs-12">
		<span class="atec-ml-10" style="float:left;">
			⏱️ <span>', esc_attr($loadTime), '</span> <span class="atec-fs-10">ms</span>';
			if ($domain=== 'atecplugins') echo '&middot; <a class="atec-nodeco" href="', esc_url($href), '">atec-Plugins</a>';
			echo '
		</span>
		<span class="atec-dilb atec-float-right atec-mr-10">
			© 2023/', esc_html(gmdate('y')), ' <a href="https://', esc_attr($domain), '.com/" target="_blank" class="atec-nodeco">', esc_attr($domain), '.com</a>
		</span>
	</div>';
}

// FOOTER AREA START

}
?>