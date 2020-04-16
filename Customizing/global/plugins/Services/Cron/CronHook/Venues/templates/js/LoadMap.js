$(document).ready(function() {
	if($("input[id^=map_][id$=_addr]").val()) {
		setTimeout(function() {
			$("input[id^=map_][id$=_addr] + input[type=button]").trigger("click");
		}, 0);
	}
});