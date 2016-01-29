(function($){
	$.fn.circleGraphic=function(options){
		$.fn.circleGraphic.defaults={
			color:'#F90',
			startAngle: 0,
			progressvalue : 0
		};

		var opts = $.extend({},$.fn.circleGraphic.defaults,options);

		var percentage;
		if(opts.progressvalue==0){
			var temp = 1;//this.html();				// DKC modification, original: var temp = this.html(); // resolves div by zero error
			if (temp === parseInt(temp, 10))
				percentage = temp;
			else{
				throw new Error("Exception : NumberFormatException check your value at DIV");
			}
		}
		else
			percentage = opts.progressvalue;

		this.empty();

		var ID="c"+percentage+Math.random();
		//alert(ID);

		this.append("<canvas id='"+ID+"'></canvas>");

		var canvas=document.getElementById(ID),
			context=canvas.getContext('2d');

		var Width = this.width();
		this.height(Width);
		var Height = this.height();

		canvas.width = Width;
		canvas.height = Height;

		var startAngle = opts.startAngle,
			endAngle = percentage/100,
			angle = startAngle,
			radius = Width*0.4;						// DKC modification, original: radius = Width*0.4;

		function drawTrackArc(){
			context.beginPath();
			context.strokeStyle = '#ECECEC';
			context.lineWidth = 5;					// DKC modification, original: context.lineWidth = 5;
			context.arc(Width/2,Height/2,radius,(Math.PI/180)*(startAngle*360-90),(Math.PI/180)*(endAngle*360+270),false);
			context.stroke();
			context.closePath();
		}

		function drawOuterArc(_angle,_color){
			var angle = _angle;
			var color = _color;
			context.beginPath();
			context.strokeStyle = color;
			context.lineWidth = 7;					// DKC modification, original: context.lineWidth = 10;
			context.arc(Width/2,Height/2,radius,(Math.PI / 180) * (startAngle * 360 - 90), (Math.PI / 180) * (angle * 360 - 90), false);
			context.stroke();
			context.closePath();
		}

		function numOfPercentage(_angle,_color){
			//var angle = Math.floor(_angle*100);
			// if percentage is zero, do not add 1
			if (_angle == 0) {						// DKC modification, original: var temp = this.html(); // resolves progressvalue:100 showing as 99%
				var angle = Math.floor(_angle * 100) + 0;
			}
			else {
				var angle = Math.floor(_angle * 100) + 1;
			}
			var color=_color;
			context.font = "24px fantasy";			// DKC modification, original: context.font = "50px fantasy";
			context.fillStyle = color;
			var metrics = context.measureText(angle);
			var textWidth = metrics.width;
			var xPos = Width/2-textWidth/1.4,		// DKC modification, original: var xPos = Width/2-textWidth/2
				yPos = Height/2+textWidth/3;		// DKC modification, original: yPos = Height/2+textWidth/2;
			context.fillText(angle+"%",xPos,yPos);
		}

		function draw(){
			var loop = setInterval(function(){
				context.clearRect(0,0,Width,Height);
				drawTrackArc();
				drawOuterArc(angle,opts.color);
				numOfPercentage(angle,opts.color);
				angle+=0.01;
				if(angle>endAngle){
					clearInterval(loop);
				}

			},1000/120);								// draw speed (DKC modification, original: },1000/60);
		}
		draw();
		return this;
	};
})(jQuery);