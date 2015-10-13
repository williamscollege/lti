$(document).ready(function () {

	// ***************************
	// Listeners
	// ***************************

	// Delete signup
	$(document).on("click", ".sus-delete-signup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "<p>Really delete <strong>" + $(this).attr('data-for-signup-name') + "'s</strong> signup for:<br /><strong>&quot;" + $(this).attr('data-for-sheet-name') + "&quot;</strong>?</p>" +
			'<p class="text-danger"><i class="glyphicon glyphicon-exclamation-sign" style="font-size: 18px;"></i>&nbsp;An alert will be sent to this user.</p>',
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup",
			ajax_id: GLOBAL_confirmHandlerData,
			opening_id: GLOBAL_confirmHandlerReference
		};
		showConfirmBox(params);
	});

	// Set initial condition: hide details
	$("#tabMySignups .toggle_opening_details").hide();
	$("#tabOthersSignups .toggle_opening_details").hide();

	// Display optional details
	$("#link_for_details_my_signups").click(function () {
		if ($("#link_for_details_my_signups").hasClass('wmsToggle')) {
			// hide details
			$("#link_for_details_my_signups").removeClass('wmsToggle').text('show details');
			$("#tabMySignups .toggle_opening_details").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
		else {
			// show details
			$("#link_for_details_my_signups").addClass('wmsToggle').text('hide details');
			$("#tabMySignups .toggle_opening_details").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}

	});

	$("#link_for_details_others_signups").click(function () {
		if ($("#link_for_details_others_signups").hasClass('wmsToggle')) {
			// hide details
			$("#link_for_details_others_signups").removeClass('wmsToggle').text('show details');
			$("#tabOthersSignups .toggle_opening_details").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
		else {
			// show details
			$("#link_for_details_others_signups").addClass('wmsToggle').text('hide details');
			$("#tabOthersSignups .toggle_opening_details").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
	});

	// Set initial condition: hide history
	$("#tabMySignups .toggle_opening_history").hide();
	$("#tabOthersSignups .toggle_opening_history").hide();

	// Display optional history
	$('#link_for_history_my_signups').click(function () {
		if ($("#link_for_history_my_signups").hasClass('wmsToggle')) {
			// hide history
			$("#link_for_history_my_signups").removeClass('wmsToggle').text('show history');
			$("#tabMySignups .toggle_opening_history").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
		else {
			// show history
			$("#link_for_history_my_signups").addClass('wmsToggle').text('hide history');
			$("#tabMySignups .toggle_opening_history").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
	});

	$('#link_for_history_others_signups').click(function () {
		if ($("#link_for_history_others_signups").hasClass('wmsToggle')) {
			// hide history
			$("#link_for_history_others_signups").removeClass('wmsToggle').text('show history');
			$("#tabOthersSignups .toggle_opening_history").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
		else {
			// show history
			$("#link_for_history_others_signups").addClass('wmsToggle').text('hide history');
			$("#tabOthersSignups .toggle_opening_history").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
		}
	});

	// ***************************
	// helper functions
	// ***************************

});