$(document).ready(function () {

	// ***************************
	// onload actions
	// ***************************
	$(".optional_opening_fields").hide();
	$("#link_show_by_duration").hide();
	$(".openings_by_duration").hide();
	$("#repeatWeekdayChooser").hide();
	$("#repeatMonthdayChooser").hide();
	$("#repeatUntilDate").hide();


	// TODO - Modal needs form reset and optional links reset (for Create mode) wired up to Cancel button (works with Escape key too?)
	//$("#link_hide_optional_opening_fields").click();
	//$("#link_show_by_duration").click();

	// ***************************
	// Calendar datepicker
	// ***************************
	$("#ajaxOpeningUntilDate").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'mm/dd/yy',
		yearRange: '-4:+4'
	});


	// ***************************
	// listeners
	// ***************************
	$("#link_show_optional_opening_fields").click(function () {
		//window.resizeBy(0, $(".optional_opening_fields").height())
		$(".optional_opening_fields").show();
		$("#link_show_optional_opening_fields").hide();
	});
	$("#link_hide_optional_opening_fields").click(function () {
		$(".optional_opening_fields").hide();
		//window.resizeBy(0, -1 * $(".optional_opening_fields").height())
		$("#link_show_optional_opening_fields").show();
	});


	// ***************************
	// default condition
	// ***************************
	$("#link_show_by_time_range").click(function () {
		$(this).hide();
		$(".openings_by_time_range").hide();
		$("label[for='ajaxOpeningBeginTimeHour']").html("Starting&nbsp;at");
		$("label[for='ajaxOpeningEndTimeHour']").html("Make&nbsp;each&nbsp;opening");
		$("#link_show_by_duration").show();
		$(".openings_by_duration").show();
	});

	$("#link_show_by_duration").click(function () {
		$(this).hide();
		$(".openings_by_duration").hide();
		$("label[for='ajaxOpeningBeginTimeHour']").html("From");
		$("label[for='ajaxOpeningEndTimeHour']").html("To");
		$("#link_show_by_time_range").show();
		$(".openings_by_time_range").show();
	});

	$(".toggler_dow").click(function (event) {
		var which = event.target.id.substr(4, 3);
		//alert("which is "+which);
		if( $(this).hasClass("btn-success") ){
			//alert("turning off #repeat_dow_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dow_" + which).prop("value", 1);
		} else {
			//alert("turning on #repeat_dow_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dow_" + which).prop("value", 0);
		}
	});

	$(".toggler_dom").click(function (event) {
		var which = event.target.id.substr(8, 3);
		//alert("which is "+which);
		if( $(this).hasClass("btn-success") ){
			//alert("turning off #repeat_dom_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dom_" + which).prop("value", 1);
		} else {
			//alert("turning on #repeat_dom_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dom_" + which).prop("value", 0);
		}
	});

	$("#radioOpeningRepeatRate1").click(function (event) {
		//alert("on 1");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").hide();
	});

	$("#radioOpeningRepeatRate2").click(function (event) {
		//alert("on 2");
		$("#repeatWeekdayChooser").show();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").show();
	});

	$("#radioOpeningRepeatRate3").click(function (event) {
		//alert("on 3");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").show();
		$("#repeatUntilDate").show();
	});


	$("#btn_save_openings").click(function (event) {
		if (($("#ajaxOpeningEndTimeHour").val() == '12')
			&& ($("#ajaxOpeningEndTimeMinute").val() == '0')
			&& ($("#ajaxOpeningEndTimeMinute_AMPM").val() == 'am')) {
			customAlert("", "cannot end an opening at 12:00 AM");
			return false;
		}

		// create start time string
		// create end time string
		var btime = valsToTimeString($("#ajaxOpeningBeginTimeHour").val(), $("#ajaxOpeningBeginTimeMinute").val(), $("#ajaxOpeningBeginTime_AMPM").val());
		var etime = valsToTimeString($("#ajaxOpeningEndTimeHour").val(), $("#ajaxOpeningEndTimeMinute").val(), $("#ajaxOpeningEndTimeMinute_AMPM").val());
		//alert("time strings are "+btime+" and "+etime);

		// if end <= start, that's a problem
		if (etime <= btime) {
			customAlert("", "end time must be later than start time");
			return false;
		}
		return true;
	});

})
;