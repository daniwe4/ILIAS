$(document).ready(function() {
	id = $('#f_selected_value').val();

	$inputs = $('input[type="radio"]')
	$.each($inputs, function(k,v) {
		$(v).removeAttr("checked");
		if($(v).attr("name") == "f_destination"
			&& $(v).val() == id
		) {
			$(v).attr("checked", "checked");
		}
	});

	$("#il_expl2_jstree_cont_out_rep_exp_sel").removeClass("ilNoDisplay");
});