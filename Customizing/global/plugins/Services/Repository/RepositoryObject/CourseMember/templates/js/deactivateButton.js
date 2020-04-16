$(document).ready(function() {
	$("form").submit(function(e){
		$("#submitBtn").attr("readonly", "readonly");
		$("#submitBtn").on("click", function() {
			return false;
		});
		$("#submitBtn").css(
			"background-color", "#b0b0b0").css(
			"color", "white").css(
			"border-color", "#e0e0e0");

		return true;
	});
});