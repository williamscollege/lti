$(document).ready(function () {

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// ***************************
	// Listeners
	// ***************************

	// Delete signup
	$(document).on("click", ".sus-delete-signup", function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-signup-id');
		GLOBAL_confirmHandlerReference = $(this).attr('data-for-opening-id');
		var params = {
			title: "Delete Signup",
			message: "Really delete this signup for <strong>&quot;" + $(this).attr('data-for-signup-name') + "&quot;</strong>?",
			label: "Delete Signup",
			class: "btn btn-danger",
			url: "../ajax_actions/ajax_actions.php",
			ajax_action: "delete-signup",
			ajax_id: GLOBAL_confirmHandlerData
		};
		showConfirmBox(params);
	});


	$('#scroll-to-todayish-signups-01').click(function () {
		scrollListToTodayishSignups_01();
	});

	$('#scroll-to-todayish-signups-02').click(function () {
		scrollListToTodayishSignups_02();
	});


	// ***************************
	// helper functions
	// ***************************

	function scrollListToTodayishSignups_01() {
		var closestFutureOpeningsList = $('#signups-list-container-01 .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#signups-list-container-01 .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#signups-list-container-01 .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#signups-list-container-01').scrollTop($('#signups-list-container-01').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
	}

	function scrollListToTodayishSignups_02() {
		var closestFutureOpeningsList = $('#signups-list-container-02 .in-the-present');
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present - looking to the past');
			closestFutureOpeningsList = $('#signups-list-container-02 .in-the-past').prev();
		}
		//console.log(closestFutureOpeningsList);

		if (!closestFutureOpeningsList.length) {
			//console.log('no present nor past - looking to the future');
			closestFutureOpeningsList = $('#signups-list-container-02 .in-the-future').last();
		}
		//console.log(closestFutureOpeningsList);

		if (closestFutureOpeningsList.length) {
			$('#signups-list-container-02').scrollTop($('#signups-list-container-02').scrollTop() + $(closestFutureOpeningsList).position().top);
		}
	}

});