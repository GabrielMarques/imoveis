$(document).ready(function() {
	// Highcharts theme

	var title_color = '#404040';
	var title_size = '12px';
	var label_color = '#808080';
	var label_size = '11px';
	var grid_line_color = '#ddd';
	var alternate_grid_bg = '#FFF';
	var link_color = '#0069d6';
	var link_hover_color = '#00438A';
	var base_line_height = 20;

	Highcharts.theme = {
		colors: ['#049cdb', '#46a546', '#9d261d', '#ffc40d', '#f89406', '#c3325f', '#7a43b6'],
		symbols: ['circle', 'circle', 'circle', 'circle', 'circle', 'circle', 'circle'],
		chart: {
			style: {
				//fontFamily: '"Helvetica Neue", Helvetica, Arial, sans-serif',
			},
			spacingTop: base_line_height,
			spacingBottom: base_line_height,
			spacingLeft: base_line_height,
			spacingRight: base_line_height,
			className: 'chart',
      //plotBorderWidth: 1,
      plotBorderColor: grid_line_color,
			shadow: false,       
		},
		title: {
			align: 'center', 			
			style: {
				color: title_color,
				fontWeight: 'bold',
				fontSize: title_size,
			}		
		},
		subtitle: {
			style: {
				color: label_color,
				fontSize: title_size,
			}
		},
		xAxis: {
			gridLineWidth: 0,
			lineWidth: 0,
			tickWidth: 0,
			gridLineColor: grid_line_color,
			title: {
				style: {
					color: title_color,
					fontWeight: 'bold',
					fontSize: title_size,
				}
			},
			labels: {
				style: {
					color: label_color,
					fontSize: label_size,
				}
			},
		},
		yAxis: {
			alternateGridColor: alternate_grid_bg,
			gridLineWidth: 1,
			gridLineColor: grid_line_color,
			lineWidth: 0,
			tickWidth: 0,
			title: {
				style: {
					color: title_color,
					fontWeight: 'bold',
					fontSize: title_size,
				}
			},
			labels: {
				style: {
					color: label_color,
					fontSize: label_size,
				}
			}
		},
		legend: {
			borderWidth: 0,
			margin: base_line_height,
			verticalAlign: 'top',
			itemStyle: {
				color: link_color,
				//fontWeight: 'bold',
				fontSize: title_size,
			},
			itemHoverStyle: {
				color: link_hover_color,
			},
			itemHiddenStyle: {
				color: 'gray',
			}
		},
	  plotOptions: {
	    spline: {
				shadow: false,
				lineWidth: 1,
	      marker: {
	      	radius: 4,
	      }
	    },
	    line: {
				shadow: false,
				lineWidth: 1,
	      marker: {
	      	radius: 4,
	      }
	    },
			column: {
				dataLabels: {
					enabled: true,
					color: label_color,
				},
				shadow: false,
	    	borderWidth: 1,
	    	borderColor: '#ddd',
			},
      series: {
				marker: {
        	lineWidth: 1,
      	}
      }	    
		},
	  exporting: {
			enabled: false,
			/*
	    buttons: {
				printButton: {
					enabled: false,
				},
				exportButton: {
					x: -base_line_height,
					y: base_line_height,
				}
	    },
	    */
		},
		tooltip: {    
			//animation: false,
	    useHTML: true,
	    shared: false,
	    borderRadius: 0,
	    borderWidth: 0,
	    shadow: false,
	    enabled: true,
	    backgroundColor: 'none',
	    formatter: function() {return '<div class="chart-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner">' + this.series.name + ': ' + this.y + '</div></div>';},   
		}
	};

	// Apply the theme
	var highchartsOptions = Highcharts.setOptions(Highcharts.theme);
});