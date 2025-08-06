<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

final class ALIAS
{

// NEW: introduced 250710 | CLEANUP: change ALIAS -> TOOLS
public static function dash_yes_no($yes) 
{ return '<span class="'.TOOLS::dash_class($yes?'yes-alt' : 'dismiss', 'atec-'.($yes?'green' : 'red')).'"></span>'; }

// NEW: introduced 250710 | CLEANUP: change ALIAS -> TOOLS
public static function tr(...$args)
{ return TOOLS::table_tr(...$args); }

// NEW: introduced 250710 | CLEANUP: change ALIAS -> TOOLS
public static function td(...$args)
{ return TOOLS::table_td(...$args); }

public static function steps($dir)
{
	$img_dir = dirname($dir) . '/assets/img/';
	$files = glob($img_dir . 'step_*.webp'); 
	if (empty($files)) return;
	natsort($files);
	$url = INIT::plugin_url_by_dir($dir) . '/assets/img/';
	TOOLS::p_title('Step-by-Step Preview');
	echo '<div class="atec-row atec-steps-row">';
		foreach ($files as $file) 
		{
			$filename = basename($file);
			$basename = pathinfo($filename, PATHINFO_FILENAME);
			$caption_file = $img_dir . $basename . '.txt';
			$caption = file_exists($caption_file) ? trim(file_get_contents($caption_file)) : '';
	
			echo '<div>';
				echo '<img src="' . esc_url($url . $filename) . '" title="Step by Step: ' . esc_attr($filename) . '" loading="lazy">'; // phpcs:ignore
				if ($caption !== '') echo '<div class="atec-step-caption" style="">' . esc_html($caption) . '</div>';
			echo '</div>';
		}
	echo '</div>

	<div id="atec-lightbox" title="Click to close" role="dialog" aria-label="Image preview. Click to close."><img src=""></div>';

	static $injected = false;
	if (!$injected)
	{
		$injected = true;
		TOOLS::load_inline_script('lighbox', '
			jQuery(function($) {
				$(".atec-steps-row img").css("cursor", "zoom-in").on("click", function() {
					$("#atec-lightbox img").attr("src", $(this).attr("src"));
					$("#atec-lightbox").css("display", "flex").hide().fadeIn(150);
				});
				$("#atec-lightbox").on("click", function() { $(this).fadeOut(150); });
				$(document).on("keydown", function(e) { if (e.key === "Escape") $("#atec-lightbox").fadeOut(150); });
			});
		', true);
	}
}

}
?>