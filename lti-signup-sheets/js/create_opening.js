$(document).ready(function () {

	// onload actions
	$(".optional_opening_fields").hide();


	// listeners
	$(".link_show_optional_opening_fields").click(function()
	{
		//window.resizeBy(0, $(".optional_opening_fields").height())
		$(".optional_opening_fields").show();
		$(".link_show_optional_opening_fields").hide();
	});
	$(".link_hide_optional_opening_fields").click(function()
	{
		$(".optional_opening_fields").hide();
		//window.resizeBy(0, -1 * $(".optional_opening_fields").height())
		$(".link_show_optional_opening_fields").show();
	});

	$("#opening_spec_toggler").click(function()
	{
		$(".openings_by_time_range").toggle();
		$(".openings_by_duration").toggle();
		if ($("#opening_spec_type").attr("value") == "by_time_range")
		{
			$("#opening_spec_type").attr("value","by_duration")
		} else
		{
			$("#opening_spec_type").attr("value","by_time_range")
		}
	});

	$(".sus_choose_date").datepicker({ dateFormat: 'yy-m-d'
		, changeMonth: true
		, changeYear: true
		, closeText: 'X'
		, hideIfNoPrevNext: true
		, nextText: '&gt;'
		, prevText: '&lt;'
		, showButtonPanel: true
		, showOtherMonths: true
		, yearRange: '-1:+1'});
	$("#ui-datepicker-div").hide(); // UGLY HACK!!!!

	$(".toggler_dow").click(function(event){
		var which = event.target.id.substr(4,3);
		//alert("which is "+which);
		if (event.target.style.background == '')
		{
			//alert("turning on #repeat_dow_"+which);
			event.target.style.background = '#aaa';
			$("#repeat_dow_"+which).attr("value",1);
		} else
		{
			//alert("turning off #repeat_dow_"+which);
			event.target.style.background = '';
			$("#repeat_dow_"+which).attr("value",0);
		}
	});

	$(".toggler_dom").click(function(event){
		var which = event.target.id.substr(8,3);
		//alert("which is "+which);
		if (event.target.style.background == '')
		{
			event.target.style.background = '#aaa';
			$("#repeat_dom_"+which).attr("value",1);
		} else
		{
			event.target.style.background = '';
			$("#repeat_dom_"+which).attr("value",0);
		}
	});

	var lastRepRate = 1;
	var wdayHeight =  $("#repeatWeekdayChooser").height() + $("#repeatUntilDate").height() + 30;
	var mdayHeight =  $("#repeatMonthdayChooser").height() + $("#repeatUntilDate").height() + 30;
	function resizeWindowForRepRate(newRepRate)
	{
		if (lastRepRate == 2)
		{
			window.resizeBy(0, -1 * wdayHeight);
		} else if (lastRepRate == 3)
		{
			window.resizeBy(0, -1 * mdayHeight);
		}

		if (newRepRate == 2)
		{
			window.resizeBy(0, wdayHeight);
		} else if (newRepRate == 3)
		{
			window.resizeBy(0, mdayHeight);
		}

		lastRepRate = newRepRate;
	}

	$("#radioOpeningRepeatRate1").click(function(event){
		//alert("on 1");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").hide();
		resizeWindowForRepRate(1);
	});

	$("#radioOpeningRepeatRate2").click(function(event){
		//alert("on 2");
		$("#repeatWeekdayChooser").show();
		$("#repeatMonthdayChooser").hide();
		$("#repeatUntilDate").show();
		resizeWindowForRepRate(2);
	});

	$("#radioOpeningRepeatRate3").click(function(event){
		//alert("on 3");
		$("#repeatWeekdayChooser").hide();
		$("#repeatMonthdayChooser").show();
		$("#repeatUntilDate").show();
		resizeWindowForRepRate(3);
	});


	$("#btn_save_openings").click(function(event) {
		if (  ($("#endtime_hour").val() == '12')
			&& ($("#endtime_minute").val() == '0')
			&& ($("#endtime_ampm").val() == 'am'))
		{
			customAlert("","cannot end an opening at 12:00 AM");
			return false;
		}

		// create start time string
		// create end time string
		var btime = valsToTimeString($("#begintime_hour").val(),$("#begintime_minute").val(),$("#begintime_ampm").val());
		var etime = valsToTimeString($("#endtime_hour").val(),$("#endtime_minute").val(),$("#endtime_ampm").val());
		//alert("time strings are "+btime+" and "+etime);

		// if end <= start, that's a problem
		if (etime <= btime)
		{
			customAlert("","end time must be later than start time");
			return false;
		}
		return true;
	});

});