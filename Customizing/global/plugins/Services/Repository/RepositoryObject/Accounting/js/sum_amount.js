il = il || {};
il.TMS = il.TMS || {};
il.TMS.accounting = il.TMS.accounting || {};
il.TMS.accounting.grosslink = '';

$(document).ready(function() {
	$(".all_amounts").children("input").focusout(function(){
		updateGross(this, "amount");
	});


	function calcSum(src) {
		tbl = $(src).closest("table");
		var sum_net = 0;

		tbl.find(".all_amounts").children("input[id^=amount]").each(function(index, elem){
			var val = floatGerToNumeric($(elem).val());
			if( !isNaN( val ) ) {
				sum_net += val;
			}
			$(elem).val(floatNumericToGer(val));
		});

		$("#summe").val(floatNumericToGer(sum_net));

		var sum_gross = 0;
		tbl.find(".all_gross").children("input[id^=gross]").each(function(index, elem){
			var val = floatGerToNumeric($(elem).val());
			if( !isNaN( val ) ) {
				sum_gross += val;
			}
		});
		$("#sum_gross").val(floatNumericToGer(sum_gross));
	}

	$("select[name^='tax']").change(function(event) {
		updateGross(event.target, "tax");
	});

	function updateGross(target, source_name) {
		var name_gross = "gross";
		var name_amount = "amount";
		var name_tax = "tax";
		var pos = target.id.substr(source_name.length);
		var sel_tax = "#"+name_tax+pos;
		var vr_id = $(sel_tax).val();

		$.getJSON(il.TMS.accounting.grosslink,"vr_id="+vr_id, function(data) {
			var vr_value = data["vr_value"];
			var sel_amount = "#"+name_amount+pos;
			var sel_gross = "#"+name_gross+pos;
			var amount = $(sel_amount).val();
			amount = floatGerToNumeric(amount);

			var gross = amount * ((100 + vr_value) / 100);
			$(sel_gross).val(floatNumericToGer(gross));

			calcSum(target);
		});
	}

	function floatGerToNumeric(ger_float_string)
	{
		return parseFloat(ger_float_string.replace(',','.'));
	}

	function floatNumericToGer(float)
	{
		return float.toFixed(2).replace('.',',');
	}
});
