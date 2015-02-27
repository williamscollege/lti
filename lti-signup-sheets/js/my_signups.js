$(document).ready(function () {

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	$('[data-toggle="popover"]').popover();


	// ***************************
	// helper functions
	// ***************************


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

});