$(document).ready(function() {
	$(document).on("change","#f_category","", changeCategories);
});

/**
*change the elements in select input ui for building block
*
*/
function changeCategories() {
	var category_id = $('#f_category option:selected').val();
	var json_link = $('#json_link').val();

	$.getJSON(json_link, "category_id="+category_id, function(data) {
		var items = [];
		$('#f_topics').empty();
		$.each(data, function(key,val) {
			var option = '<div style="white-space:nowrap"><input type="checkbox" name="f_topics[]" id="f_topics_' + key + '" value="' + key + '">';
			option += '&nbsp;<label for="f_topics_' + key + '">' + val + '</label></div>';
			items.push(option);
		});

		$('#f_topics').append(items.join(""));
	});
}