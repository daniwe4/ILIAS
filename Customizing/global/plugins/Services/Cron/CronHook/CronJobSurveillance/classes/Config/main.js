$(document).ready(function() {
	getNewId = function() {
		var new_id = 0;
		var tmp_id = 0;

		$('div[id*="ilFormField~"]').each(function() {
			tmp_id = $(this).attr('id').split('~')[2];
			tmp_id = parseInt(tmp_id);

			if (tmp_id > new_id) {
				new_id = tmp_id;
			}
		});
		new_id = new_id + 1;
		return new_id;
	}

	/**
	 * Bind click events and handle preset values
	 */
	ilMultiFormValues.init = function() {
		// add click event to +-icons
		$('button[id*="ilMultiAdd"]').on('click', function(e) {
			ilMultiFormValues.addEvent(e);
		});
		// add click event to --icons
		$('button[id*="ilMultiRmv"]').on('click', function(e) {
			ilMultiFormValues.removeEvent(e);
		});
		// add click event to down-icons
		$('button[id*="ilMultiDwn"]').on('click', function(e) {
			ilMultiFormValues.downEvent(e);
		});
		// add click event to up-icons
		$('button[id*="ilMultiUp"]').on('click', function(e) {
			ilMultiFormValues.upEvent(e);
		});
		// return triggers add  (BEFORE adding preset items)
		$('button[id*="ilMultiAdd"]').each(function() {
			var id = $(this).attr('id').split('~');
			// only text inputs are supported yet
			$('div[id="ilFormField~' + id[1] + '~' + id[2] + '"]').find('input:text[id*="' + id[1] + '"]').on('keydown', function(e) {
				ilMultiFormValues.keyDown(e);
			});
		});

		// handle preset values (in hidden inputs)
		$('input[id*="ilMultiValues"]').each(function() {
			ilMultiFormValues.handlePreset(this);
		});
	}

	/**
	 * Add multi item (click event)
	 *
	 * @param event e
	 */
	ilMultiFormValues.addEvent = function(e) {
		var id = $(e.delegateTarget).attr('id').split('~');
		ilMultiFormValues.add(id[2], '', '');
	}

	/**
	 * Remove multi item (click event)
	 *
	 * @param event e
	 */
	ilMultiFormValues.removeEvent =  function(e) {
		var id = $(e.delegateTarget).attr('id').split('~');
		console.log(id);
		if($('div[id*="ilFormField~~"]').length > 1) {
			$('div[id="ilFormField~~' + id[2] + '"]').remove();
		}
		else {
			$('div[id="ilFormField~~' + id[2] + '"]').find('input[id*="tolerance"]').val("");
			$('div[id="ilFormField~~' + id[2] + '"]').find('select[id*="select"]').find('option').each(function() {
				$(this).removeAttr('selected');
			});
		}
	}

	ilMultiFormValues.setValue = function(element, tolerance, selected) {
		if ($(element).attr('id') == undefined) {
			return;
		}
		var group_id = $(element).attr('id').split('~');
		var element_id = group_id[1];

		$(element).find('input[id*="tolerance"]').each(function() {
			if(tolerance == null) {
				$(this).val('');
			} else {
				$(this).val(tolerance);
			}
		});

		$(element).find('select').each(function() {
			if(selected == "") {
				$(this).find("option").each(function() {
					$(this).removeAttr('selected');
				});
			} else {
				$(this).find("option").each(function() {
					if($(this).attr('value') == selected) {
						$(this).attr('selected', 'selected');
					}
				});
			}
		});

		// return triggers add
		$(element).find('input:text[id*="' + element_id + '"]').on('keydown', function(e) {
			ilMultiFormValues.keyDown(e);
		});

		return;
	};

	ilMultiFormValues.add = function(element_id, tolerance, selected) {
		var new_id = getNewId();
		var original_element = $('div[id="ilFormField~~'+element_id+'"]');
		var new_element = $(original_element).clone();

		// fix id of cloned element
		$(new_element).attr('id', 'ilFormField~~' + new_id);

		// binding +-icon
		$(new_element).find('[id*="ilMultiAdd"]').each(function() {
			$(this).attr('id', 'ilMultiAdd~~' + new_id);
			$(this).on('click', function(e) {
				ilMultiFormValues.addEvent(e);
			});
		});

		// binding --icon
		$(new_element).find('[id*="ilMultiRmv"]').each(function() {
			$(this).attr('id', 'ilMultiRmv~~' + new_id);
			$(this).on('click', function(e) {
				ilMultiFormValues.removeEvent(e);
			});
		});

		ilMultiFormValues.setValue(new_element, tolerance, selected);

		$(original_element).after(new_element);

		if(selected)
		{
			$(document).find('input:hidden[id="ilMultiValues~' + element_id + '"]').each(function() { 
					$(this).remove();
			});
		}
	};

	/**
	 * Use value from hidden item to add preset multi items
	 *
	 * @param node element
	 */
	ilMultiFormValues.handlePreset = function(element) {
		var element_id = $(element).attr('id').split('~');
		var element_id = element_id[1];

		var values = $(element).attr('value').split('~');

		var selected = values[0];
		var tolerance = values[1];
		ilMultiFormValues.add(element_id, tolerance, selected);
	}

	posSelect = $('#select').offset();
	posTolerance = $('[name*="tolerance"]').offset();
	posHeaderSelect = $('#header-select').offset();
	posHeaderTolernace = $('#header-tolerance').offset();

	$('#header-tolerance').offset({ left: posTolerance.left + 3, top: posHeaderTolernace.top});
	$('#header-select').offset({ left: posSelect.left + 3, top: posHeaderSelect.top});
})