<?php
defined('ABSPATH') || exit;

use ATEC\CHECK;
use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPC;
use ATEC\WPCA;

return function($una, $license_ok) 
{
	$option_key= 'atec_WPCA_settings';

	$arr = [];
	if (defined('ATEC_OC_VERSION')) $arr['OC'] = ATEC_OC_VERSION;
	if (defined('ATEC_OC_KEY_SALT')) $arr['OC 🧂'] = ATEC_OC_KEY_SALT;

	$salt = WPCA::settings('salt');
	$arr['PC 🧂'] = $salt!=='' ? $salt : '-/-';
	$arr['Zlib'] = INIT::bool(ini_get('zlib.output_compression'));
	TOOLS::little_block_multi($una, 'APCu - '.__('Settings', 'atec-cache-apcu'), [], '', $arr);

	if (!defined('AUTH_KEY')) TOOLS::msg(false, 'AUTH_KEY is not defined but required. Please fix the „keys and salts“ section in your „wp-config.php“');

	$o_cache = WPCA::settings('o_cache');
	$p_cache = WPCA::settings('p_cache');

	if ($o_cache!==defined('ATEC_OC_ACTIVE_APCU')) 
	{
		$error = 'OC is '.(defined('ATEC_OC_ACTIVE_APCU') ? 'active' : 'not active');
		TOOLS::msg(false, 'The cache settings are inconsistent ('.$error.').<br>Please save again to auto-fix it');
	}

	echo
	'<div class="atec-g atec-g-50">
		<div>
		
			<div class="atec-border-white">
				<h4>APCu ', esc_attr__('Object Cache', 'atec-cache-apcu'), ' '; TOOLS::enabled($o_cache); echo '</h4>';

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
				}
				else TOOLS::msg(false, 'APCu '.__('extension is NOT installed/enabled', 'atec-cache-apcu'));

				echo
				'</div>';
				TOOLS::submit_button('#editor-break '.__('Save', 'atec-cache-apcu'));
				echo
				'<hr>';

				if (!$license_ok) 
				{
					TOOLS::pro_feature($una, ' - '.__('this will enable the advanced', 'atec-cache-apcu').' '.__('object cache', 'atec-cache-apcu'), true);
					echo '<br class="atec-mb-20">';
				}

				echo 
				'<div class="atec-row">';
				
					TOOLS::help(__('Object Cache', 'atec-cache-apcu'),
						__('The object cache is the main feature of the plugin and will speed up your site', 'atec-cache-apcu').'.');
	
					// NEEDS translation
					TOOLS::help(__('„PRO“ AOC Mode (Advanced Object Cache)', 'atec-cache-apcu'),
						__('Improves performance by optimizing autoloaded options and internal caching behavior. Recommended for high-traffic sites.', 'atec-cache-apcu').'.');
						
				echo
				'</div>';

			echo
			'</div>
			
		</div>

		<div>
		
			<div class="atec-border-white">
				<h4>', esc_attr__('APCu Page Cache', 'atec-cache-apcu'), ' '; TOOLS::enabled($p_cache); echo '</h4>';

				if (WPCA::apcu_enabled())
				{
					echo '<div class="atec-row" style="gap:0;">';
						TOOLS::badge($p_cache,__('Page Cache', 'atec-cache-apcu').'#'.__('is active', 'atec-cache-apcu'), __('is inactive', 'atec-cache-apcu'));
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
										<th scope="row">', esc_html__('Admin bar „PC Flush“ icon', 'atec-cache-apcu'), '</th>
										<td>'; CHECK::checkbox(['opt-name' => $option_key, 'name' => 'p_admin', 'value' => WPCA::settings('p_admin')]); echo '</td>
									</tr>';
									echo
									'<tr>
										<th scope="row">', esc_html__('Show debug', 'atec-cache-apcu'), '<br><span style="font-size:80%; color:#999;">', esc_html__('Cache indicator and browser console log', 'atec-cache-apcu'), '.</span></th>
										<td>'; CHECK::checkbox(['opt-name' => $option_key, 'name' => 'p_debug', 'value' => WPCA::settings('p_debug')]); echo '</td>
									</tr>';
							TOOLS::table_footer();
						}
					echo
					'</div>';
					TOOLS::submit_button('#editor-break '.__('Save', 'atec-cache-apcu'));
				}
				else TOOLS::msg(false, 'APCu '.__('extension is NOT installed/enabled', 'atec-cache-apcu'));

				echo
				'<hr>';

				if (!$license_ok) 
				{
					TOOLS::pro_feature($una, ' - '.__('this will enable the advanced', 'atec-cache-apcu').' '.__('page cache', 'atec-cache-apcu'), true);
					echo '<br class="atec-mb-20">';
				}
				
				echo 
				'<div class="atec-row">';
				
					TOOLS::help(__('Page Cache', 'atec-cache-apcu'),
						__('The page cache is an additional feature of this plugin', 'atec-cache-apcu').'. '.
						__('It will give your page an additonal boost, by delivering pages from APCu cache', 'atec-cache-apcu').'. '.
						__('The page cache saves pages, posts and categories – no product/shop pages (WooCommerce)', 'atec-cache-apcu').'.');

					// NEEDS translation
					TOOLS::help(__('„PRO“ APC Mode (Advanced Page Cache)', 'atec-cache-apcu'),
						__('Serves full page HTML earlier than standard caching for maximum performance', 'atec-cache-apcu').'.');

					if ($p_cache)
					{
						TOOLS::help(__('„Show debug“', 'atec-cache-apcu'),
							__('The „Show debug“ feature is for temporary use. It will show a small green circle in the upper left corner, when the page is served from cache. In addition you will find further details in your browser console. Please flush the page cache, once you are done with testing', 'atec-cache-apcu').'.');
					}

					TOOLS::help(__('Multiple PC plugins', 'atec-cache-apcu'),
						__('Do not use multiple page cache plugins simultaneously', 'atec-cache-apcu').'.', true);
	
					if (is_multisite()) TOOLS::msg('warning', __('The page cache is not designed to support multisites', 'atec-cache-apcu').'.<br>'.__('Please try the „Mega-Cache“-Plugin for multisites', 'atec-cache-apcu'), true);

						
				echo
				'</div>';
				
				if ($msg = WPC::pcache_detected()) TOOLS::msg('warning', $msg);

			echo
			'</div>',
			
		'</div>',

		'</form>',
		
	'</div>';

	TOOLS::reg_inline_style('wpca_settings',
	'
		.atec-custom-form { min-height: 185px; }
		p.submit { margin-top: 10px !important; }
	');

}
?>