/*! Parser: weekday - updated 10/26/2014 (v2.18.0) */
/* Demo: http://jsfiddle.net/Mottie/abkNM/4169/ */
/*jshint jquery:true */
;
(function ($) {
	'use strict';

	var ts = $.tablesorter;
	ts.dates = $.extend({}, ts.dates, {
		// *** modify this array to change match the language ***
		weekdayCased: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
	});
	ts.dates.weekdayLower = ts.dates.weekdayCased.join(',').toLocaleLowerCase().split(',');

	ts.addParser({
		id: 'weekday',
		is: function () {
			return false;
		},
		format: function (s, table) {
			if (s) {
				var j = -1, c = table.config;
				s = c.ignoreCase ? s.toLocaleLowerCase() : s;
				$.each(ts.dates['weekday' + (c.ignoreCase ? 'Lower' : 'Cased')], function (i, v) {
					if (j < 0 && s.match(v)) {
						j = i;
						return false;
					}
				});
				// return s (original string) if there isn't a match
				// (non-weekdays will sort separately and empty cells will sort as expected)
				return j < 0 ? s : j;
			}
			return s;
		},
		type: 'numeric'
	});

})(jQuery);
