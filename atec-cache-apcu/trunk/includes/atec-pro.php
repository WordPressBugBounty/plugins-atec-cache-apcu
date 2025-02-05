<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_pro { 

public function atec_pro_check_license($licenseCode=null, $siteName=null, $plugin=null)
{
	$optionBaseName='atec_license_code';
	if ($plugin) $suffix=$plugin==='mega-cache'?'_MEGA':'';
	else { $slug=atec_get_slug(); $suffix=$slug==='atec_wpmc'?'_MEGA':''; }
	$optName=$optionBaseName.$suffix;
	if (!$licenseCode)
	{
		if (get_transient($optName)) return true;
		$licenseCode=get_option($optName,'');
	}
	if ($licenseCode==='') return false; // 'Empty license code';
	if (!$siteName) $siteName=wp_parse_url(get_site_url(),PHP_URL_HOST);
	$siteName = str_replace('www.','',$siteName);
	if (!extension_loaded('openssl')) return false; // 'OpenSSL extension is required to verify the license';

$publicKey='-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5zzmHZQNovGx5j6v3I+E9d3ry
5vqZJduXkur14y8g3dHUIwKBTT8LdGnNA6injcj0C7ja75zGTphPBXIhql756XhJ
UlHIMXYppEhrzp8SpmpLViw98aMQuBmglj78kFmR8yI6VZG10H4Pl2MsDdBhA/x2
riq3aeQVZ8yllQ3bbwIDAQAB
-----END PUBLIC KEY-----';

	@openssl_public_decrypt(base64_decode($licenseCode), $decrypted, $publicKey);
	$licenseOk=in_array($decrypted,[$siteName.$suffix,$siteName.'_MULTI']);
	if ($licenseOk) 
	{
		set_transient($optName,true,86400); update_option($optName,$licenseCode); 
		if (str_ends_with($decrypted, '_MULTI'))
		{
			$optName=($suffix==='')?$optionBaseName.'_MEGA':$optionBaseName;
			set_transient($optName,true,86400); update_option($optName,$licenseCode);		
		}
	}
	else { delete_transient($optName); delete_option($optName); }
	return $licenseOk;
}

public function atec_pro_form($url, $nonce, $licenseCode, $plugin)
{
	$suffix = $plugin==='mega-cache'?'_MEGA':'';
	$optName='atec_license_code'.$suffix;

	if ($licenseCode==='') $licenseCode=get_option($optName,'');
	else update_option($optName,$licenseCode,'auto');
	
	$siteName=wp_parse_url(get_site_url(),PHP_URL_HOST);
	$licenseOk=$this->atec_pro_check_license($licenseCode,$siteName,$plugin);

	if ($licenseOk) 
	{ 
		echo '<h4 class="atec-green">', esc_attr__('Thank you for activating your „Lifetime-Site-License“.','atec-cache-apcu'),'</h4>'; 
		atec_reg_inline_script('group_pro_package','jQuery("#pro_package").remove();');
	}

	$imgSrc = plugins_url( '/assets/img/atec-group/', __DIR__ );
	echo '
		<div style="width: fit-content; margin: 10px auto;">
		<form class="atec-box-white" name="atec_license" method="post" action="', esc_url($url), '&license=true&save=true&_wpnonce=', esc_attr($nonce), '&plugin=', esc_attr($plugin), '">
			<p class="atec-m-0 atec-mb-10"><strong>Site name: </strong>', esc_attr($siteName), '</p>';
			// @codingStandardsIgnoreStart | Image is not an attachement	
			echo
			'<p class="atec-m-0"><img src="', esc_url($imgSrc.'atec_license_icon.svg') ,'" style="height:22px; margin-right: 5px;"></p>';
			// @codingStandardsIgnoreEnd
			echo 
			'<label><b>', esc_attr__('Paste your license code here','atec-cache-apcu'), '</b></label><br>
			<textarea cols="40" rows="3" name="licenseCode">', esc_textarea($licenseCode), '</textarea><br><br>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="', esc_attr__('Save','atec-cache-apcu'), '">
		</form>
		</div>';
			  
		if ($licenseCode!=='')
		{ echo '<center>'; atec_badge(__('The license code is valid for your site','atec-cache-apcu'),__('The license code is not valid','atec-cache-apcu'),$licenseOk); echo '</center>'; }
}

function __construct() {
}}
?>