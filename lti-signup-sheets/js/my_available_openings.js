$(document).ready(function () {

	// ***************************
	// For performance reasons, the Tooltip and Popover data-apis are opt-in, meaning you must initialize them yourself.
	// ***************************
	//TODO - minor efficiency improvement: remove popover initialization from any JS files that do not use it
	$('[data-toggle="popover"]').popover();


});