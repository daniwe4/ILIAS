var TEPCalendar = {
	tag_event: '.il_calevent.session',

	init: function()
	{
		$(this.tag_event).hover(this.highlight, this.lowlight);
		$('.tep_col_selector .dropdown-menu').click(
			function(event){
				event.stopPropagation();
			}
		);
	},

	highlight: function(e)
	{
		var id = $(e.currentTarget).attr('data-eventid');
		$('.il_calevent.session.' + id).addClass('highlighted');
	},

	lowlight: function(e)
	{
		var id = $(e.currentTarget).attr('data-eventid');
		$('.il_calevent.session.' + id).removeClass('highlighted');
	},

	submitTutorAssign: function(id)
	{
		var form = $('.tep-form.session#' + id)
			.find('.assigntutor form');
		form.submit();
	},

	submitTutorAdd: function(form_id, param)
	{
		var form = $('#' + form_id),
			action = form.attr('action'),
			tutor_id = form.find('.form-control.tep-tutor-add-selection')[0].value
			add_frm = form.parents('.session-forms').children('.addtutor')[0],
			assign_frm = form.parents('.session-forms').children('.assigntutor')[0];

		$('.tep-form.session .blur').addClass('active');

		action = action + '&' + param  + '=' + tutor_id;
		this.addTutorAndRefreshForms(action, add_frm, assign_frm);
	},

	addTutorAndRefreshForms: function(url, add_form_wrapper, assign_form_wrapper)
	{
		$.ajax(
			{
				url: url,
				dataType: 'html'
			}
		).done(function(html) {
			var parts = html.split('##SPLIT##');
			$(add_form_wrapper).children().first().replaceWith(parts[0]);
			$(assign_form_wrapper).children().first().replaceWith(parts[1]);
			$('.tep-form.session .blur').removeClass('active');
		});
	}

};

$(document).ready(function() {
	TEPCalendar.init();
});