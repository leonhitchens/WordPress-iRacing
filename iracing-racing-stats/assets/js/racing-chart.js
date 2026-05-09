( function () {
	'use strict';

	function initCharts() {
		var charts = window.iRacingCharts;
		if ( ! charts || ! charts.length ) {
			return;
		}

		charts.forEach( function ( chart ) {
			var canvas = document.getElementById( chart.id );
			if ( ! canvas || ! chart.data || ! chart.data.length ) {
				return;
			}

			new Chart( canvas, {
				type: 'line',
				data: {
					datasets: [
						{
							label: 'iRating',
							data: chart.data,
							borderColor: '#4a9eff',
							backgroundColor: 'rgba(74, 158, 255, 0.08)',
							borderWidth: 2,
							pointRadius: 3,
							pointHoverRadius: 5,
							pointBackgroundColor: '#4a9eff',
							tension: 0.3,
							fill: true,
						},
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					parsing: {
						xAxisKey: 'x',
						yAxisKey: 'y',
					},
					interaction: {
						mode: 'index',
						intersect: false,
					},
					plugins: {
						legend: {
							display: false,
						},
						tooltip: {
							backgroundColor: '#1a1e27',
							borderColor: '#2a2f3b',
							borderWidth: 1,
							titleColor: '#8b95a5',
							bodyColor: '#e8eaed',
							callbacks: {
								label: function ( ctx ) {
									return ' ' + ctx.parsed.y.toLocaleString() + ' iR';
								},
							},
						},
					},
					scales: {
						x: {
							type: 'category',
							grid: {
								color: '#1e2330',
							},
							ticks: {
								color: '#8b95a5',
								font: { size: 11 },
								maxTicksLimit: 8,
							},
						},
						y: {
							grid: {
								color: '#1e2330',
							},
							ticks: {
								color: '#8b95a5',
								font: { size: 11 },
								callback: function ( value ) {
									return value.toLocaleString();
								},
							},
						},
					},
				},
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initCharts );
	} else {
		initCharts();
	}
} )();
