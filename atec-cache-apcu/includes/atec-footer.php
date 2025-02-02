<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_footer { function __construct() {	

global $timestart;

$plugin			= atec_get_plugin(__DIR__);
$mega			= !str_starts_with($plugin,'atec-');
$domain		= $mega?'wpmegacache.com':'atecplugins.com';

echo '
<div class="atec-footer atec-center atec-fs-12">
	<span class="atec-ml-10" style="float:left;">
		<span class="atec-fs-12" title="', esc_attr__('Execution time','atec-cache-apcu'), '">
			<span class="atec-fs-12" class="',esc_attr(atec_dash_class('clock')), '"></span> ', 
			esc_attr(intval((microtime(true) - $timestart)*1000)), 
			' <span class="atec-fs-10">ms</span>
		</span>';
		if (!$mega) echo '&middot; <a class="atec-nodeco" href="',esc_url(get_admin_url().'admin.php?page=atec_group'),'">atec-',  esc_attr__('plugins','atec-cache-apcu'), ' – ', esc_attr__('Group','atec-cache-apcu'), '</a>';
		echo '
	</span>
	<span style="width: fit-content;" class="atec-dilb atec-right atec-mr-10">
		© 2023/25 <a href="https://', esc_attr($domain), '/" target="_blank" class="atec-nodeco">', esc_attr($domain), '</a>
	</span>
</div>';

atec_reg_inline_script('footer','
jQuery(".atec-progressBar").css("background","transparent");
jQuery("#footer-upgrade").html("PHP: '.esc_attr(phpversion()).' | WP: '.esc_attr(get_bloginfo('version')).'");', true);
}}

new ATEC_footer();
?>
