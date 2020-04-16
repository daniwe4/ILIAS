/**
 * Handle multi values for select and text input fields
 */
var ilTagMultiForm = {
	tag_container: 'tbody.tcont',
	tag_row: 'tr.trow',
	tag_button: 'singlechoice',

	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function(row) {
		$(row).find('input:text').val('');
		$(row).find('input:hidden').val('');
		$(row).find('.ColorExam > a').css('background-color', "#FFFFFF");
		$(row).find('.allocs').html('');
	},

	reindexRows: function(tbody) {
		var that = this;
		var rowindex = 0;

		// process all rows
		$(tbody).find(this.tag_row).each(function() {

			// name
			$(this).find('input:text[id*="[name]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			// color
			$(this).find('input:text[id*="[color]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			// hidden id
			$(this).find('input:hidden[id*="[id]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			// button
			$(this).find('button .btn-link').each(function() {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			rowindex++;
		});
	},

	initEvents: function(rootel) {
		var that = this;

		if (typeof tinyMCE == 'undefined' || $(rootel).closest('table').find('textarea').size() == 0) {
			$(rootel).find('button.' + this.tag_button + '_add').click(function(e) {
				that.addRow(e);
			});
			$(rootel).find('button.' + this.tag_button + '_remove').click(function(e) {
				that.removeRow(e);
			});
			$(rootel).find('button.' + this.tag_button + '_up').click(function(e) {
				that.moveRowUp(e);
			});
			$(rootel).find('button.' + this.tag_button + '_down').click(function(e) {
				that.moveRowDown(e);
			});
		} else {
			// skip the javascript functionality if tinyMCE is running
			$(rootel).find('button.' + this.tag_button + '_add').attr("type", "submit");
			$(rootel).find('button.' + this.tag_button + '_remove').attr("type", "submit");
			$(rootel).find('button.' + this.tag_button + '_up').attr("type", "submit");
			$(rootel).find('button.' + this.tag_button + '_down').attr("type", "submit");
		}
	}
};

var ilWizardTagInput = {

	init: function() {
		this.initEvents($(this.tag_container));
	},

	initEvents: function(rootel) {
		var that = this;
		$(rootel).find('button.' + this.tag_button + '_add').click(function(e) {
			that.addRow(e);
		});	
		$(rootel).find('button.' + this.tag_button + '_remove').click(function(e) {
			that.removeRow(e);
		});	
		$(rootel).find('button.' + this.tag_button + '_up').click(function(e) {
			that.moveRowUp(e);
		});	
		$(rootel).find('button.' + this.tag_button + '_down').click(function(e) {
			that.moveRowDown(e);
		});
	},

	addRow: function(e) {
		// clone row
		var source = this.getRowFromEvent(e);
		var target = $(source).clone();

		// add events
		this.initEvents(target);

		// empty inputs
		this.cleanRow(target);
		
		$(source).after(target);

		this.reindexRows(this.getContainerFromEvent(e));
	},
	
	removeRow: function(e) {
		var source = this.getRowFromEvent(e);
		var tbody = this.getContainerFromEvent(e);

		// do not remove last row
		if($(tbody).find(this.tag_row).size() > 1) {
			$(source).remove();
		}
		// reset last remaining row
		else {
			this.cleanRow(source);
		}

		this.reindexRows(tbody);
	},

	moveRowUp: function(e) {
		var source = this.getRowFromEvent(e);
		var prev = $(source).prev();
		if(prev[0])
		{
			$(prev).before(source);

			this.reindexRows(this.getContainerFromEvent(e));
		}
	},

	moveRowDown: function(e) {
		var source = this.getRowFromEvent(e);
		var next = $(source).next();
		if(next[0])
		{
			$(next).after(source);

			this.reindexRows(this.getContainerFromEvent(e));
		}
	},

	handleId: function(el, attr, new_idx) {
		var parts = $(el).attr(attr).split('[');
		parts.pop();
		parts.push(new_idx + ']');
		$(el).attr(attr, parts.join('['));
	}
};

$(document).ready(function() {
	var ilSingleTagWizardInput = $.extend({}, ilWizardTagInput, ilTagMultiForm);
	ilSingleTagWizardInput.init();
});