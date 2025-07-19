<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\GROUP;
use ATEC\INIT;
use ATEC\TOOLS;

final class LICENSE
{

	private static function feature_list($slug, $is_atec)
	{
		echo
		'<div class="atec-db atec-mt-10">
			🎁 ';

			if ($slug=== 'wpmc') echo '<strong>Seven additional storage options</strong>';
			elseif ($slug=== 'wpct') echo '<strong>Redis / Memcached storage</strong>';
			else echo '<strong>Including over 40 valuable plugins</strong>';

			echo
			'<br class="atec-mb-10">

			<div class="atec-dilb atec-mr-10">
				<ul class="atec-border-white atec-bg-w">
					<li>⭐ Priority support</li>
					<li>⭐ Upgrades & updates</li>';
					if (!$is_atec) echo '<li>⭐ ‘Lifetime-site-License’</li>';
				echo
				'</ul>
			</div>

			<div class="atec-dilb atec-ml-10">
				<ul class="atec-border-white atec-bg-w">';

					if ($slug=== 'wpmc')
					{
						echo
						'<li>⭐ Minify HTML</li>
						<li>⭐ Custom post types</li>
						<li>⭐ Full page cache priming</li>';
					}
					elseif ($slug=== 'wpct')
					{
						echo
						'<li>⭐ Object Cache</li>
						<li>⭐ Database Tuning</li>
						<li>⭐ Full page cache priming</li>
						<li>⭐ <strong>WooCommerce</strong> product caching</li>
				</ul>
			</div>

			<div class="atec-dilb atec-ml-10">
				<ul class="atec-border-white atec-bg-w">
					<li>⭐ Minify HTML</li>
					<li>⭐ Exclude URLs</li>
					<li>⭐ Custom post types</li>';
					}
					else
					{
						echo
						'<li>⭐ ‘Lifetime-site-License’</li>
						<li>⭐ Access to all the ‘PRO’ features</li>';
					}

				echo
				'</ul>
			</div>

		</div>';
	}

	public static function init($una)
	{
		if (!extension_loaded('openssl')) INIT::admin_notice($una->slug, 'warning', 'The openSSL extension is required for license handling.');
		$is_atec = !in_array($una->slug,['wpmc', 'wpct'],true);

		echo
		'<div class="atec-border-white atec-center" style="margin: 20px auto; padding: 20px;">
		
			<h3 class="atec-row atec-fit atec-m-auto" style="align-items: center;">';
				\ATEC\SVG::echo($una->slug=== 'wpmc' ? 'wpmc' : 'wpa', 'atec-vat');
				if ($una->slug=== 'wpmc') echo 'Mega Cache';
				else echo '<div><span class="atec-logo-text">tec</span>-Plugins</div>';
				echo ' ‘PRO’ package',
			'</h3>
			
			<div id="pro_package" style="display: none; style="padding: 5px 15px 10px 15px;">';
				
					self::feature_list($una->slug, $is_atec);
					echo
					'<div class="atec-db atec-fit atec-m-auto atec-border-white atec-bg-w" style="padding: 0 5px 15px 5px;">';
						if (!$is_atec)
						{
							$arr = $una->slug=== 'wpmc' ? ['apcu', 'redis', 'memcached', 'mariadb', 'mysql'] : ['redis', 'memcached'];
							foreach ($arr as $a) \ATEC\SVG::echo($a); 
						}
						else
						{
							$atec_group = GROUP::all_plugins();
							$halfway = round(count($atec_group)/2);
							foreach ($atec_group as $index => $p)
							{
								if (in_array($p->slug,['wpmc', 'wpct'])) continue;
								if ($index % $halfway===0) echo '<br>';
								\ATEC\SVG::echo($p->slug, 'atec-m-3'); 
							}
						}
						echo
					'</div>';

					echo
					'<p style="font-size: 1.2em; line-height: 1.3em;">',
						'Buy the ‘PRO’ package through one time payment.<br>',
						'The license is valid for the lifetime of your site (domain).<br><b>',
						'No subscription. No registration required.</b>',
					'</p>';

					$domain = $una->slug=== 'wpmc' ? 'wpmegacache' : ($una->slug=== 'wpct' ? 'cachetune' : 'atecplugins');
					$licenseUrl = 'https://'.$domain.'.com/license/';

					echo
					'<div class=" atec-m-auto">
						<a class="atec-nodeco atec-fit" href="', esc_url($licenseUrl), '" target="_blank">
							<button class="button button-primary">GET YOUR ‘PRO’ PACKAGE NOW</button>
							<div class="atec-mt-5 atec-small">', esc_textarea($licenseUrl), '</div>
						</a>
					</div>';

				echo
				'</div>';

		if (method_exists('\ATEC\PRO', 'pro_form')) \ATEC\PRO::pro_form($una);
		else TOOLS::reg_inline_script('group_pro_package', 'jQuery("#pro_package").show();');

		echo
		'</div>';

		TOOLS::reg_inline_script('atec_pro_banner', 'jQuery("#atec_pro_banner").remove();');

	}

}
?>