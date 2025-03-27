<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_info { 
	
private static function atec_parse_readme_faq($readme) {
	if (!preg_match('/^== Frequently Asked Questions ==\s*(.*?)^\s*==/sm', $readme . "\n==", $matches)) { return ''; }
	$faq_raw = trim($matches[1]);
	preg_match_all('/^= (.+?) =\s*\n(.*?)(?=^= |\z)/sm', $faq_raw, $qna_matches, PREG_SET_ORDER);
	if (empty($qna_matches)) return '';
	$output = '<div class="readme-faq">';
	foreach ($qna_matches as $qna) {
		$question = trim($qna[1]);
		$answer   = nl2br(esc_html(trim($qna[2])));

		$output .= '<div class="faq-item">';
		$output .= '<div class="faq-question">' . esc_html($question) . '</div>';
		$output .= '<div class="faq-answer">' . $answer . '</div>';
		$output .= '</div>';
	}
	$output .= '</div>';
	return $output;
}

function __construct($dir,$url=null,$nonce=null) {

if (!class_exists('ATEC_fs')) @require('atec-fs.php');

$iconPath = plugins_url('assets/img/atec-group/',$dir).atec_get_slug().'_icon.svg';
$readme 	= (new ATEC_fs)->get(plugin_dir_path($dir).'readme.txt');

atec_little_block('Info'); 

echo
'<style>

</style>';

atec_reg_inline_style('info', 
'.readme-content .faq-item { margin-bottom: 1.5em; padding: 0.5em; border-left: 3px solid #0073aa; background: #f9f9f9; border-radius: 3px; }
.readme-content .faq-question { font-weight: 600; font-size: 1em; margin-bottom: 0.25em; color: #23282d; }
.readme-content .faq-answer { margin: 0; color: #444; line-height: 1.6; }');

echo 
'<div id="readme" class="atec-mt-10 atec-box-white atec-anywrap" style="font-size: 1.125em; max-width: 100%; padding: 20px;">';

	if (!$readme) echo '<p class="atec-red">Can not read the readme.txt file.</p>';
	else
	{
		preg_match('/^===\s*(.*?)\s*===/m', $readme, $matches);
		$pluginName = trim($matches[1]??'');
		$faq = self::atec_parse_readme_faq($readme);
		$readme = preg_replace('/Contributors(.*)gpl-2\.0\.html\n/sm', '', $readme);
		$readme = preg_replace('/== Installation ==.*/sm', '', $readme);
		$readme = preg_replace('/==(\s+)(.*)(\s+)==\n/', "<strong style=\"font-size: 1.2em;\">$2</strong><br>", $readme);
		$readme = preg_replace('/===(\s+)(.*)(\s+)===\n/', '', $readme);
		$readme= ltrim($readme,"\n");
		
		// @codingStandardsIgnoreStart | Image is not an attachement
		echo 
		'<div class="atec-db atec-mb-10">',
			'<div class="atec-dilb atec-vat"><img style="height: 30px;" class="atec-vat nav-icon" src="', esc_url($iconPath), '"></div>&nbsp;&nbsp;',
			'<div class="atec-dilb atec-vat atec-bold atec-mb-0" style="font-size: 1.3em;">', esc_attr($pluginName), '</div>',
		'</div><br>';
		// @codingStandardsIgnoreEnd
		echo 
		'<div class="readme-content"><p class="atec-m-0">', wp_kses_post($readme), '</p>', (empty($faq)?'':wp_kses_post($faq)), '</div>';
	}
	
echo 
'</div>';

}}
?>