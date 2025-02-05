<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_info { function __construct($dir,$url=null,$nonce=null) {

global $wp_filesystem; WP_Filesystem();

$iconPath 		= plugins_url('assets/img/atec-group/',__DIR__).atec_get_slug().'_icon.svg';
$readmePath	= plugin_dir_path($dir).'readme.txt';
$readme			= $wp_filesystem->get_contents($readmePath);

atec_little_block('Info'); 

echo 
'<div id="readme" class="atec-mt-10 atec-mb-0 atec-border atec-bg-w6 atec-anywrap" style="font-size: 1.125em; max-width: 100%; padding: 20px 20px 0 20px;">';

if (!$readme) echo '<p class="atec-red">Can not read the readme.txt file.</p>';
else
{
	preg_match('/===(\s+)(.*)(\s+)===\n/', $readme, $matches);

	$readme = preg_replace('/== Installation(.*)/sm', '', $readme);
	$readme = preg_replace('/Contributors(.*)html\n/sm', '', $readme);
	$readme = preg_replace('/===(\s+)(.*)(\s+)===\n/', '', $readme);
	$readme = preg_replace('/==(\s+)(.*)(\s+)==\n/', "<strong>$2</strong><br>", $readme);

	// @codingStandardsIgnoreStart
	// Image is not an attachement
	echo 
	'<div class="atec-db atec-m-0">',
		'<div class="atec-dilb atec-vat"><img style="height: 30px;" class="atec-vat nav-icon" src="', esc_url($iconPath), '"></div>',
		'<div class="atec-dilb atec-vat atec-fs-16 atec-bold">', esc_attr(trim($matches[2])), '</div>',
	'</div>';
	// @codingStandardsIgnoreEnd
	echo '<p class="atec-m-0">', esc_html($readme), '</p>';
	atec_reg_inline_script('readme','readme=jQuery("#readme"); html=readme.html(); html = html.replaceAll("&lt;", "<"); html = html.replaceAll("&gt;", ">"); readme.html(html);', true);
}
echo 
'</div>';

}}
?>