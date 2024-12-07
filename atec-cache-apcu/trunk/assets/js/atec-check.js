function atec_check_validate(id) 
{
	let $check=jQuery('#check_'+id);
	let checked=$check.val()=='true';
	$check.val(checked?'false':'true');
	$check.prop('checked',checked?false:true);
}
