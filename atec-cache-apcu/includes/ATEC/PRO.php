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

	private static function pro_transient($option_key, $option_key_MULTI) : bool
	{
		return get_transient($option_key) || get_transient($option_key_MULTI);
	}
	
	public static function pro_check_license($license_code_param = null, $site_host = null, $slug = null)
	{
		if (is_null($slug)) $slug = INIT::slug();
		$suffix = $slug=== 'wpmc' ? '_MEGA' : '';

		$option_base = 'atec_license_code';
		$option_key= $option_base.$suffix;
		$option_key_MULTI = $option_base.'_MULTI';
		
		if (!$license_code_param)
		{
			if (self::pro_transient($option_key, $option_key_MULTI)) return true;	// license status is cached
			$license_code = get_option($option_key);											// get "atec", "mega" or "ct4w" license code from DB
			if (!$license_code) $license_code = get_option($option_key_MULTI);
		}
		else $license_code = $license_code_param;
		if (empty($license_code)) return false; 				// 'Empty license code';
		
		if (!extension_loaded('openssl')) return false; 	// 'OpenSSL extension is required to verify the license';
		if (!$site_host) $site_host = INIT::site_host();
		$site_host = self::get_root_domain($site_host);

		$decoded = base64_decode($license_code, true);
		if ($decoded === false) return false;
		$decrypt_ok = openssl_public_decrypt($decoded, $decrypted, self::ATEC_PUBLIC_KEY);

		$license_ok = false;
		if ($decrypt_ok)
		{
			$license_ok = in_array($decrypted,[$site_host.$suffix, $site_host.'_MULTI']);		// true if licenseCode is "atec", "mega" or "ct4w" – or a "multi" license
			if ($license_ok)
			{
				if (str_ends_with($decrypted, '_MULTI')) set_transient($option_key_MULTI, true, 86400);
				else set_transient($option_key, true, 86400);
			}
		}

		if ($license_ok) 
		{
			if ($license_code_param) update_option($option_key, $license_code);		// set "atec" or "mega" | code can resolve to a MULTI license
		}
		else 
		{ 
			delete_option($option_key); delete_transient($option_key); 
		}
		return $license_ok;
	}

	public static function pro_form($una)
	{
		$option_base = 'atec_license_code';
		$slug = $una->slug;
		$suffix = $slug=== 'atec_wpmc' ? '_MEGA' : ($slug=== 'atec_wpct' ? '_CT4W' : '');
		$option_key = $option_base.$suffix;

		$license_code = TOOLS::clean_request('licenseCode');
		if ($license_code=== '') $license_code= get_option($option_key,'');

		$site_host = wp_parse_url(get_site_url(),PHP_URL_HOST);
		$license_ok = self::pro_check_license($license_code, $site_host, $slug);

		$imgSrc = plugins_url('/assets/img/atec-group/', dirname(__DIR__));
		echo
		'<div class="atec-db atec-center atec-m-20">';

			if ($license_ok) echo '<h4 class="atec-green atec-mt-10">Thank you for activating your „Lifetime-Site-License“.</h4>';
			else TOOLS::reg_inline_script('group_pro_package', 'jQuery("#pro_package").show();');
			
			TOOLS::p('');
			
			TOOLS::form_header($una, '', 'License', '', 'atec-fit');
				\ATEC\SVG::echo('license');
				echo
				'<p class="atec-m-0 atec-mb-10">',
					'<strong>Site name: </strong>', esc_attr($site_host), 
				'</p>',
				'<textarea cols="80" rows="3" name="licenseCode" class="atec-fs-10">', esc_textarea($license_code), '</textarea><br>',
				'<label class="atec-fs-12"><b>Paste your license code here</b></label>',
				'<br><br>
				<div class="atec-m-auto">';
					TOOLS::submit_button('',true);
				echo
				'</div>';
			TOOLS::form_footer();
			
			if ($license_code!== '') 
			{
				TOOLS::p('');
				echo '<div class="atec-m-auto">'; 
					TOOLS::badge($license_ok, 'The license code is#valid for your site', 'NOT valid');
				echo '</div>';
			}
			
		echo
		'</div>';
	
	}

}
?>