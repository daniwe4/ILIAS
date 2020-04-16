il = il || {};
il.TMS = il.TMS || {};
il.TMS.agenda = il.TMS.agenda || {};

(function($, agenda) {
	agenda.addNewRow = (function($, agenda) {
		var table_data = {};

		var _startsWith = function(needle, haystack) {
			return haystack.substr(0, needle.length) === needle;
		};

		var _incrementIndexInName = function(name) {
			var start = name.indexOf('['),
				stop = name.indexOf(']'),
				index = name.substring(start + 1, stop);

			index = parseInt(index) + 1;
			return _replaceIndexInName(name, index.toString());
		};

		var _replaceIndexInName = function(name, index) {
			//name is something[INDEX]something
			var start = name.indexOf('['),
				stop = name.indexOf(']'),
				ret = name.substr(0, start+1) + index + name.substr(stop);

			return ret;
		};

		var _getNextPosition = function (rows) {
			return rows.length * 10 + 10;
		};

		var rowAdding = function() {
			var rows = $('#agenda_entries tbody').children();

			row = $('#agenda_entries tbody tr:last').clone();

			position = $(row).find('.small input.form-control');
			position.val(_getNextPosition(rows));

			hidden_id = $(row).find('.hidden_id');
			hidden_id.val(parseInt(hidden_id.val()) + 1);

			selects = $(row).find('select');
			$.each(selects, function(k, v) {
				$(v).val($(v).find('option:first').val());

				name = $(v).attr('name');
				if(_startsWith('duration[', name)
				) {
					$(v).attr('name', _incrementIndexInName(name));
				}

				if(_startsWith('pool_item[', name)
				) {
					$(v).attr('name', _incrementIndexInName(name));
				}
			});

			textareas = $(row).find('textarea');
			$.each(textareas, function(k, v) {
				name = $(v).attr('name');
				$(v).attr('name', _incrementIndexInName(name));
			});

			textareas = $(row).find('input[type=text]');
			$.each(textareas, function(k, v) {
				name = $(v).attr('name');
				$(v).attr('name', _incrementIndexInName(name));
			});

			var hidden = $(row).find('input[type="hidden"]');
			$.each(hidden, function(k, v) {
				name = v.name;
				if(_startsWith('hidden_id[', name)) {
					nu_name = _incrementIndexInName(name);
					$(v).attr('name', nu_name);
					v.value = -1;
				}
			});

			blanks = $(row).find('.agenda_item_blank');
			$.each(blanks, function(k, v) {
				name = $(v).attr('name');
				$(v).attr('name', _incrementIndexInName(name));
			});

			start_time = $(row).find('.day_start_time');
			start_time.val(il.TMS.agenda.start_time);

			item_start = $(row).find('.agenda_item_start');
			var duration = 0;
			$.each(rows, function(k, v) {
				duration += parseInt($(v).find('.agenda_item_duration option:selected').val());
			});
			item_start.html(il.TMS.agenda.itemselection.toHumanReadableTime(il.TMS.agenda.start_time + duration));

			checkbox = $(row).find('input[type="checkbox"]').first();
			name = $(checkbox).attr('name');
			$(checkbox).attr('name', _incrementIndexInName(name));

			no_edit = row.find('.non-edit');
			blank = row.find('.blank');
			edit_fixed = row.find('.edit-fixed');

			no_edit.html('');

			txt = $(blank).find('textarea');
			$(txt).val('');
			txt = $(edit_fixed).find('textarea');
			$(txt).val('');
			$(no_edit).removeClass("hide");
			$(edit_fixed).addClass("hide");
			$(blank).addClass("hide");

			row.find('.agenda_item_blank').val(0);

			if(il.TMS.agenda.edu_active == 1) {
				row.find('.agenda_item_iddrelevance').html('');
				_resetIDDTime(row);
			}

			$('#agenda_entries tbody').append(row);
		}

		var _resetIDDTime = function(row) {
			var idd_time_cell = row.find('.agenda_idd_time');
			idd_time_cell.html('00:00');
		}

		var updateStartTime = function() {
			row = $('#agenda_entries tbody tr:last');
			start_time = $(row).find('.day_start_time');
			start_time.val(il.TMS.agenda.start_time);
		}

		var updateTable = function() {
			row = $('#agenda_entries tbody tr:last');
			row = row.clone();

			if(
				typeof table_data !== 'undefined' &&
				Object.keys(table_data).length > 0
			) {
				$('#agenda_entries tbody').html('');
			}

			$.each(table_data, function(dk, data) {

				new_row = row.clone();
				selects = new_row.find('select');
				$.each(selects, function(sk, select) {
					name = select.name;


					if(_startsWith('pool_item', name)) {
						if(data.pool_item != "0") {
							$(select).val(data.pool_item);
						}
						throw_change_on = select;
						$(select).attr('name', _replaceIndexInName(name, dk));
					}

					if(_startsWith('duration[', name)) {
						$(select).val(parseInt(data.duration));
						$(select).attr('name', _replaceIndexInName(name, dk));
					}

				});

				textareas = new_row.find('textarea');
				$.each(textareas, function(k, v) {
					name = v.name;
					nu_name = _replaceIndexInName(name, dk);
					$(v).attr('name', nu_name);

					if(data.is_blank === 1 || data.edit_fixed === 1) {
						if(_startsWith('content[', name)) {
							window.setTimeout(function() {
								v.value = data.content;
							}, 1);
						}
						if(_startsWith('goals[', name)) {

							window.setTimeout(function() {
								v.value = data.goals;
							}, 1);
						}
					}
				});

				blanks = $(new_row).find('.agenda_item_blank');
				$.each(blanks, function(k, v) {
					name = $(v).attr('name');
					$(v).attr('name', _replaceIndexInName(name, dk));
					$(v).val(data.is_blank);
				});

				checkbox = $(new_row).find('input[type="checkbox"]').first();
				name = $(checkbox).attr('name');
				$(checkbox).attr('name', _replaceIndexInName(name, dk));
				$(checkbox).val(-1);

				var position = $(new_row).find('input[type="text"]').first();
				name = $(position).attr('name');
				$(position).attr('name', _replaceIndexInName(name, dk));
				position.val(data.position);

				var hidden = $(new_row).find('input[type="hidden"]');
				$.each(hidden, function(k, v) {
					name = v.name;
					if(_startsWith('hidden_id[', name)) {
						nu_name = _replaceIndexInName(name, dk);
						$(v).attr('name', nu_name);
						v.value = -1;
					}
				});

				$('#agenda_entries tbody').append(new_row);

				if(typeof throw_change_on !== 'undefined' && throw_change_on.onchange) {
					throw_change_on.onchange();
				}
			});
		}

		var addData = function(key, data) {
				table_data[key] = data;
		};

		return {
			rowAdding: rowAdding,
			addData: addData,
			updateTable: updateTable,
			updateStartTime: updateStartTime
		}
	})($, agenda);
})($, il.TMS.agenda);
