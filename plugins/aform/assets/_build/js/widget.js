(function($) {
	
	var $seaformChart = $("#seaformsWidgetChart");	
	var seaformChart = $seaformChart[0].getContext("2d");
	var chartParent = $seaformChart.parent('div');
	
	var curLabels = Object.keys(currentForms);
	console.log( curLabels );
	
	var curData = [];
	
	curLabels.forEach( function(key) {
		curData.push( currentForms[key] );
	});
	
	console.log( curData );
	
	var data = {
		labels: curLabels,
		datasets: [
			{
				data: curData,
				fillColor: "#f2ad60",
				strokeColor:  "#e28925"
			}
		]
	};
	
	Chart.types.Line.extend({
		name : 'SeaformsWidget',
		initialize: function (data) {
	        Chart.types.Line.prototype.initialize.apply(this, arguments);
	        var xLabels = this.scale.xLabels
	        xLabels.forEach(function (label, i) {
            if (i % 5 != 1)
                xLabels[i] = '';
	        });
	    }
	});

	var seaformsLineChart = new Chart(seaformChart).SeaformsWidget(
		data,
		{
			bezierCurve: false,
			datasetStrokeWidth: 4,
			maintainAspectRatio: false,
			pointHitDetectionRadius: 4,
			responsive: true
		}
	);
		
})(jQuery);