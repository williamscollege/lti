$(document).ready(function () {

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// ***************************
	// Listeners
	// ***************************

	// Delete signup
	$(document).on("click", ".sus-delete-my-signup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "Really delete your signup for:<br /><strong>&quot;" + $(this).attr('data-for-sheet-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-my-signup",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});

	$(document).on("click", ".sus-delete-others-signup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "Really delete <strong>" + $(this).attr('data-for-signup-name') + "'s</strong> signup for:<br /><strong>&quot;" + $(this).attr('data-for-sheet-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-others-signup",
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


	// ***************************
	// helper functions
	// ***************************

	function scrollListToTodayishSignups_01() {
		var closestFutureOpeningsList = $('#my-signups-list-container .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#my-signups-list-container .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#my-signups-list-container .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#my-signups-list-container').scrollTop($('#my-signups-list-container').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
	}

	function scrollListToTodayishSignups_02() {
		var closestFutureOpeningsList = $('#others-signups-list-container .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#others-signups-list-container .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#others-signups-list-container .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#others-signups-list-container').scrollTop($('#others-signups-list-container').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
	}

});