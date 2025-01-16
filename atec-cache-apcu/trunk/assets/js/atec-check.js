function atec_check_validate(id) 
{
	let $check = jQuery('#check_'+id);
	let checked = $check.attr('checked')==='checked';
	if (checked) $check.removeAttr('checked');
	else $check.attr('checked',true);
}
