$(document).ready(function () {

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// ***************************
	// Listeners
	// ***************************

	// Delete signup
	$(document).on("click", ".sus-delete-signup-from-mine", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		//GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "Really delete <strong>your</strong> signup for <strong>&quot;" + $(this).attr('data-for-sheet-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup-from-mine",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	$(document).on("click", ".sus-delete-signup-from-others", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		//GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "Really delete <strong>" + $(this).attr('data-for-signup-name') + "'s</strong> signup for <strong>&quot;" + $(this).attr('data-for-sheet-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup-from-others",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	$('#scroll-to-todayish-signups-mine').click(function () {
		scrollListToTodayishSignups_01();
	});

	$('#scroll-to-todayish-signups-others').click(function () {
		scrollListToTodayishSignups_02();
	});


	// ***************************
	// helper functions
	// ***************************

	function scrollListToTodayishSignups_01() {
		var closestFutureOpeningsList = $('#signups-list-container-mine .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#signups-list-container-mine .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#signups-list-container-mine .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#signups-list-container-mine').scrollTop($('#signups-list-container-mine').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
	}

	function scrollListToTodayishSignups_02() {
		var closestFutureOpeningsList = $('#signups-list-container-others .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#signups-list-container-others .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#signups-list-container-others .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#signups-list-container-others').scrollTop($('#signups-list-container-others').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
	}

});