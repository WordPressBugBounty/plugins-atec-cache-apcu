<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;

final class PRO
{

private const ATEC_PUBLIC_KEY =
'-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5zzmHZQNovGx5j6v3I+E9d3ry
5vqZJduXkur14y8g3dHUIwKBTT8LdGnNA6injcj0C7ja75zGTphPBXIhql756XhJ
UlHIMXYppEhrzp8SpmpLViw98aMQuBmglj78kFmR8yI6VZG10H4Pl2MsDdBhA/x2
riq3aeQVZ8yllQ3bbwIDAQAB
-----END PUBLIC KEY-----';

	public static function noop(): void {}
	
	private static function get_root_domain(string $host): string
	{
		if (strpos($host, '://') !== false) { $host = wp_parse_url($host, PHP_URL_HOST); }
		$host = preg_replace('/^www\./', '', strtolower($host));

		// List of known multi-part TLDs (expand if needed)
		$doubleTLDs = ['co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'com.au', 'net.au', 'org.au', 'co.nz', 'com.br', 'com.mx',];

		foreach ($doubleTLDs as $tld)
		{
			if (str_ends_with($host, '.' . $tld))
			{
				$parts = explode('.', $host);
				$cnt = count($parts);
				if ($cnt >= 3) { 	return $parts[$cnt - 3] . '.' . $parts[$cnt - 2] . '.' . $parts[$cnt - 1]; }		// Return: domain + TLD, e.g. example.co.uk
			}
		}

		// Default fallback: last two parts
		$parts = explode('.', $host);
		$cnt = count($parts);
		if ($cnt >= 2) { 	return $parts[$cnt - 2] . '.' . $parts[$cnt - 1]; }
		return $host;
	}

	public static function pro_check_license($licenseCode=null, $siteName=null, $slug=null)
	{
		$optionBaseName = 'atec_license_code';
		$optionBaseName_MULTI = $optionBaseName.'_MULTI';
		if (is_null($slug)) $slug = INIT::slug();
		$suffix = $slug=== 'atec_wpmc' ? '_MEGA' : ($slug=== 'atec_wpct' ? '_CT4W' : '');
		$option_key= $optionBaseName.$suffix;
		if (!$licenseCode)
		{
			if (get_transient($option_key)) return true;									// license status is cached
			$licenseCode= get_option($option_key,'');									// get "atec", "mega" or "ct4w" license code from DB
			if ($licenseCode=== '') $licenseCode= get_option($optionBaseName_MULTI,'');
		}

		if ($licenseCode=== '') return false; 					// 'Empty license code';
		if (!extension_loaded('openssl')) return false; 	// 'OpenSSL extension is required to verify the license';
		if (!$siteName) $siteName=wp_parse_url(get_site_url(),PHP_URL_HOST);
		$siteName = self::get_root_domain($siteName);

		$decoded = base64_decode($licenseCode, true);
		if ($decoded === false) return false;
		$licenseOk = openssl_public_decrypt($decoded, $decrypted, self::ATEC_PUBLIC_KEY);

		if ($licenseOk)
		{
			$licenseOk = in_array($decrypted,[$siteName.$suffix, $siteName.'_MULTI']);		// true if licenseCode is "atec", "mega" or "ct4w" – or a "multi" license
			if ($licenseOk)
			{
				if (str_ends_with($decrypted, '_MULTI')) set_transient($optionBaseName_MULTI, true, 86400);
				else set_transient($suffix=== '' ? $optionBaseName : $optionBaseName.$suffix, true, 86400); // "atec_license_code", "atec_license_code_MEGA", or "atec_license_code_CT4W"

			}
		}
		if ($licenseOk) update_option($option_key, $licenseCode);		// set "atec", "mega" or "ct" license code | code can resolve to a MULTI license
		else { delete_option($option_key); delete_transient($option_key); }
		return $licenseOk;
	}

	public static function pro_form($una)
	{
		$optionBaseName = 'atec_license_code';
		$slug = $una->slug;
		$suffix = $slug=== 'atec_wpmc' ? '_MEGA' : ($slug=== 'atec_wpct' ? '_CT4W' : '');
		$option_key = $optionBaseName.$suffix;

		$licenseCode = TOOLS::clean_request('licenseCode');
		if ($licenseCode=== '') $licenseCode= get_option($option_key,'');

		$siteName = wp_parse_url(get_site_url(),PHP_URL_HOST);
		$licenseOk = self::pro_check_license($licenseCode, $siteName, $slug);

		$imgSrc = plugins_url('/assets/img/atec-group/', dirname(__DIR__));
		echo
		'<div class="atec-db atec-center atec-m-20">';

			if ($licenseOk) echo '<h4 class="atec-green atec-mt-10">Thank you for activating your „Lifetime-Site-License“.</h4>';
			else TOOLS::reg_inline_script('group_pro_package', 'jQuery("#pro_package").show();');
			
			TOOLS::p('');
			
			TOOLS::form_header($una, '', 'License', '', 'atec-fit');
				\ATEC\SVG::echo('license');
				echo
				'<p class="atec-m-0 atec-mb-10">',
					'<strong>Site name: </strong>', esc_attr($siteName), 
				'</p>',
				'<textarea cols="80" rows="3" name="licenseCode" class="atec-fs-10">', esc_textarea($licenseCode), '</textarea><br>',
				'<label class="atec-fs-12"><b>Paste your license code here</b></label>',
				'<br><br>
				<div class="atec-m-auto">';
					TOOLS::submit_button('',true);
				echo
				'</div>';
			TOOLS::form_footer();
			
			if ($licenseCode!== '') 
			{
				TOOLS::p('');
				echo '<div class="atec-m-auto">'; 
					TOOLS::badge($licenseOk, 'The license code is#valid for your site', 'NOT valid');
				echo '</div>';
			}
			
		echo
		'</div>';
	
	}

}
?>