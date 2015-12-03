(function ($) {
	$.fn.circleGraphic = function (options) {
		$.fn.circleGraphic.defaults = {
			color: '#F90',
			startAngle: 0,
			//endAngle:50
		};

		var opts = $.extend({}, $.fn.circleGraphic.defaults, options);

		//var percentage=this.html();
		var percentage = this.children("span.circleIntegerValue").html(); // DKC modification
		var ID = "c" + percentage + Math.random();
		// alert(percentage);

		this.append("<canvas id='" + ID + "'></canvas>");

		var canvas = document.getElementById(ID),
			context = canvas.getContext('2d');

		var Width = this.width();
		this.height(Width);
		var Height = this.height();

		canvas.width = Width;
		canvas.height = Height;

		var startAngle = opts.startAngle,
			endAngle = percentage / 100,
			angle = startAngle,
			radius = Width * 0.4;

		// background complete circle of shaded track
		function drawTrackArc() {
			context.beginPath();
			context.strokeStyle = '#ECECEC';
			context.lineWidth = 5;
			context.arc(Width / 2, Height / 2, radius, (Math.PI / 180) * (startAngle * 360 - 90), (Math.PI / 180) * (endAngle * 360 + 270), false);
			context.stroke();
			context.closePath();
		}

		// foreground percentage of completed circle
		function drawOuterArc(_angle, _color) {
			var angle = _angle;
			var color = _color;
			//console.log(angle);
			// dkc modification
			//if (angle < 0.95) {
			//	color = "#E53238";
			//} else{
			//	color = "#00B233";
			//}
			context.beginPath();
			context.strokeStyle = color;
			context.lineWidth = 10;
			context.arc(Width / 2, Height / 2, radius, (Math.PI / 180) * (startAngle * 360 - 90), (Math.PI / 180) * (angle * 360 - 90), false);
			context.stroke();
			context.closePath();
		}

		function numOfPercentage(_angle, _color) {
			// if percentage is zero, do not add 1
			// console.log(_angle);
			if (_angle == 0) {
				var angle = Math.floor(_angle * 100) + 0;
			}
			else {
				var angle = Math.floor(_angle * 100) + 1;
			}
			var color = _color;
			context.font = "50px fantasy";
			context.fillStyle = color;
			var metrics = context.measureText(angle);
			var textWidth = metrics.width;
			//console.log(textWidth);
			//console.log(Width);
			//console.log(Height);
			var xPos = Width / 2 - textWidth / 1.5,// DKC modification (original: var xPos = Width / 2 - textWidth / 2,)
				yPos = Height / 2 + textWidth / 3; // DKC modification (original: yPos = Height / 2 + textWidth / 2)
			context.fillText(angle + "%", xPos, yPos);
		}

		function draw() {
			var loop = setInterval(function () {
				context.clearRect(0, 0, Width, Height);
				drawTrackArc();
				drawOuterArc(angle, opts.color);
				//alert(angle,opts.color);
				numOfPercentage(angle, opts.color);
				angle += 0.01; // draw by increments of... (DKC modification, original: 0.01)
				if (angle > endAngle) {
					clearInterval(loop);
				}
			}, 1000 / 120); // draw speed (DKC modification,original: 1000 / 120)
		}

		draw();
		return this;
	};
})(jQuery);
