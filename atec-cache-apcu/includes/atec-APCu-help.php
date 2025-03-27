<?php
if (!defined('ABSPATH')) { exit; }

atec_help('apcu',__('Recommended settings','atec-cache-apcu'));
echo '
<div id="apcu_help" class="atec-help">
	<p class="atec-bold atec-mb-5 atec-mt-0">', esc_attr__('Recommended settings','atec-cache-apcu'), ':</p>
	<ul class="atec-m-0">
		<li>apc.enable=1</li>
		<li>apc.shm_size=32M</li>
	</ul>
	If you want to use the page cache features, increase the `shm_size’ accordingly.
</div>';	
?>