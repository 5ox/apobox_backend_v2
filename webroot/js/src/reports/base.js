import $ from 'jquery';
import Highcharts from 'highcharts';

Highcharts.setOptions({
    lang: {
        thousandsSep: ','
    }
});
$(document).ready(function() {
	var options = {
		chart: {
			renderTo: 'sales-volume',
			type: 'area'
		},
		title: {
			text: 'Order Volume'
		},
		legend: {
			align: 'right',
			x: -60,
			verticalAlign: 'top',
			y: 25,
			floating: true,
			backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
			borderColor: '#CCC',
			borderWidth: 1,
			shadow: false
		},
		exporting: {
			chartOptions: { // specific options for the exported image
				plotOptions: {
					series: {
						dataLabels: {
							enabled: true
						}
					}
				}
			},
			scale: 3,
			fallbackToExportServer: false
		},
		plotOptions: {
			column: {
				stacking: 'normal',
			},
			area: {
				// pointStart: 1940,
				marker: {
					enabled: false,
					symbol: 'circle',
					radius: 2,
					states: {
						hover: {
							enabled: true
						}
					}
				}
			}
		},
		xAxis: {
			categories: []
		},
		yAxis: [{
			title: {
				text: 'Dollars ($)'
			},
		}, {
			title: {
				text: 'Orders'
			},
			opposite: true,
		}],
		series: []
	};

	if ($('#sales-volume').length) {
		options.xAxis.categories = $('#sales-volume').data('dates');
		options.series = $('#sales-volume').data('volumes');
		var chart = new Highcharts.Chart(options);
	}

	var signupOptions = {
		chart: {
			renderTo: 'signups-volume',
			type: 'areaspline'
		},
		title: {
			text: 'Signups'
		},
		legend: {
			enabled: false,
		},
		plotOptions: {
			area: {
				marker: {
					enabled: false,
					symbol: 'circle',
					radius: 2,
					states: {
						hover: {
							enabled: true
						}
					}
				}
			}
		},
		xAxis: {
			categories: []
		},
		yAxis: [{
			title: {
				text: null
			},
		}],
		series: []
	};

	if ($('#signups-volume').length) {
		signupOptions.xAxis.categories = $('#signups-volume').data('dates');
		signupOptions.series = $('#signups-volume').data('volumes');
		var chart2 = new Highcharts.Chart(signupOptions);
	}

	var demoOptions = {
		chart: {
			renderTo: 'demo-volume',
			type: 'pie'
		},
		title: {
			text: 'Customers'
		},
		series: [
			{
				name: 'Customers',
				colorByPoint: true,
				data: []
			}
		]
	};

	if ($('#demo-volume').length) {
		demoOptions.title.text = $('#demo-volume').data('title');
		demoOptions.series[0].data = $('#demo-volume').data('values');
		var chart3 = new Highcharts.Chart(demoOptions);
	}
});
