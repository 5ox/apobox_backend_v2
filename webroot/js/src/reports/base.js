import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

document.addEventListener('DOMContentLoaded', function() {
	// Sales Volume chart (area → bar + line with dual y-axis)
	var salesEl = document.querySelector('#sales-volume');
	if (salesEl) {
		var dates = JSON.parse(salesEl.dataset.dates || '[]');
		var volumes = JSON.parse(salesEl.dataset.volumes || '[]');

		var canvas = document.createElement('canvas');
		salesEl.appendChild(canvas);

		var datasets = volumes.map(function(series) {
			return {
				label: series.name,
				data: series.data,
				type: series.type === 'column' ? 'bar' : 'line',
				yAxisID: series.yAxis === 1 ? 'y1' : 'y',
				fill: series.type !== 'column',
				tension: 0.3,
				pointRadius: 0,
				pointHoverRadius: 4,
			};
		});

		new Chart(canvas, {
			type: 'bar',
			data: { labels: dates, datasets: datasets },
			options: {
				responsive: true,
				plugins: {
					legend: { position: 'top' },
					title: { display: true, text: 'Order Volume' },
				},
				scales: {
					y: { title: { display: true, text: 'Dollars ($)' }, position: 'left' },
					y1: { title: { display: true, text: 'Orders' }, position: 'right', grid: { drawOnChartArea: false } },
				},
			},
		});
	}

	// Signups chart (areaspline → line with fill)
	var signupsEl = document.querySelector('#signups-volume');
	if (signupsEl) {
		var dates = JSON.parse(signupsEl.dataset.dates || '[]');
		var volumes = JSON.parse(signupsEl.dataset.volumes || '[]');

		var canvas = document.createElement('canvas');
		signupsEl.appendChild(canvas);

		var datasets = volumes.map(function(series) {
			return {
				label: series.name,
				data: series.data,
				fill: true,
				tension: 0.4,
				pointRadius: 0,
				pointHoverRadius: 4,
			};
		});

		new Chart(canvas, {
			type: 'line',
			data: { labels: dates, datasets: datasets },
			options: {
				responsive: true,
				plugins: {
					legend: { display: false },
					title: { display: true, text: 'Signups' },
				},
			},
		});
	}

	// Demographics chart (pie)
	var demoEl = document.querySelector('#demo-volume');
	if (demoEl) {
		var title = demoEl.dataset.title || 'Customers';
		var values = JSON.parse(demoEl.dataset.values || '[]');

		var canvas = document.createElement('canvas');
		demoEl.appendChild(canvas);

		new Chart(canvas, {
			type: 'pie',
			data: {
				labels: values.map(function(v) { return v.name; }),
				datasets: [{
					data: values.map(function(v) { return v.y; }),
				}],
			},
			options: {
				responsive: true,
				plugins: {
					title: { display: true, text: title },
				},
			},
		});
	}
});
