<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;

final class CHECK
{

	public static function form($slug, $hidden=[])
	{
		echo
		'<div class="atec-border-white">
			<form class="atec-form" method="post" action="options.php">';
				if (!empty($hidden))
				{
					foreach ($hidden as $key => $value)
					{ echo '<input type="hidden" name="', esc_attr($slug), '_settings[', esc_attr($key), ']" value="', esc_attr($value), '">'; }
				}
				settings_fields($slug);
				do_settings_sections($slug);
				TOOLS::submit_button();
			echo '
			</form>
		</div>';
	}

	// SANITIZE AREA START

	public static function sanitize_textarea(&$input, $arr)
	{ foreach($arr as $a) $input[$a] = sanitize_textarea_field($input[$a]??''); }

	public static function sanitize_key(&$input, $arr)
	{ foreach($arr as $a) $input[$a] = sanitize_key($input[$a]??''); }

	public static function sanitize_email(&$input, $arr)
	{ foreach($arr as $a) $input[$a] = sanitize_email($input[$a]??''); }

	public static function sanitize_text(&$input, $arr)
	{ foreach($arr as $a) $input[$a] = sanitize_text_field($input[$a]??''); }

	public static function sanitize_text_in_array(&$input, $inArr)
	{
		foreach($inArr as $key=>$arr)
		{ if (isset($input[$key])) $input[$key] = in_array($input[$key], $arr)?sanitize_text_field($input[$key]):$arr[0]; }
	}

	public static function sanitize_boolean(&$input, $arr)
	{ foreach($arr as $b) $input[$b] = filter_var($input[$b]??0,258); }

	public static function sanitize_url(&$input, $arr)
	{ foreach($arr as $a) $input[$a] = sanitize_url($input[$a]??''); }

	// SANITIZE AREA END

	public static function opt_arr($opt, $slug): array { return array('name'=>$opt, 'opt-name' => 'atec_'.$slug.'_settings' ); }
	public static function opt_arr_select($opt, $slug, $arr): array { $optArr= self::opt_arr($opt, $slug); return array_merge($optArr,['array'=>$arr]); }

	// BUTTON AREA START

	public static function checkbox_button_td($una, $action, $nav, $option, $disabled = false, $class = ''): void
	{ 
		echo '<td>';
		self::checkbox_button($una, $action, $nav, $option, $disabled, $class);
		echo '</td>';
	}

	public static function checkbox_button($una, $action, $nav, $option, $disabled = false, $class = ''): void
	{
		$id = uniqid('atec');
		$href=INIT::build_url($una, $action, $nav);
		echo '
		<div class="atec-ckbx', esc_html($class !== '' ? ' '.$class : ''), '">
			<label class="switch" for="check_', esc_attr($id), '" ', ($disabled ? 'class="check_disabled"' : ' onclick="location.href=\''.esc_url($href).'\'"'), '>
				<input name="check_', esc_attr($id), '"', ($disabled ? 'disabled="true"' : ''), ' type="checkbox" value="1"', checked(INIT::bool($option),true,true), '>
				<div class="slider round"></div>
			</label>
		</div>';
	}

	public static function checkbox_button_div($una, $action, $nav, $button, $option, $disabled=false, $pro=null): void
	{
		$href=INIT::build_url($una, $action, $nav);
		echo
		'<div', ($pro===false ? ' style="background: #f9f9f9; border: solid 1px #d0d0d0; border-radius: var(--px-3); marin-right: 10px;"' : '') ,'>';
			if ($pro===false)
			{
				$disabled=true;
				echo 
				'<a class="atec-nodeco atec-blue" href="', esc_url($href), '">
					<span class="atec-dilb atec-fs-9">'; TOOLS::dash_span('awards', 'atec-blue atec-fs-16'); echo 'PRO feature – please upgrade.</span>
				</a><br>';
			}
			echo
			'<div class="atec_checkbox_button_div atec-dilb">',
				esc_attr($button), '&nbsp;&nbsp;&nbsp;';
				self::checkbox_button($una, $action, $nav, $option, $disabled);
			echo
			'</div>
		</div>';
	}

	// BUTTON AREA END

	// INPUT AREA START

	public static function checkbox($args): void
	{
		$option = get_option($args['opt-name'],[]); $field= $args['name'];
		echo '
		<div class="atec-ckbx">
			<label class="switch" for="check_', esc_attr($field), '">
				<input type="checkbox" id="check_', esc_attr($field), '" name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']" value="1" onclick="atec_check_validate(\'', esc_attr($field), '\');" ', checked(filter_var($option[$field]??0,258),true,true), '/>
				<div class="slider round"></div>
			</label>
		</div>';
	}

	public static function input_select($args): void
	{
		$option = get_option($args['opt-name'],[]); $field = $args['name']; $value = $option[$field]??''; $arr = $args['array'];
		echo '<select name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']">';
		foreach ($arr as $key) { echo '<option value="'.esc_attr($key).'"', selected($value, $key), '>', esc_attr($key), '</option>'; }
		echo '</select>';
	}

	public static function input_text($args, $type= 'text'): void
	{
		$option = get_option($args['opt-name'],[]); $field= $args['name'];
		echo '<input id="ai_'.esc_attr($field).'" type="', esc_attr($type), '" name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']" value="', esc_attr($option[$field]??''), '">';
	}

	public static function input_color($args): void
	{
		$option = get_option($args['opt-name'],[]); $field= $args['name'];
		echo '<input id="ac_'.esc_attr($field).'" type="color" name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']" value="', esc_attr($option[$field]??''), '">';
	}

	public static function input_password($args): void { self::input_text($args, 'password'); }
	public static function input_url($args): void { self::input_text($args, 'url'); }

	public static function input_textarea($args): void
	{
		$option = get_option($args['opt-name'],[]); $field= $args['name'];
		echo '<textarea id="textarea_', esc_attr($field), '" style="resize:both;" rows="', (($args['size']??'')=== ''?'2':esc_attr($args['size'])), '" cols="30" name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']">', esc_textarea($option[$field]??''), '</textarea>';
	}

	// INPUT AREA END

}
?>