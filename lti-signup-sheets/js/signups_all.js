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
			message: "Really delete <strong>" + $(this).attr('data-for-signup-name') + "'s</strong> signup for:<br /><strong>&quot;" + $(this).attr('data-for-sheet-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup",
			ajax_id: GLOBAL_confirmHandlerData,
			opening_id: GLOBAL_confirmHandlerReference
		};
		showConfirmBox(params);
	});

	$('#scroll-to-todayish-my-signups').click(function () {
		scrollListToTodayishSignups_01();
	});

	$('#scroll-to-todayish-others-signups').click(function () {
		scrollListToTodayishSignups_02();
	});

	// set initial condition: hide details
	$("#tabMySignups .toggle_opening_details").hide();
	$("#tabOthersSignups .toggle_opening_details").hide();

	// Display optional details for openings
	$("#link_for_opening_details_1").click(function () {
		if ($("#tabMySignups .toggle_opening_details").hasClass('wmsToggle')) {
			// hide details
			$("#tabMySignups .toggle_opening_details").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
			$("#link_for_opening_details_1").text('show details');
		}
		else {
			// show details
			$("#tabMySignups .toggle_opening_details").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
			$("#link_for_opening_details_1").text('hide details');
		}
	});

	$("#link_for_opening_details_2").click(function () {
		if ($("#tabOthersSignups .toggle_opening_details").hasClass('wmsToggle')) {
			// hide details
			$("#tabOthersSignups .toggle_opening_details").removeClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
			$("#link_for_opening_details_2").text('show details');
		}
		else {
			// show details
			$("#tabOthersSignups .toggle_opening_details").addClass('wmsToggle').toggle("highlight", {color: '#D7F3FB'}, 300);
			$("#link_for_opening_details_2").text('hide details');
		}
	});


	// ***************************
	// helper functions
	// ***************************

	function scrollListToTodayishSignups_01() {
		var closestFutureOpeningsList = $('#container-my-signups .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#container-my-signups .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#container-my-signups .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#container-my-signups').scrollTop($('#container-my-signups').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
		$(closestFutureOpeningsList).first().effect("highlight", {color: '#C9E5C9'}, 300);
	}

	function scrollListToTodayishSignups_02() {
		var closestFutureOpeningsList = $('#container-others-signups .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#container-others-signups .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#container-others-signups .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#container-others-signups').scrollTop($('#container-others-signups').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
		$(closestFutureOpeningsList).first().effect("highlight", {color: '#C9E5C9'}, 300);
	}

});