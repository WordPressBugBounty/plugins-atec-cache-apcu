<?php
if (!defined('ABSPATH')) { exit(); }

(function() {

	global $timestart;
	$isMega	= str_contains(__DIR__,'mega-cache');
	$isCT 	= !$isMega && str_contains(__DIR__,'cache-tune');
	$domain	= $isMega?'wpmegacache.com':($isCT?'cachetune.com':'atecplugins.com');
	
	echo '
	<div class="atec-footer atec-center atec-fs-12">
		<span class="atec-ml-10" style="float:left;">
			<span class="atec-fs-12" title="', esc_html__('Execution time','atec-cache-apcu'), '">
				<span class="atec-fs-12 ',esc_attr(atec_dash_class('clock')), '"></span> ', esc_attr(intval((microtime(true) - $timestart)*1000)), ' <span class="atec-fs-10">ms</span>
			</span>';
			if (!$isMega && !$isCT) echo '&middot; <a class="atec-nodeco" href="',esc_url(get_admin_url().'admin.php?page=atec_group'),'">atec-',  esc_attr__('plugins','atec-cache-apcu'), ' – ', esc_attr__('Group','atec-cache-apcu'), '</a>';
			echo '
		</span>
		<span style="width: fit-content;" class="atec-dilb  atec-float-right atec-mr-10">
			© 2023/', esc_html(gmdate('y')), ' <a href="https://', esc_attr($domain), '/" target="_blank" class="atec-nodeco">', esc_attr($domain), '</a>
		</span>
	</div>';
	
	atec_reg_inline_script('footer','
	jQuery(".atec-progressBar").css("background","transparent");
	$wpFooter=jQuery("#footer-upgrade"); $wpFooter.html("PHP: '.esc_attr(phpversion()).' | WP: "+$wpFooter.html().replace("Version",""));', true);

})();
?>