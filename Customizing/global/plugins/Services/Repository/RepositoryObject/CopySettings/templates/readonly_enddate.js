$(document).ready(function() {
	$('input[name="course_period[end]"]').attr('readonly', true);

	var re = /session_(start|end)\[(\d+)\]\[(hh|mm)\]/;
	var select_boxes = {};

	$('select[name^="session_"]').each(function(k, item) {
		var match = re.exec(item.getAttribute("name"));
		var ref_id = match[2];
		var start_end = match[1];
		var hh_mm = match[3];
		if (!(ref_id in select_boxes)) {
			select_boxes[ref_id] = { "start" : {}, "end" : {}};
		}
		if (hh_mm in select_boxes[ref_id]) {
			console.log("select box '"+item.getAttribute("name")+"' already registered.");
		}
		select_boxes[ref_id][start_end][hh_mm] = item;
	});

	var maybe_prepend_zero = function(val) {
		if (val < 10 && val > 0) {
			return "0" + String(val);
		}
		return String(val);
	}

	for (var ref_id in select_boxes) {
		var start_hh = select_boxes[ref_id]["start"]["hh"];
		var end_hh = select_boxes[ref_id]["end"]["hh"];
		var start_mm = select_boxes[ref_id]["start"]["mm"];
		var end_mm = select_boxes[ref_id]["end"]["mm"];

		(function(start_hh, end_hh, start_mm, end_mm) {
			var previous_hh;
			start_hh.addEventListener("focus", function() {
				previous_hh = this.value;
			});
			start_hh.addEventListener("change", function() {
				var new_val = Number(end_hh.value) + Number(this.value) - Number(previous_hh);
				if (new_val <= 0 || new_val > 23) {
					start_hh.value = previous_hh;
					return;
				}
				end_hh.value = new_val;
				previous_hh = Number(this.value);
			});

			var previous_mm;
			start_mm.addEventListener("focus", function() {
				previous_mm = this.value;
			});
			start_mm.addEventListener("change", function() {
				var new_val = Number(end_mm.value) + Number(this.value) - Number(previous_mm);
				if (new_val < 0) {
					end_hh.value = maybe_prepend_zero(Number(end_hh.value) - 1);
					new_val = 60 + new_val;
				}
				if (new_val >= 60) {
					end_hh.value = maybe_prepend_zero(Number(end_hh.value) + 1);
					new_val = new_val - 60;
				}
				end_mm.value = new_val
				previous_mm = Number(this.value);
			});
		})(start_hh, end_hh, start_mm, end_mm);
	}
});

