<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;

final class CPANEL
{

/** @var array<string,callable> Tab-keyed instruction renderers — owned here, not in TOOLS (mixed-env safe). */
private static array $instructions = [];

/** Load atec-{slug}-instructions.php from the plugin includes dir ($una from TOOLS::una). */
public static function instructions_load(string $dir, object $una): void
{
	$file = $dir . '/atec-' . strtolower($una->slug) . '-instructions.php';
	if (!is_readable($file)) return;
	self::$instructions = require $file;
}

private static function instructions_render(string $tab): void
{
	if (!isset(self::$instructions[$tab])) return;
	(self::$instructions[$tab])();
}

private static function cpanel_info_pills(array $infoArr): void
{
	foreach ($infoArr as $key => $value)
	{
		echo '<div class="atec-dilb atec-border-tiny atec-ml-5 atec-bg-w85 atec-nowrap" style="padding: 5px 5px 2px 5px;">';
			self::dash_and_button_div(self::dash_and_button($key), 'atec-bold');
			echo '<span class="atec-dilb atec-mr-5">:</span>';
			self::dash_and_button_div(self::dash_and_button($value));
		echo '</div>';
	}
}

/**
 * Tab header — title chip + actions left, info + Instructions right.
 * Reuses TOOLS::little_block_multi for title/buttons.
 */
public static function cpanel_header($una, string $title, array $buttons = [], string $nav = '', array $info = []): void
{
	$tab = $una->nav;
	$has_help = isset(self::$instructions[$tab]);

	echo '<div class="atec-cpanel-header">';
		echo '<div class="atec-gap atec-full" style="flex-wrap:wrap">';
			TOOLS::little_block_multi($una, $title, $buttons, $nav, []);
			TOOLS::loader_dots(15);
			if (!empty($info) || $has_help) {
				echo '<div class="atec-gap atec-row-right">';
					if (!empty($info)) self::cpanel_info_pills($info);
					if ($has_help) {
						TOOLS::help('Instructions', TOOLS::capture(static function() use ($tab) {
							self::instructions_render($tab);
						}), false, '', 'atec-cpanel-instructions');
					}
				echo '</div>';
			}
		echo '</div>';
	echo '</div>';
	TOOLS::flush();
}

public static function pro_more($una, $str=''): void
{ 
	TOOLS::pro_block($una);
	echo
	'<div class="atec-box-white atec-pro-desc">
		<p><b>What it does: </b>', wp_kses_post(INIT::trailingdotit($str)),
			'<button type="button" class="button-link atec-open-instructions atec-ml-10">[MORE]</button>
		</p>
	</div>';
	self::inject_instructions_open_script();
	TOOLS::clear();
}

public static function inject_instructions_open_script(): void
{
	static $done = false;
	if ($done) return;
	$done = true;

	TOOLS::reg_inline_script('open-instructions',
		'document.addEventListener("click", function(e) {
			var btn = e.target.closest(".atec-open-instructions");
			if (!btn) return;
			var help = document.getElementById("atec-cpanel-instructions");
			if (!help) return;
			e.preventDefault();
			e.stopPropagation();
			help.click();
		});'
	);
}

// ======  ======  ======  ======  ======  ======  ======  ======  ======

// These two functions are already PUBLIC in TOOLS, so can we removed and adjusted in cpanel_info_pills() once TOOLS is rolled out
public static function dash_and_button($dnb): object
{
	if (!is_string($dnb)) return (object) ['dash' => '', 'button' => $dnb];
	preg_match('/#([\w\-]+)\s?(.*)/i', $dnb, $matches);
	$dash = $matches[1] ?? '';
	$button = isset($matches[2]) ? trim($matches[2]) : $dnb;
	return (object) ['dash' => $dash, 'button' => $button];
}

public static function dash_and_button_div($dnb, string $class = ''): void
{
	if (!empty($dnb->dash))
	{
		TOOLS::dash_span($dnb->dash);
		if ($dnb->button === '') return;
	}

	if (isset($dnb->button))
	{
		if (is_string($dnb->button) && $dnb->button !== '0' && $dnb->button !== '1' && $dnb->button !== '')
		{
			echo '<span', ($class !== '' ? ' class="'.esc_attr($class).'"' : ''), '>', esc_html($dnb->button), '</span>';
		}
		elseif ($dnb->button === true || $dnb->button === 1 || $dnb->button === '1') { TOOLS::dash_span('yes-alt', 'atec-green'); }
		else TOOLS::dash_span('dismiss', 'atec-red');
	}
}

public static function enabled_dot($enabled): string
{
	return
	'<span style="color:'. ($enabled ? 'green' : 'red'). '" title="'. ($enabled ? 'Enabled' : 'Disabled'). '" '.
		'class="'. esc_attr(TOOLS::dash_class($enabled?'yes-alt':'dismiss')). ' atec-vac">'.
	'</span>';
}

}
?>
