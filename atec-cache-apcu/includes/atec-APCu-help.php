<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;

echo 
'<div class="atec-mt-5">';

	TOOLS::help(__('Recommended settings', 'atec-cache-apcu'),
		'<p class="atec-bold atec-mb-5 atec-mt-0">', esc_attr__('Recommended settings', 'atec-cache-apcu'), ':</p>
		<ul class="atec-m-0">
			<li>apc.enable=1</li>
			<li>apc.shm_size=32M</li>
		</ul>
		If you want to use the page cache features, increase the `shm_size’ accordingly.');
	
echo 
'</div>';
?>