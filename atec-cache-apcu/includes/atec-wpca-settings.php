<?php
defined('ABSPATH') || exit;

use ATEC\CPANEL;
use ATEC\CHECK;
use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPC;
use ATEC\WPCA;

return function($una, $license_ok)
{
	$option_key= 'atec_WPCA_settings';

	$salt = WPCA::settings('salt');

	$info = [];
	if (defined('ATEC_OC_VERSION')) $info['OC'] = ATEC_OC_VERSION;
	if (defined('ATEC_OC_KEY_SALT')) $info['OC 🧂'] = ATEC_OC_KEY_SALT;
	$info['PC 🧂'] = $salt !== '' ? $salt : '-/-';
	$info['Zlib']  = INIT::bool(ini_get('zlib.output_compression'));

	if (!defined('AUTH_KEY')) TOOLS::msg(false, 'AUTH_KEY is not defined but required. Please fix the ‘keys and salts’ section in your ‘wp-config.php’');

	$o_cache = WPCA::settings('o_cache');
	$p_cache = WPCA::settings('p_cache');

	if ($o_cache!==defined('ATEC_OC_ACTIVE_APCU'))
	{
		$error = 'OC is '.(defined('ATEC_OC_ACTIVE_APCU') ? 'active' : 'not active');
		TOOLS::msg(false, 'The cache settings are inconsistent ('.$error.').<br>Please save again to auto-fix it');
	}

	CPANEL::cpanel_header($una, __('Settings', 'atec-cache-apcu'), [], '', $info);

	TOOLS::div('border-g');

	echo '<div class="atec-g atec-g-50 atec-wpca-settings-cols"><div>';

			TOOLS::little_block('OC '.__('Settings', 'atec-cache-apcu'));

			TOOLS::div('border', '', 'atec-fit');

				if (WPCA::apcu_enabled())
				{
					echo '<div class="atec-row" style="gap:0;">';
					TOOLS::badge(defined('ATEC_OC_ACTIVE_APCU'), __('Object Cache', 'atec-cache-apcu').'#'.__('is active', 'atec-cache-apcu'), __('is inactive', 'atec-cache-apcu'));
						if ($o_cache && $license_ok) TOOLS::msg(true, 'Advanced');
					echo '</div>';

					echo
					'<hr class="atec-mb-10">
					<div class="atec-custom-form">
						<form class="atec-form atec-mt-10" method="post" action="options.php">
							<input type="hidden" name="atec_WPCA_settings[salt]" value="', esc_attr($salt), '">';
							$slug = 'atec_WPCA';
							settings_fields($slug);
							do_settings_sections($slug);
					TOOLS::div(-1);
					
					echo '<div class="atec-help-toggle atec-fit atec-p-10" id="atec-wpca-status"></div>';
					TOOLS::submit_button('#editor-break '.__('Save', 'atec-cache-apcu'));

					TOOLS::div('row', '', 'atec-wpca-col-footer atec-mt-10');
					
						TOOLS::help(__('Object Cache', 'atec-cache-apcu'),
							__('The object cache is the main feature of the plugin and will speed up your site', 'atec-cache-apcu').'.');
						// NEEDS translation
						TOOLS::help(__('‘PRO’ AOC Mode (Advanced Object Cache)', 'atec-cache-apcu'),
							__('Improves performance by optimizing autoloaded options and internal caching behavior. Recommended for high-traffic sites.', 'atec-cache-apcu').'.');
						TOOLS::help(
							__('APCu Compatibility Check', 'atec-cache-apcu'),
							__('Runs a short test to see if APCu object caching is safe on this server (checks for multiple PHP workers and shared APCu memory). You can disable this after testing.', 'atec-cache-apcu')
						);
						
					TOOLS::div(-1);
				}
				else TOOLS::msg(false, 'APCu '.__('extension is NOT installed/enabled', 'atec-cache-apcu'));

			TOOLS::div(-1);

			TOOLS::tr();

			if (!$license_ok) 
			{
				TOOLS::pro_feature($una, ' - '.__('this will enable the advanced', 'atec-cache-apcu').' '.__('object cache', 'atec-cache-apcu'), true);
				echo '<br class="atec-mb-20">';
			}

		TOOLS::div(0);

			TOOLS::little_block('PC '.__('Settings', 'atec-cache-apcu'));

			TOOLS::div('border', '', 'atec-fit');

				if (WPCA::apcu_enabled())
				{
					echo '<div class="atec-row" style="gap:0;">';
						TOOLS::badge($p_cache, __('Page Cache', 'atec-cache-apcu').'#'.__('is active', 'atec-cache-apcu'), __('is inactive', 'atec-cache-apcu'));
						if (defined('ATEC_ADV_PC_ACTIVE_APCU')) TOOLS::msg(true, 'Advanced');
					echo '</div>';

					echo
					'<hr class="atec-mb-10">
					<div class="atec-form atec-custom-form atec-mb-0">
						<table class="form-table" role="presentation">
							<tbody>
								<tr>
									<th scope="row">', esc_html__('Page Cache', 'atec-cache-apcu'), '</th>
									<td>'; CHECK::checkbox(['opt-name' => $option_key, 'name' => 'p_cache', 'value' => $p_cache]); echo '</td>
								</tr>';
						TOOLS::table_footer();

						if ($p_cache)
						{
							echo
							'<h2><small>', esc_html__('Options', 'atec-cache-apcu'), '</small></h2>
							<table class="form-table" role="presentation">
								<tbody>
									<tr>
										<th scope="row">', esc_html__('Admin bar ‘PC Flush’ icon', 'atec-cache-apcu'), '</th>
										<td>'; CHECK::checkbox(['opt-name' => $option_key, 'name' => 'p_admin', 'value' => WPCA::settings('p_admin')]); echo '</td>
									</tr>';
							TOOLS::table_footer();
						}

					TOOLS::div(-1);
					echo '<div class="atec-wpca-status-spacer" aria-hidden="true"></div>';
					TOOLS::submit_button('#editor-break '.__('Save', 'atec-cache-apcu'));

					TOOLS::div('row', '', 'atec-wpca-col-footer atec-mt-10');
					
						TOOLS::help(__('Page Cache', 'atec-cache-apcu'),
							__('The page cache is an additional feature of this plugin', 'atec-cache-apcu').'. '.
							__('It will give your page an additonal boost, by delivering pages from APCu cache', 'atec-cache-apcu').'. '.
							__('The page cache saves pages, posts and categories – no product/shop pages (WooCommerce)', 'atec-cache-apcu').'.');
						// NEEDS translation
						TOOLS::help(__('‘PRO’ APC Mode (Advanced Page Cache)', 'atec-cache-apcu'),
							__('Serves full page HTML earlier than standard caching for maximum performance', 'atec-cache-apcu').'.');
						TOOLS::help(__('Multiple PC plugins', 'atec-cache-apcu'),
							__('Do not use multiple page cache plugins simultaneously', 'atec-cache-apcu').'.', true);
						if (is_multisite()) TOOLS::msg('warning', __('The page cache is not designed to support multisites', 'atec-cache-apcu').'.<br>'.__('Please try the ‘Mega-Cache’-Plugin for multisites', 'atec-cache-apcu'), true);
						
					TOOLS::div(-1);
				}
				else TOOLS::msg(false, 'APCu '.__('extension is NOT installed/enabled', 'atec-cache-apcu'));

				TOOLS::form_footer();

			TOOLS::div(-1);

				TOOLS::tr();

				if (!$license_ok) 
				{
					TOOLS::pro_feature($una, ' - '.__('this will enable the advanced', 'atec-cache-apcu').' '.__('page cache', 'atec-cache-apcu').' '.__(' and remove the footer attribution', 'atec-cache-apcu'), true);
					echo '<br class="atec-mb-20">';
				}

				if ($msg = WPC::pcache_detected()) TOOLS::msg('warning', '⚠️ '.$msg);

	TOOLS::div(-2);

	TOOLS::div(-1);

	TOOLS::reg_inline_style('wpca_settings',
	'.atec-wpca-settings-cols { align-items: stretch; }
	.atec-wpca-settings-cols > div { display: flex; flex-direction: column; }
	.atec-wpca-settings-cols .atec-border-white { flex: 1; display: flex; flex-direction: column; width: 100% !important; }
	.atec-wpca-settings-cols .atec-custom-form { min-height: 185px; flex: 1; }
	.atec-wpca-settings-cols .atec-wpca-col-footer { margin-top: auto; }
	.atec-wpca-settings-cols #atec-wpca-status,
	.atec-wpca-settings-cols .atec-wpca-status-spacer { min-height: 2.4em; }
	p.submit { margin-top: 10px !important; }
	#atec-wpca-status .atec-compat-emoji { margin: 0 0.35em; }
	');

	echo
	'<script>
	(function(){
	const el = document.getElementById("atec-wpca-status");
	if(!el) return;

	const label = "APCu Compatibility Check";
	function formatVerdict(verdict) {
		const raw = String(verdict || "unknown").toLowerCase()
			.replace(/🟢|🟡|🔴|🟠/g, "")
			.trim()
			.replace(/\s+/g, "_");
		const map = {
			safe: ["🟢", "SAFE"],
			not_safe: ["🔴", "NOT SAFE"],
			inconclusive: ["🟡", "INCONCLUSIVE"],
			unknown: ["🟠", "UNKNOWN"]
		};
		const pair = map[raw] || map.unknown;
		return \'<span class="atec-compat-emoji">\' + pair[0] + \'</span> \' + pair[1];
	}
	function setStatus(html) {
		el.innerHTML = label + ": " + html;
	}
	try {
		const s = localStorage.getItem("atec_wpca_worker_test");
		if(!s){ setStatus(\'<span class="atec-compat-emoji">🟠</span> not tested yet.\'); return; }
		const r = JSON.parse(s);
		setStatus(formatVerdict(r.verdict));
	} catch(e){
		setStatus(\'<span class="atec-compat-emoji">🟠</span> not tested yet.\');
	}
	})();
	</script>';

}
?>
