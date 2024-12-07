<?php
if (!defined( 'ABSPATH' )) { exit; }

	atec_help('opcache',__('Recommended settings','atec-cache-apcu'));
	echo '
	<div id="opcache_help" class="atec-help">
		<p class="atec-bold atec-mb-5 atec-mt-0">', esc_attr__('Recommended settings','atec-cache-apcu'), ':</p>
		<ul class="atec-m-0">
			<li>opcache.enable=1</li>
			<li>opcache.memory_consumption=128</li>
			<li>opcache.interned_strings_buffer=8</li>
			<li>opcache.max_accelerated_files=10000</li>
			<li>opcache.validate_timestamps=1</li>
			<li>opcache.revalidate_freq=60</li>
			<li>opcache.consistency_checks=0</li>
			<li>opcache.save_comments=0</li>
			<li>opcache.enable_file_override=1</li>
		</ul>
		A revalidate_freq of 0 will result in OPcache checking for updates on every request.
	</div>';	
?>