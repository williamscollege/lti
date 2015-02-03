$(document).ready(function () {

	// ***************************
	// onload actions
	// ***************************



	// ***************************
	// Calendar datepicker
	// ***************************
	$("#ajaxOpeningUntilDate").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'mm/dd/yy',
		yearRange: '-4:+4'
	});


	// populate modal form with calendar date of day clicked
	$(".addOpeningLink").click(function(){
		var dateClicked = $(this).attr('data-cal-date');
		console.log('dateClicked = ' + dateClicked );
		setupModalForm(dateClicked);
	});

	function setupModalForm(forDateYYYYMMDD){

		var forDateAry = forDateYYYYMMDD.split('-');
		var forDateClean = forDateAry[1]+'/'+forDateAry[2]+'/'+forDateAry[0];

		var d = new Date(forDateYYYYMMDD);
		var dow = (['mon','tue','wed','thu','fri','sat','sun'])[d.getDay()];
		var dom = forDateAry[2] * 1;


		// set up the date
		$("#ajaxOpeningUntilDate").attr('value',forDateClean);

		$(".ajaxOpeningCalDate").html(forDateClean);

		// clear out & reset the day-of-week repeats
		$('.repeat_dow_val').val(0);
		$('.toggler_dow').removeClass('btn-success');
		$('.toggler_dow').removeClass('btn-default');
		$('.toggler_dow').addClass('btn-default');
		$('#btn_'+dow).click();

		// clear out & reset the day-of-month repeats
		$('.repeat_dom_val').val(0);
		$('.toggler_dom').removeClass('btn-success');
		$('.toggler_dom').removeClass('btn-default');
		$('.toggler_dom').addClass('btn-default');
		$('#btn_dom_'+dom).click();

		// set the repeat option to be the default (only on)
		$('#radioOpeningRepeatRate1').click();

		// hide the stuff that should be hidden
		$('#link_show_by_duration').click();
		$('#link_hide_optional_opening_fields').click();

		// reset non-dynamic form fields to defaults
		//$("#frmAjaxOpening select").val(0);
		$('#frmAjaxOpening').trigger("reset");
	}

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
		if ($(this).hasClass("btn-success")) {
			//alert("turning off #repeat_dow_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dow_" + which).prop("value", 1);
		}
		else {
			//alert("turning on #repeat_dow_"+which);
			$(this).addClass("btn-success").removeClass("btn-default");
			$("#repeat_dow_" + which).prop("value", 0);
		}
	});

	$(".toggler_dom").click(function (event) {
		var which = event.target.id.substr(8, 3);
		//alert("which is "+which);
		if ($(this).hasClass("btn-success")) {
			//alert("turning off #repeat_dom_"+which);
			$(this).removeClass("btn-success").addClass("btn-default");
			$("#repeat_dom_" + which).prop("value", 1);
		}
		else {
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


	// ***************************
	// Cancel and cleanup
	// ***************************
	// TODO - update other modal cleanUpForm fxns with solutions from this one

	function cleanUpForm(formName) {
		// reset form to initial values (does not effect hidden inputs)
		$('#' + formName).trigger("reset");

		// TODO - temporary... need to deal with hidden values, and button conditions after click, dismiss, and re-open of modal
		// TODO - also need to bind  button[data-dismiss="modal"]  action (AND ESCAPE KEY TOO) to the cleanUpForm()
		//$(":input", form).each(function () {
		//	var type = this.type;
		//	var tag = this.tagName.toLowerCase();
		//	if (type == 'text') {
		//		this.value = "";
		//	}
		//});
		//validateAjaxOpening.resetForm();
		// manually remove input highlights
		// $(".form-group").removeClass('success').removeClass('error');
	}

	$('#btnAjaxOpeningCancel').click(function () {
		cleanUpForm("frmAjaxOpening");

		// manually clear modal values
		//$("#ajaxOpeningID").val(0);
		//$("#ajaxOpeningLabel").text('');
		//$("#ajaxOpeningAction").val('');
		//$("#frmAjaxOpening textarea").val('');
		//$("#frmAjaxOpening input[type=text]").val('');
		//$("#frmAjaxOpening input[type=radio]").attr("checked", false);
		//$("#frmAjaxOpening select").val(0);

		// reset submit button (avoid disabled state)
		$("#btnAjaxOpeningSubmit").button('reset');
	});

	// TODO - implement in ajax success callback
	//success: function (data) {
	//	// hide and reset form
	//	$("#btnAjaxOpeningCancel").click();

	// END: Cancel and cleanup

});