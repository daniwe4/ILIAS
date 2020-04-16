il = il || {};
il.TMS = il.TMS || {};
il.TMS.agenda = il.TMS.agenda || {};

(function ($, agenda) {
	agenda.itemselection = (function ($) {
		var _lookup = {};
		var _current_lookup = {};
		var _total_idd_time = 0;

		var FIXED = 'non-edit';
		var BLANK = 'blank';
		var EDIT = 'edit-fixed';

		var CONTENT = 'agenda_item_contents';
		var GOALS = 'agenda_item_goals';

		var addToLookup = function (key, data) {
			_lookup[key] = data;
		}
		var addCurrentToLookup = function (key, data) {
			_current_lookup[key] = data;
		}

		var init = function() {
			var rows = $("#agenda_entries").find('tbody').children();
			_switchTimes(rows);

			rows.each(function() {
				var idd_rel = $(this).find('.agenda_item_iddrelevance')
					selected_id = $(this).find('.agenda_item_selection option:selected').val()
				;
				if (idd_rel.html() === undefined || idd_rel.html().trim() === "Nein") {
					_resetIDDTime($(this));
				} else {
					_setIDDTime($(this));
				}

				switchRowInputMode($(this), selected_id);
				switchContentAndGoals($(this), selected_id);
				_switchIsBlank($(this), selected_id);
			});

			_setTotalIddTime(rows);
		};

		var onItemSelect = function (element, value) {
			var row = $(element).closest('tr'),
				rows = $(element).closest('tbody').children(),
				idd_rel = row.find('.agenda_item_iddrelevance'),
				total_idd_field = $("#total_idd_time")
			;

			switchRowInputMode(row, value);
			switchContentAndGoals(row, value);

			_switchIddTime(row, value);
			_switchTimes(rows, value);
			_switchIsBlank(row, value);

			if (!value) {
				idd_rel.html('');
				if (_eduActive()) {
					_resetIDDTime(row);
				}
			} else {
				idd_rel.html(_lookup[value].idd_relevance);

				if (_lookup[value].calc_idd_time === 1 && _eduActive()) {
					_setIDDTime(row);
				} else if (_eduActive()) {
					_resetIDDTime(row);
				}
			}

			_setTotalIddTime(rows);
		};

		var _setTotalIddTime = function (rows) {
			var total_idd_field = $("#total_idd_time")
				sum_duration = 0
			;

			rows.each(function() {
				var duration = $(this).find(".agenda_item_duration option:selected").val(),
					idd_rel = $(this).find('.agenda_item_iddrelevance')
				;

				if (idd_rel.html() !== undefined && idd_rel.html().trim() === "Ja") {
					sum_duration += parseInt(duration);
				}
			});

			total_idd_field.html(toHumanReadableDuration(sum_duration));
		};

		var _switchTimes = function (rows) {
			var start = parseInt(il.TMS.agenda.start_time),
				sum_duration = 0
			;

			rows.each(function (index, row) {
				var start_element = $(row).find(".agenda_item_start"),
					end_element = $(row).find(".agenda_item_end"),
					duration = parseInt($(row).find(".agenda_item_duration option:selected").val());
				;

				$(start_element).html(toHumanReadableTime(sum_duration + start));
				$(end_element).html(toHumanReadableTime(duration + start + sum_duration));
				sum_duration += duration;
			});

			_setTotalIddTime(rows);
		};

		var toHumanReadableTime = function (minutes) {
			var h = parseInt(minutes / 60),
				m = minutes % 60
			;

			while(h >= 24) {
				h = h - 24;
			}

			if (h < 10) {
				h = "0"+h;
			}

			if (m < 10) {
				m = "0"+m;
			}

			return h + ":" + m;
		}

		var toHumanReadableDuration = function (minutes) {
			var h = parseInt(minutes / 60),
				m = minutes % 60
			;

			if (h < 10) {
				h = "0"+h;
			}

			if (m < 10) {
				m = "0"+m;
			}

			return h + ":" + m;
		};

		var _switchIddTime = function (row, value) {
			var item_duration = $(row).find('.agenda_item_duration'),
				hidden_id = _getHiddenId($(row)),
				duration = "0";
			;

			if (typeof (_current_lookup[hidden_id]) !== 'undefined') {
				if (_current_lookup[hidden_id].key === value) {
					duration = _current_lookup[hidden_id].duration;
				}
				_setTimeCell(item_duration, duration);
			}
		};

		var _setTimeCell = function (cell, duration) {
			var input = cell.find('select');
			input.val(duration);
		};

		var registerTimeInputEvents = function () {
			$(document).on('change', '.agenda_item_duration select', onTimeChange);
		};

		/**
		 * Get selection-value and re-calc time;
		 */
		var onTimeChange = function (event) {
			var row = $(this).closest('tr'),
				rows = $(this).closest('tbody').children(),
				selection = row.find('.agenda_item_selection select option:selected'),
				value = selection.val()
			;

			if (_lookup[value] && _lookup[value].calc_idd_time && _eduActive()) {
				_setIDDTime(row);
			} else if (_eduActive()) {
				_resetIDDTime(row);
			}
			_switchTimes(rows);
		};

		/**
		 * calculate and set IDD-time.
		 * @param HTMLElement row
		 */
		var _setIDDTime = function (row) {
			var idd_time_cell = row.find('.agenda_idd_time'),
				idd_duration = row.find('.agenda_item_duration option:selected'),
				idd_time_str = _calcIDDTime(idd_duration.val())
			;

			idd_time_cell.html(idd_time_str);
		};

		var _resetIDDTime = function (row) {
			var idd_time_cell = row.find('.agenda_idd_time'),
				duration = row.find('.agenda_item_duration option:selected').val()
			;

			idd_time_cell.html('00:00');

		};

		var _getHiddenId = function (row) {
			var inputs = row.find('input'),
				ret = -1;

			inputs.each(function (index, i) {
				if (i.name.substr(0, i.name.length - 3) === 'hidden_id') {
					ret = i.value;
				}
			});

			return ret;
		};

		/**
		 * calulate the idd-time.
		 */
		var _calcIDDTime = function (duration) {
			hh = '0';
			mm = '0';

			if (duration > 0) {
				minutes = duration;
				hh = Math.floor(minutes / 60);
				mm = minutes - (hh * 60);
				hh = hh.toString();
				mm = mm.toString();
			}

			if (hh.length == 1) {
				hh = '0' + hh;
			}

			if (mm.length == 1) {
				mm = '0' + mm;
			}

			return [hh, mm].join(':');
		};


		var getRowInput = function(row, element_class, which) {
			query = '.'
			if(which) {
				query = query + which + ' .'
			}
			query = query + element_class;
			return row.find(query);
		};


		var setRowInputMode = function(row, mode) {
			$([FIXED, BLANK, EDIT]).each(function(index, element_class){
				getRowInput(row, element_class).removeClass('hide'); //legacy
				if(element_class !== mode) {
					getRowInput(row, element_class)
						.hide()
						.find('textarea').attr("disabled", "disabled");
				}
			});
			getRowInput(row, mode)
				.show()
				.find('textarea').removeAttr("disabled");
		};

		var switchRowInputMode = function (row, value) {
			if (!value) {
				setRowInputMode(row, FIXED);
				return;
			}
			if (_lookup[value].is_blank === 1) {
				setRowInputMode(row, BLANK);
				return;
			}
			if (_lookup[value].edit_fixed === 1) {
				setRowInputMode(row, EDIT);
				return;
			}
			setRowInputMode(row, FIXED);
		};


		var switchContentAndGoals = function (row, value) {
			hidden_id = _getHiddenId(row);

			$(['contents', 'goals']).each(function(idx, column) {

				col_class = [CONTENT, GOALS][idx];

				non_edit = getRowInput(row, FIXED, col_class);
				edit_fixed = getRowInput(row, EDIT, col_class);
				blank = getRowInput(row, BLANK, col_class);

				if (!value) {
					non_edit.html('');
				} else {
					if (_lookup[value].is_blank === 1) {
						non_edit.html('');

						content = _lookup[value][column];

						if (typeof(_current_lookup[hidden_id]) !== 'undefined') {
							if (_current_lookup[hidden_id].key === value) {
								content = _current_lookup[hidden_id][column];
							}
						}

						//content = content.replace(/(?:<br \/>)/g, '\r\n');
						txt = $(blank).find('textarea');
						if (txt.val() == '') {
							txt.val(content);
						}

						txt = $(edit_fixed).find('textarea');

					} else {

						if (_lookup[value].edit_fixed === 1) {
							non_edit.html('');

							txt = $(blank).find('textarea');
							content = _lookup[value][column];

							if (typeof(_current_lookup[hidden_id]) !== 'undefined') {
								if (_current_lookup[hidden_id].key === value) {
									content = _current_lookup[hidden_id][column];
								}
							}

							//content = content.replace(/(?:<br \/>)/g, '\r\n');
							txt = $(edit_fixed).find('textarea');
							txt.val(content);
						} else {

							fixed_content = _lookup[value][column];
							content = fixed_content;

							edited_content = '';
							if(hidden_id > -1 && _current_lookup.hasOwnProperty(hidden_id)) {
								edited_content = _current_lookup[hidden_id][column];
							}

							if(	edited_content != '' &&
								value == _current_lookup[hidden_id].key
							) {
								content = edited_content;
							}

							content  = content.replace(/(?:\r\n|\r|\n)/g, '<br \/>');
							non_edit.html(content);
							$(blank).attr("");
						}
					}
				}

			});
		};

		/**
		 * Set hiddeninput for blank on true or not
		 */
		var _switchIsBlank = function (row, value) {
			blank = row.find('.agenda_item_blank');
			if (!value) {
				blank.val(0);
			} else {
				blank.val(_lookup[value].is_blank);
			}
		};

		var _eduActive = function () {
			return il.TMS.agenda.edu_active === 1;
		};

		return {
			addToLookup: addToLookup,
			addCurrentToLookup: addCurrentToLookup,
			onItemSelect: onItemSelect,
			onTimeChange: onTimeChange,
			registerTimeInputEvents: registerTimeInputEvents,
			init: init,
			toHumanReadableTime: toHumanReadableTime
		}

	})($);
})($, il.TMS.agenda);
