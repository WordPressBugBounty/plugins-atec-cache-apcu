<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;
use ATEC\WPCA;

(function() {

	$una = TOOLS::una(__DIR__, 'Settings');
	TOOLS::add_nav($una, true, '#archive Cache');

	if (WPCA::apcu_enabled())
	{
		TOOLS::add_nav($una, true, '#memory APCu');
		TOOLS::add_nav($una, WPCA::settings('p_cache'), '#blog Page Cache');
	}
	if (WPCA::settings('o_rest')) TOOLS::add_nav($una, true, '#php APCu Compatibility Check');

	if (is_null( $license_ok = TOOLS::page_header($una, 999, false, false, defined('ATEC_OC_ACTIVE_APCU')) )) return;

		switch ($una->nav)
		{
			case 'Settings': 
				TOOLS::lazy_require(__DIR__, 'atec-wpca-settings.php', $una, $license_ok); 
				break;
				
			case 'Cache': 
				TOOLS::lazy_require(__DIR__, 'atec-wpca-cpanel.php', $una); 
				break;
				
			case 'APCu': 
				TOOLS::lazy_require(__DIR__, 'atec-wpca-groups.php', $una); 
				break;
				
			case 'Page_Cache': 
				TOOLS::lazy_require(__DIR__, 'atec-wpca-pcache-stats.php', $una); 
				break;
				
			case 'Debug': 
				TOOLS::lazy_require_class(__DIR__, 'atec-wpca-wpc-groups.php', 'ATEC_WPCA\\Groups', $una); 
				break;

			case 'APCu_Compatibility_Check': 
				TOOLS::lazy_require(__DIR__, 'atec-wpca-rest-client.php', $una); 
				break;
		}
			
	TOOLS::page_footer();

})();
?>