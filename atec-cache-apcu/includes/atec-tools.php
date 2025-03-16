<?php
if (!defined('ABSPATH')) { exit(); }
define('ATEC_TOOLS_INC',true); // downward comp. Feb 25, remove later

// START These functions are all deprecated, Feb. 25, remove later

function atec_get_upload_dir($p): string { return wp_get_upload_dir()['basedir'].'/'.($p==='mega-cache'?'':'atec-').$p; }
// Replaced by ATEC_fs, used in *activation & *uninstall Feb. 25, remove later
// function atec_info($str): void { atec_badge($str,'','info'); }
// Replaced by atec_info_msg, before Feb. 25, remove later
function atec_get_version($slug): string { return wp_cache_get('atec_'.esc_attr($slug).'_version'); }
// Was also only used in atec_header() until Feb. 25, remove later
if (!function_exists('atec_get_plugin'))
{ function atec_get_plugin($dir): string { $plugin=plugin_basename($dir); return substr($plugin,0,strpos($plugin,DIRECTORY_SEPARATOR)); } }
// Was used in atec_header(); & atec-footer.php until Feb. 25, remove later

// STOP These functions are all deprecated, Feb. 25, remove later

function atec_una($navDefault='')
{
	$nav = atec_clean_request('nav');
	if ($nav==='') $nav=$navDefault;
	$navs = $navDefault==='Dashboard'?['#admin-home Dashboard']:[];
	return (object) array('url' => atec_get_url(), 'nonce' => wp_create_nonce(atec_nonce()), 'action' => atec_clean_request('action'), 'nav' => $nav, 'navs' => $navs);
}

function atec_p($txt): void { echo '<p class="atec-mb-0">', esc_html($txt), '.</p>'; }
function atec_enabled($enabled,$active=false): void 
{ 
	echo '<span style="color:', ($enabled?($active?'black':'green'):'red'), '" title="', ($enabled?esc_attr__('Enabled','atec-cache-apcu'):esc_attr__('Disabled','atec-cache-apcu')), '" class="', esc_attr(atec_dash_class($enabled?'yes-alt':'warning')), '"></span>'; 
}

function atec_server_sys_icon($dir,$icon) : void
{ 
	// @codingStandardsIgnoreStart | Image is not an attachement
	echo '<img class="atec-sys-icon" src="', esc_url(plugins_url( '/assets/img/system/'.$icon.'-icon.svg', $dir)), '">'; 
	// @codingStandardsIgnoreEnd
}

function atec_icon($dir,$icon,$margin=15): void
{
	$iconPath=plugins_url('assets/img/icons/',$dir);
	preg_match('/#([\-|\w]+)\s?(.*)/i', $icon, $matches);
	// @codingStandardsIgnoreStart | Image is not an attachement
	echo '<img style="max-width: 18px; max-height:18px; margin-right: ', esc_attr($margin), 'px;" src="', esc_url($iconPath.$matches[1].'.svg'), '">', isset($matches[2])?' '.esc_attr($matches[2]):'';
	// @codingStandardsIgnoreEnd
}

function atec_loader_dots($c=7): void
{ echo '<div class="atec-loader-dots atec-dilb">'; for ($i=0;$i<$c;$i++) echo '<span></span>'; echo '</div>'; }

function atec_check_admin_bar($slug,$url,$nonce,$nav): void
{
	$optName='atec_'.$slug.'_admin_bar'; $option=get_option($optName);
	update_option($optName,$option==0?1:0);
	wp_redirect(admin_url().'admin.php?page=atec_'.$slug.'&nav='.$nav.'&_wpnonce='.$nonce); 
}

function atec_notice(&$notice,$type,$str): void
{
	$message = ($notice['message']??'')!=='';
	$message.= ($message===''?' ':'').$str;
	if (($notice['type']??'')!=='') $type=$notice['type']==='info'?$type:$notice['type'];
	$notice['type']=$type; $notice['message']=$message;
}

function atec_little_ext_box($arr): void
{
	echo '<div class="atec-dilb" style="margin-right: -10px;">';
	foreach($arr as $ext)
	{
		$enabled 	= extension_loaded(strtolower($ext));
		$bg 			= $enabled?'#f0fff0':'#fff0f0';
		echo '<span title="', esc_attr($ext), ' extension ', esc_attr($enabled?'enabled':'disabled'), '" class="atec-badge atec-dilb atec-float-right" style="background:', esc_attr($bg), '"><strong>',
		esc_attr($ext), '</strong>&nbsp;'; atec_dash_span($enabled?'yes-alt':'dismiss',$enabled?'atec-green':'atec-red'); echo '</span>';
	}
	echo '</div>';
}

function atec_trailingslashit($str): string { return rtrim($str,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR; }

function atec_random_string($length,$lower=false): string
{ 
	$charset = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ'; $string = ''; 
	// @codingStandardsIgnoreStart | wp_rand is not available if called early
	while(strlen($string)<$length) { $string .= substr($charset,rand(0,61),1); } 
	// @codingStandardsIgnoreEnd
	return $lower?strtolower($string):$string;
}

function atec_integrity_check($dir,$plugin=''): void
{
	if ($plugin==='') $plugin=str_replace('/includes','',plugin_basename($dir));
	if (get_option('atec_allow_integrity_check')===true) 
	wp_remote_get('https://atecplugins.com/WP-Plugins/activated.php?plugin='.esc_attr($plugin).'&domain='.get_bloginfo('url')); 
}

function atec_short_string($str,$len=128): string 
{ 
	if ($str=='') return $str;
	return strlen($str)>$len?substr($str, 0, $len).' ...':$str; 
}

function atec_dash_class($icon,$class=''): string { return 'dashicons dashicons-'.$icon.($class!==''?' '.$class:''); }

function atec_include_if_exists($dir,$php): void
{
	$include=$dir.'/'.$php;
	if (file_exists($include)) @include_once($include);
	else echo '<!-- ', esc_attr($include), ' -- not found -->';
}

function atec_check_license($licenseCode=null, $siteName=null): bool
{
	// @codingStandardsIgnoreStart | This function should have a low CPU footprint, therefore no use of WP_Filesystem();.
	$include=__DIR__.'/atec-pro.php';
	if (!class_exists('ATEC_pro') && file_exists($include)) @include_once($include);
	// @codingStandardsIgnoreEnd
	if (class_exists('ATEC_pro')) { return (new ATEC_pro)->atec_pro_check_license($licenseCode, $siteName); }
	return false;
}

function atec_integrity_check_banner($dir):void
{
	$plugin=str_replace('/includes','',plugin_basename($dir));
	$link_yes=get_admin_url().'admin.php?page=atec_group&action=integrity&integrity=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce').'&plugin='.$plugin);
	$link_no=str_replace('integrity=true','integrity=false',$link_yes);
	echo '
	<div class="atec-sticky-left" style="height:36px;" title="Allow one time connection to https://atecplugins.com on plugin activation.">
		<div class="atec-dilb atec-fs-10">Connect to atecplugins.com<br><div class="atec-fs-8" style="margin-top: -4px;">One time connection on activation.</div></div>
		<div class="atec-dilb atec-vat atec-mt-5">
			<a style="background: rgba(0, 180, 0, 0.5); color:white !important;" class="atec-integritry atec-fs-12" href="', esc_url($link_yes), '">YES</a>
			<a style="background: rgba(180, 0, 0, 0.5); color:white !important;" class="atec-integritry atec-fs-12" href="', esc_url($link_no), '">NO</a>
		</div>
	</div>';
}

function atec_license_banner($dir): bool
{
	$plugin=str_replace('/includes','',plugin_basename($dir));
	$licenseOk=atec_check_license();
	$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce').'&plugin='.$plugin);
	$mega=str_starts_with($plugin,'atec-')?'':'Mega-';
	echo '
	<div class="atec-sticky-right button" style="padding-top: 4px !important;', ($licenseOk?' border: var(--border-lightgrey);':''), '">
		<a class="atec-nodeco atec-', ($licenseOk?'green':'blue') ,'" href="', esc_url($link), '">';
			atec_dash_span('awards','atec-'.($licenseOk?'green':'blue'),'margin-right: 4px;');
			echo '<span style="vertical-align: middle; padding-top: 2px;">', ($licenseOk?esc_attr__('„PRO“ version','atec-cache-apcu'):esc_attr__('Upgrade to „PRO“','atec-cache-apcu')), '.</span>',
		'</a>
	</div>';
	return $licenseOk;
}

function atec_br($str) : void
{
	$c			= 0;
	$ex 		= explode('<br>',$str);
	$count 	= count($ex);
	foreach ($ex as $t) { $c++; echo esc_html($t), ($c<count($ex)?'<br>':''); }
}

function atec_pro_feature($desc='',$small=false, $licenseOk=null): bool
{ 
	if (is_null($licenseOk)) $licenseOk=atec_check_license()===true; 
	if (!$licenseOk) 
	{ 
		$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce'));
		echo '
		<div class="', ($desc!==''?'atec-dilb':''), '">
			<a class="atec-dilb atec-nodeco atec-blue" href="', esc_url($link), '">';
			if ($small)
			{
				echo 
				'<div class="atec-dilb atec-blue atec-badge atec-fs-12" style="background: #f9f9ff; border: solid 1px #dde; margin: 0; padding: 4px 5px;">',
					'<div class="atec-dilb atec-vat">'; atec_dash_span('awards','atec-blue atec-fs-14','padding-top: 2px;'); echo '</div>',
					'<div class="atec-dilb atec-vat">Upgrade to „PRO“', str_starts_with($desc,'<br>')?'.':' '; atec_br($desc); echo '.</div>',
				'</div>';
				$desc='';
			}
			else atec_badge('„PRO“ feature - please upgrade','','blue');
		echo '
			</a>
		</div>';
		if ($desc!=='') { echo '<br><div class="atec-pro-box"><h4>'; atec_br($desc); echo '.'; echo '</h4></div>'; 	}
	}
	return $licenseOk; 
}

function atec_pro_block($inline='',$more=null): void
{
	$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce'));
	echo '
	<div class="atec-df atec-pro-box">',
		'<div class="atec-df1 atec-vat" style="max-width: 22px;">'; atec_dash_span('awards','atec-blue atec-fs-14','padding-top: 2px;'); echo '</div>',
		'<div class="atec-df1 atec-vat atec-nowrap">';
			if ($more) { atec_br($more); echo '.<br>'; }
			echo 
			'<a class="atec-nodeco atec-blue" href="', esc_url($link), '">Please upgrade to „PRO“ version<strong>', ($inline!==''?' '.esc_attr($inline):''), '</strong>.</a>';
		echo
		'</div>',
	'</div><br>';
}

function atec_pro_only($licenseOk=null): bool
{ 
	if (is_null($licenseOk)) $licenseOk=atec_check_license();
	if (!$licenseOk)	atec_pro_block('','This is a „PRO“ ONLY plugin.<br>A license is required to use the basic functions');
	return $licenseOk;
}

function atec_nav_tab_dashboard($url, $nonce, $nav, $dir=''): void
{ atec_nav_tab($url, $nonce, $nav, ['#admin-home Dashboard']); }

function atec_single_nav_tab($url,$nonce,$nav,$actNav,$iconPath,$icon,$str,$margin=0) : void
{
	// @codingStandardsIgnoreStart | Image is not an attachement
	echo '<a style="margin-right: ', esc_attr($margin), 'px;" href="', esc_url($url), '&nav=', esc_attr($actNav), '&_wpnonce=', esc_attr($nonce), '" class="nav-tab', ($nav===$actNav?' nav-tab-active':''), '">';
		if (in_array($icon,['info','update','code-standards'])) echo '<span class="', esc_attr(atec_dash_class($icon,'atec-blue')), '"></span>';
		else echo '<img class="nav-icon" src="', esc_url($iconPath.$icon.'.svg'), '"> ', ($icon===strtolower($str)?'':esc_attr($str));
	echo '</a>';
	// @codingStandardsIgnoreEnd
}

function atec_nav_tab($url, $nonce, $nav, $arr, $break=999, $licenseOk=null, $highlight='', $about=false, $update=false, $debug=false): void
{
	$iconPath = plugins_url('assets/img/',__DIR__).'icons/';
	$link			= 'https://'.(str_contains($url, 'wpmc')?'wpmegcache':'atecplugins').'.com/';
	$dashArr 	= ['admin-home','admin-plugins','awards','admin-generic','archive','info','editor-code','editor-table','admin-comments','backup','hourglass','admin-tools','update','admin-settings','database','forms','groups','code-standards'];
	$noPRO = (is_null($licenseOk) || !$licenseOk) && $break!==999;
	echo 
	'<h2 class="nav-tab-wrapper"', $noPRO?' style="margin-top: -10px;"':'', '>';
		$c 	= 0;
		$reg = '/#([\-|\w]+)\s(.*)/i';
		foreach($arr as $a) 
		{ 
			$c++;
			preg_match($reg, $a, $matches);
			$nice=str_replace(['(',')'],'',str_replace([' ','.','-','/'],'_',$matches[2]??$a));
			echo 
			'<div class="atec-dilb atec-fs-12"', $c===$break?' style="margin-right: 0.5em;"':'', '>';
				if ($noPRO) echo '<div class="atec-db atec-pro" style="height:15px; padding-left:10px;">', $c>$break?'PRO':'&nbsp;', '</div>';
				echo
				'<a href="', esc_url($url.'&nav='.$nice.'&_wpnonce='.$nonce), '" class="nav-tab ', ($nav==$nice?' nav-tab-active':''), ($nice==$highlight?' atec-under':''), '">';
					// @codingStandardsIgnoreStart | Image is not an attachement
					if (isset($matches[2])) 
					{
						if (in_array($matches[1], $dashArr)) echo '<span class="', esc_attr(atec_dash_class($matches[1],'atec-blue')), '"></span>';
						else echo '<img class="nav-icon" src="', esc_url($iconPath.$matches[1].'.svg'), '">';
						echo '&nbsp;', esc_attr($matches[2]);
					}
					else echo esc_attr(preg_replace($reg, '', $a));
					// @codingStandardsIgnoreEnd
				echo 
				'</a>
			</div>';
		}
		echo '
		<div class="atec-dilb atec-float-right"', $noPRO?' style="padding-top:24px;"':'', '>';
			if ($update) atec_single_nav_tab($url,$nonce,$nav,'Update',$iconPath,'update','Update');
			if ($about) atec_single_nav_tab($url,$nonce,$nav,'About',$iconPath,'about','About');
			if ($debug) atec_single_nav_tab($url,$nonce,$nav,'Debug',$iconPath,'code-standards','Debug');
			if (!str_contains($url, 'atec_group')) atec_single_nav_tab($url,$nonce,$nav,'Info',$iconPath,'info','Info',10);
		echo '
		</div>
	</h2>';
}

function atec_empty_tr(): void { echo '<tr><td colspan="99" class="emptyTR1"></td></tr><tr><td colspan="99" class="emptyTR2"></td></tr>'; }
function atec_table_footer(): void { echo '</tbody></table>'; }
function atec_table_header_tiny($tds,$id='',$class=''): void
{
	echo '<table ', (esc_attr($id!==''?" id=$id":'')) ,' class="atec-table atec-table-tiny atec-fit ', esc_attr($class), '"><thead><tr>';
	$reg = '/#([\-|\w]+)\s?(.*)/i';
	foreach ($tds as $td) 
	{ 
		echo '<th>';
			preg_match($reg, $td, $matches);
			if (isset($matches[1])) 
			{
				atec_dash_span($matches[1]); 
				if (isset($matches[2])) echo ' '.esc_attr($matches[2]);
			}
			else echo esc_attr($td);
		echo '</th>'; 
	}
	echo '</tr></thead><tbody>';
}

function atec_nav_button($url,$nonce,$action,$nav,$button,$primary=false,$simple=false,$blank=false): void
{
	if (!$simple) echo '<div class="atec-float-left">';
	$href=$url.'&action='.$action.'&nav='.$nav.'&_wpnonce='.$nonce;
	$action=$action===''?'update':$action;
	$dash='';
	if ($action==='update' || $action==='delete' || $action==='deleteAll') { $dash=$action==='update'?'update':'trash'; $button=''; }
	elseif (in_array($button,['left','right'])) { $dash='arrow-'.$button.'-alt'; $button=''; }
	else
	{
		$reg = '/#([\-|\w]+)\s?(.*)/i';
		preg_match($reg, $button, $matches);
		if (isset($matches[2])) { $dash=$matches[1]; $button=$matches[2]; }
	}
	echo '
	<a id="', esc_attr($nonce), '" href="', esc_url($href), '"', ($blank?' target="_blank"':'') ,'>',
		'<button class="button button-', $primary?'primary':'secondary', '">';
			if ($dash!=='') { atec_dash_span($dash); if ($button!=='') echo ' '; }
			echo '<span>', esc_attr($button), '</span>',
		'</button>',
	'</a>';
	if (!$simple) echo '</div>';
}

function atec_dash_span($dash,$class='',$style=''): void
{ echo '<span '.($style!==''?'style="'.esc_textarea($style).'"':'').' class="'.esc_attr(atec_dash_class($dash)).($class!==''?' '.esc_attr($class):'').'"></span>'; }

function atec_nav_button_confirm($url,$nonce,$action,$nav,$button,$pro=null): void
{
	echo '
	<div class="atec-float-left atec-btn-bg">
		<input title="Confirm action" type="checkbox" onchange="const $btn=jQuery(this).parent().find(\'button\'); $btn.prop(\'disabled\',!$btn.prop(\'disabled\'));">
		<a href="', esc_url($url), '&action=', esc_attr($action), '&nav=', esc_attr($nav), '&_wpnonce=', esc_attr($nonce),'">
			<button disabled="true" class="button button-secondary">';
				if (str_contains($action,'delete')) atec_dash_span('trash');
				echo '<span>', esc_attr($button), '</span>',
			'</button>
		</a>
	</div>';
}

function atec_create_button($action,$icon,$enabled,$url,$id,$nonce,$primary=false): void
{
	echo '
	<td><button ', esc_attr(!$enabled)?'disabled ':'', 'onclick="window.location.assign(\'', esc_url($url), '&action=', esc_attr($action), '&id=', esc_attr($id), '&_wpnonce=', esc_attr($nonce),'\');" class="button button-', ($primary?'primary':'secondary'), '">'; atec_dash_span($icon); echo '</button></td>';
}
  
function atec_missing_class_check($class=''): void
{
	if ($class!=='' && class_exists($class)) return;
	echo  '
	<div class="atec-badge atec-dilb" style="background: #fff0f0;">
		<div class="atec-dilb" style="width:20px; margin-right:5px;">'; atec_dash_span('dismiss'); echo '</div>
		<div class="atec-dilb atec-vam">A required class-file is missing – please ';
		if (is_plugin_active('atec-deploy/atec-deploy.php')) echo 'use <a href="', esc_url(admin_url().'admin.php?page=atec_wpdp'), '">';
		else echo 'download/activate <a href="https://atecplugins.com/WP-Plugins/atec-deploy.zip">';
		echo 'atec-deploy</a> to install the „PRO“ version of this plugin.
		</div>
	</div>';
}

function atec_badge($strSuccess,$strFailed,$ok,$hide=false,$nomargin=false,$block=false): void
{
	$md5 = $hide?md5($ok?$strSuccess:$strFailed):'';
	$bg 	= $ok==='blue'?'#f9f9ff':($ok==='info'?'#fff':($ok==='warning'?'rgba(255, 253, 253, 0.95)':($ok?'#f0fff0':'#fff0f0')));
	$border = $ok==='blue'?'#dde':($ok==='info'?'#eee':($ok==='warning'?'rgba(255, 155, 0, 1)':($ok?'#e0ffe0':'#ffe0e0')));
	$icon	= $ok==='blue'?'awards':($ok==='info'?'info-outline':($ok==='warning'?'warning':($ok?'yes-alt':'dismiss')));
	$color	= 'atec-'.($ok==='blue'?'blue':($ok==='info'?'black':($ok==='warning'?'orange':($ok?'green':'red'))));
	echo 
	'<div class="atec-badge atec-', ($block?'db':'dilb'), ' atec-fit', ($nomargin==true?' atec-mr-0':''), '"', ($md5!==''?' id="'.esc_attr($md5).'"':''), ' style="font-size: 13px !important; background:', esc_attr($bg), '">
		<div class="atec-dc" style="width:20px; padding-right:5px;"><span class="', esc_attr(atec_dash_class($icon,$color)), '"></span></div>
		<div class="atec-dc atec-vam" style="color: ', ($ok==='blue'?'#2271B1':($ok==='warning'?'orange':'black')), '">'; atec_br($ok?$strSuccess:$strFailed); echo '.</div>
	</div>';
	if ($md5!=='') atec_reg_inline_script('badge', 'setTimeout(()=> { jQuery("#'.esc_attr($md5).'").slideUp(); }, 750);', true);
}

function atec_info_msg($str, $br_before=null): void { if ($br_before) echo '<br>'; atec_badge($str,'','info'); }
function atec_warning_msg($str, $br_before=null, $br_after=null): void { if ($br_before) echo '<br>'; atec_badge($str,'','warning'); if ($br_after) echo '<br>'; }
function atec_error_msg($txt, $br_before=null, $br_after=null): void { if ($br_before) echo '<br>'; atec_badge('',$txt,false); if ($br_after) echo '<br>'; }
function atec_success_msg($txt, $br_before=null, $br_after=null): void { if ($br_before) echo '<br>'; atec_badge($txt,'',true); if ($br_after) echo '<br>'; }

function atec_progress_div(): void 
{ 
	echo '<div id="atec_loading" class="atec-progress"><div class="atec-progressBar"></div></div>';
	atec_reg_inline_script('progress', 'setTimeout(()=>{ jQuery("#atec_loading").css("opacity",0); },4500);', true); 
}

function atec_progress(): void { ob_start(); atec_flush(); }
function atec_flush(): void 
{ 
	if (@ob_get_level() > 0) @ob_flush();
	@flush(); 
}

function atec_info($id,$title): void { atec_help($id,$title,false,true,true); }

function atec_help($id,$title,$hide=false,$margin=true,$info=false): void
{ 
	echo '
	<div id="', esc_attr($id), '_help_button" class="button atec-help-button" style="', ($info?'border-color:#ccc !important;':''), ($margin?'':' margin-bottom: 0 !important;'), '" onclick="return showHelp(\'', esc_attr($id), '\');">';
		atec_dash_span($info?'info':'editor-help','atec-'.($info?'lightgrey':'orange'),'vertical-align: middle;'); echo '&nbsp;<span style="padding-top: 4px; vertical-align: middle;">', esc_attr($title), '</span>',
	'</div>';
	atec_reg_inline_script('help', 'function showHelp(id) { jQuery("#"+id+"_help").removeClass("atec-dn").addClass("atec-db"); jQuery("#"+id+"_help_button").remove(); return false; }');
}

function atec_get_url(): string
{ 
	$url_parts	= wp_parse_url( home_url() );
	$url			= $url_parts['scheme'] . "://" . $url_parts['host'] . (isset($url_parts['port'])?':'.$url_parts['port']:'') .atec_query();
	return rtrim(strtok($url, '&'),'/');
} 

function atec_clean_request($t,$nonce=''): string
{ 
	if (!isset($_REQUEST[ '_wpnonce' ]) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ '_wpnonce' ]) ), $nonce===''?atec_nonce():$nonce ) ) { return ''; }
	return isset($_REQUEST[$t])?sanitize_text_field(wp_unslash($_REQUEST[$t])):'';
}

function atec_header($dir,$slug,$title,$sub_title=''): bool
{ 
	$imgBaseDir		= plugins_url('/assets/img/',$dir).'/atec-group/';
	$atec_slug_arr	= ['wpb','wpca','wpci','wpd','wpdb',	'wpds','wps','wpsi','wms','wpwp',	'wpmc'];
	$approved		= in_array($slug, $atec_slug_arr);
	$wordpress		= 'https://wordpress.org/support/plugin/';
	$plugin 			= str_replace('/includes','',plugin_basename($dir)); 
	$supportLink	= (!$approved)?'https://atecplugins.com/contact/':$wordpress.$plugin;
	$ver					= wp_cache_get('atec_'.esc_attr($slug).'_version');

	if (is_null(get_option('atec_allow_integrity_check',null))) atec_integrity_check_banner($dir);
	$licenseOk = atec_license_banner($dir);

	echo '
	<div class="atec-header">
		<h3 class="atec-mb-0 atec-center" style="height: 24px; line-height: 0.85em;">';
			// @codingStandardsIgnoreStart | Image is not an attachement
			echo '<img alt="Plugin icon" src="',esc_url($imgBaseDir.'atec_'.($slug==='wpmc'?'wpmc':'wpa').'_icon.svg'),'" class="atec-plugin-icon" style="margin-right: 5px; height:'.($slug===''?'20':'14').'px; '.($slug==='wpmc'?'padding-bottom:4px':'').'">';
			if ($slug==='wpmc') echo '<span style="color:#2340b1;">Mega</span>-<span style="color:#fe5300;">Cache</span>';
			elseif ($slug==='') echo 'atec-Plugins';
			// @codingStandardsIgnoreEnd
			if ($slug!=='') 
			{
				if ($slug!=='wpmc')
				{
					$fixName 	= str_replace(['Atec',' '],['atec','-'],ucwords(str_replace(['-','apcu','webp','svg','htaccess'],[' ','APCu','WebP','SVG','HTaccess'],$plugin)));
					echo '&nbsp;', esc_html($fixName);
					// @codingStandardsIgnoreStart | Image is not an attachement
					echo '<img alt="Plugin icon" src="',esc_url($imgBaseDir.'atec_'.esc_attr($slug).'_icon.svg'),'" class="atec-plugin-icon" style="margin:0 5px 0 10px; height:20px; vertical-align:bottom;">';
					// @codingStandardsIgnoreEnd
				}
				echo '<span class="atec-fs-10">&nbspv', esc_attr($ver), '</span>';
			}
		echo '</h3>';
		atec_progress_div();
		$color='rgba(34, 113, 177, 0.33)';
		echo '
		<div class="atec-center atec-vat atec-mt-2">',
			'<a class="atec-fs-12 atec-nodeco atec-btn-small atec-mt-0" style="border-color: ', esc_attr($color), ' ;" href="', esc_url($supportLink), '" target="_blank">'; 	atec_dash_span('sos'); echo '&nbsp;Plugin support</a>';
			if (in_array($slug,['wpci','wpd','wpdp','wppp']))
			{
				$url			= atec_get_url();
				$nonce 	= wp_create_nonce(atec_nonce());
				$action 	= atec_clean_request('action');
				$nav 		= atec_clean_request('nav');
				$adminBar 	= get_option('atec_'.$slug.'_admin_bar');
				$id='atec_'.$slug.'_admin_bar';
				
				echo 
				'<div class="atec-dilb atec-border atec-bg-w85 atec-p-0" style="vertical-align: bottom; margin-left: 10px; height: 24px; border-color: ', esc_attr($color), '; border-radius: 5px;">
					<div id="atec_admin_bar" title="Toggle admin bar display" style="width:76px;">
						<div style="font-size: 22px;" class="atec-dilb ', esc_attr(atec_dash_class('dashboard')), '"></div>
						<div class="atec-ckbx atec-dilb atec-ckbx-mini">
							<label class="switch" for="check_', esc_attr($id), '" onclick="location.href=\'', esc_url($url), '&action=adminBar&nav=',esc_attr($nav),'&_wpnonce=',esc_attr($nonce),'\'">
								<input name="check_', esc_attr($id), '" type="checkbox" value="', esc_attr($adminBar), '"', checked($adminBar,true,true), '>
								<div class="slider round"></div>
							</label>
						</div>
					</div>
				</div>';
			}
			
			if ($approved)
			{
				echo '<a class="atec-fs-12 atec-nodeco atec-btn-small atec-ml-10 atec-mt-0" style="border-color: ', esc_attr($color), ';" href="', esc_url($wordpress.$plugin.'/reviews/#new-post'), '" target="_blank">'; atec_dash_span('admin-comments'); echo '&nbsp;', esc_attr__('Post a review','atec-cache-apcu'), '</a>';
			}		
		echo '
		</div>
	</div>';
	return $licenseOk;
}

function atec_reg_style($id,$dir,$css,$ver): void { wp_register_style($id, plugin_dir_url($dir).'assets/css/'.$css, [], esc_attr($ver)); wp_enqueue_style($id); } 
function atec_reg_script($id,$dir,$js,$ver): void { wp_register_script($id, plugin_dir_url($dir).'assets/js/'.$js, [], esc_attr($ver),true); wp_enqueue_script($id); } 
function atec_reg_inline_style($id, $css_safe):void { $id=($id==='')?'atec-css':'atec_'.$id; wp_register_style($id, false, [], '1.0.0'); wp_enqueue_style($id); wp_add_inline_style($id, $css_safe); }
function atec_reg_inline_script($id, $js_safe, $jquery=false):void { $id='atec_'.$id; wp_register_script($id, false, $jquery?array('jquery'):array(), '1.0.0', false); wp_enqueue_script($id); wp_add_inline_script($id, $js_safe); }

function atec_little_block($str,$tag='H3',$class='atec-head',$classTag=''): void 
{ echo '<div class="',esc_attr($class),'"><',esc_attr($tag),' class="',esc_attr($classTag),'">',esc_html($str),'</',esc_attr($tag),'></div>'; }

function atec_little_block_with_info($str,$arr,$class='',$buttons=[],$url='',$nonce='',$nav='',$right=true): void
{
	$iconPath=plugins_url('assets/img/icons/',__DIR__);
	$reg = '/#([\-|\w]+)\s?(.*)/i';
	echo '
	<div class="atec-db atec-mb-10" style="padding-bottom: 5px; border-bottom: solid 1px rgba(208, 208, 208, 0.65);">
		<div class="atec-dilb atec-mr-10">'; atec_little_block($str,'H3','atec-head atec-mb-0'); echo '</div>';
		if (!empty($buttons)) 
			foreach ($buttons as $b)
			{ 
				echo 
				'<div class="atec-dilb atec-mr-10 atec-vat">';
				$lower=strtolower($b);
				if ($lower!==$b) atec_nav_button_confirm($url,$nonce,$lower,$nav,$lower==='update'?'Reload':'Delete');
				else atec_nav_button($url,$nonce,$lower,$nav,$lower==='update'?'Reload':'Delete'); 
				echo 
				'</div>'; 
			}
		echo '
		<div class="atec-dilb ', $right?'atec-float-right':'', '">';
			foreach ($arr as $key => $value)
			{ 
				preg_match($reg, $key, $matches);
				echo
				'<span class="atec-dilb atec-border-tiny atec-ml-5 atec-bg-w85">',
					'<strong>'; 
						// @codingStandardsIgnoreStart | Image is not an attachement
						if (isset($matches[2])) echo '<img class="atec-sys-icon" src="', esc_url($iconPath.$matches[1].'.svg'), '">', esc_attr($matches[2]);
						else echo esc_attr($key);
						// @codingStandardsIgnoreEnd
					echo 
					': </strong>',
					'<span class="', esc_attr($class), '">';
						preg_match($reg, $value, $matches);
						if (isset($matches[2])) atec_dash_span($matches[1]);
						else echo esc_attr($value);
					echo 
					'</span>',
				'</span>'; 
			}
		echo 
		'</div>
	</div>';
}

function atec_little_block_with_button($str,$url,$nonce,$action,$nav,$button,$primary=false,$simple=false,$float=true): void
{
	if (gettype($action)!=='array') { $action=array($action); $nav=array($nav); $button=array($button); $primary=array($primary); }
	echo '
	<div>',
		'<div class="atec-dilb">'; atec_little_block($str); echo '</div>';
		$c=0;
		foreach($action as $a)
		{
			echo 
			'<div class="atec-dilb atec-vat', $float?' atec-right':' atec-ml-20', '">'; atec_nav_button($url,$nonce,$action[$c],$nav[$c],$button[$c],$primary[$c],$simple); echo '</div>';
			$c++;
		};
	echo '
	</div>';
}
?>