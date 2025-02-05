function atec_check_validate(id) 
{
	const check = document.getElementById("check_"+id);
	let checked = check.getAttribute("checked")!==null;
	if (checked) { check.removeAttribute("checked"); check.checked = false; check.value=0; }
	else { check.setAttribute("checked", "true"); check.checked = true; check.value=1; }
}
