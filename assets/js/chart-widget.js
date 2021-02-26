
/*Line chart*/
    var optionslinechart = {
        chart: {
            toolbar: {
                show: false
            },
            height: 170,
            type: 'line'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: [ 4 ]
        },
        xaxis: {
            show: false,
            type: 'datetime',
            categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00"],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
        },
        grid: {
            show: false,
            padding: {
                left: 15,
                right: 15,
                bottom: 20
            }
        },
        colors:['#ffffff'],
        series: [
            {
                name: 'series1',
                data: [1.2, 2.3, 1.7, 3.2, 1.8, 3.2, 1]
            }
        ],
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            }
        }
    };

    var chartlinechart = new ApexCharts(
        document.querySelector("#chart-widget1"),
        optionslinechart
    );
    chartlinechart.render();

/*Line chart2*/
    var optionslinechart = {
        chart: {
            toolbar: {
                show: false
            },
            height: 170,
            type: 'line'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: [ 4 ]
        },
        xaxis: {
            show: false,
            type: 'datetime',
            categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00"],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
        },
        grid: {
            show: false,
            padding: {
                left: 15,
                right: 15,
                bottom: 20
            }
        },
        colors:['#ffffff'],
        series: [
            {
                name: 'series1',
                data: [1.2, 2.3, 1.7, 3.2, 1.8, 3.2, 1]
            }
        ],
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            }
        }
    };

    var chartlinechart = new ApexCharts(
        document.querySelector("#chart-widget2"),
        optionslinechart
    );
    chartlinechart.render();


/*Line chart3*/
    var optionslinechart = {
        chart: {
            toolbar: {
                show: false
            },
            height: 170,
            type: 'line'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: [ 3 ]
        },
        xaxis: {
            show: false,
            type: 'datetime',
            categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00"],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
        },
        yaxis: {
          show: false,
          labels: {
              show: false,
          },
          axisBorder: {
              show: false,
          },
        },
        grid: {
            show: false,
            padding: {
                left: 15,
                right: 15,
                bottom: 20
            }
        },
        colors:['#ffffff'],
        series: [
            {
                name: 'series1',
                data: [1.2, 2.3, 1.7, 3.2, 1.8, 3.2, 1]
            }
        ],
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            }
        }
    };

    var chartlinechart = new ApexCharts(
        document.querySelector("#chart-widget3"),
        optionslinechart
    );
    chartlinechart.render();

// [ radial-bar chart ] start
var optionsLine = {
   yaxis: {
     show: false,
   },
  grid: {
    show: false,
  },

  xaxis: {
     axisBorder: {
          show: true,
          color: '#cccccc',
          height: 1,
          width: '100%',
          offsetX: 0,
          offsetY: 0
      },  
  },

  chart: {
    height: 360,
    type: 'line',
    zoom: {
      enabled: false
    },
    toolbar: {
        show: false
    }
  },
  stroke: {
    curve: 'smooth',
    width: 4
  },
  colors: [pocoAdminConfig.primary, '#fd517d', '#ffc717'],
  series: [{
      name: "Music",
      data: [1, 15, 26, 20, 33, 27]
    },
    {
      name: "Photos",
      data: [3, 33, 21, 42, 19, 32]
    },
    {
      name: "Files",
      data: [0, 39, 52, 11, 29, 43]
    }
  ],
  subtitle: {
    text: 'Statistics',
    offsetY: 55,
    offsetX: 20
  },
  markers: {
    size: 6,
    strokeWidth: 0,
    hover: {
      size: 9
    }
  },
  labels: ['01/15/2020', '01/16/2020', '01/17/2020', '01/18/2020', '01/19/2020', '01/20/2020'],
  legend: {
    position: 'top',
    horizontalAlign: 'right',
    offsetY: -20
  }
}
var chartLine = new ApexCharts(document.querySelector('#line-adwords'), optionsLine);
chartLine.render();

// ======

var optionsCircle4 = {
  
  chart: {
    type: 'radialBar',
    width: 490,
    height: 360,
  },
  plotOptions: {
    radialBar: {
      size: undefined,
      inverseOrder: true,
      hollow: {
        margin: 5,
        size: '48%',
        background: 'transparent',
      },
      track: {
        show: false,
      },
      startAngle: -180,
      endAngle: 180
    },
  },
  stroke: {
    lineCap: 'round'
  },
  colors: [pocoAdminConfig.primary, '#fd517d', '#ffc717'],
  series: [71, 63, 77],
  labels: ['June', 'May', 'April'],
  legend: {
    show: true,
    floating: true,
    position: 'right',
    offsetX: 70,
    offsetY: 240
  },
}

var chartCircle4 = new ApexCharts(document.querySelector('#radialBarBottom'), optionsCircle4);
chartCircle4.render();

// [ heat-map chart ] start
    function generateData(count, yrange) {
      var i = 0;
      var series = [];
      while (i < count) {
        var x = (i + 1).toString();
        var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

        series.push({
          x: x,
          y: y
        });
        i++;
      }
      return series;
    }
    var options = {
      chart: {
        toolbar: {
            show: false
        },
        height: 350,
        type: 'heatmap',
      },
      plotOptions: {
        heatmap: {
          shadeIntensity: 0.5,

          colorScale: {
            ranges: [{
                from: -30,
                to: 5,
                name: 'low',
                color: '#06b5dd'
              },
              {
                from: 6,
                to: 20,
                name: 'medium',
                color: '#fe80b2'
              },
              {
                from: 21,
                to: 45,
                name: 'high',
                color: '#7e37d8'
              },
              {
                from: 46,
                to: 55,
                name: 'extreme',
                color: '#ffc717'
              }
            ]
          }
        }
      },
      dataLabels: {
        enabled: false
      },
      xaxis: {
        tooltip: {
            enabled: false
        },
        axisBorder: {
            show: false
        },
        labels: {
            show: false
        }
      },
      series: [{
          name: 'Jan',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Feb',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Mar',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Apr',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'May',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Jun',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Jul',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Aug',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        },
        {
          name: 'Sep',
          data: generateData(20, {
            min: -30,
            max: 55
          })
        }
      ],
      title: {
        text: 'HeatMap Chart with Color Range',
        offsetX: 20 ,
        offsetY: 15
      },

    }

    var chart = new ApexCharts(
      document.querySelector("#chart"),
      options
    );

    chart.render();


// [ dount chart ] start
var options = {
    chart: {
        width: 350,
        type: 'donut',
    },
    dataLabels: {
        enabled: false
    },
    series: [44, 55, 13],
    responsive: [{
        breakpoint: 200,
        options: {
            chart: {
                width: 200
            },
            legend: {
                show: false
            }
        }
    }],
    legend: {
        position: 'bottom'
    },
    fill: {
        opacity: 1
    },
    colors:['#7e37d8', '#fe80b2', '#06b5dd'],

}

var chart = new ApexCharts(
    document.querySelector("#chart1"),
    options
);

chart.render()

// [ progress chart ] start
    colors = ['#7e37d8', '#fe80b2', '#80cf00', '#06b5dd', '#ffc717', '#fd517d', '#158df7'];

    function shuffleArray(array) {
      for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
      }
      return array;
    }

    var arrayData = [{
      y: 400,
      quarters: [{
        x: 'Q1',
        y: 120
      }, {
        x: 'Q2',
        y: 90
      }, {
        x: 'Q3',
        y: 100
      }, {
        x: 'Q4',
        y: 90
      }]
    }, {
      y: 430,
      quarters: [{
        x: 'Q1',
        y: 120
      }, {
        x: 'Q2',
        y: 110
      }, {
        x: 'Q3',
        y: 90
      }, {
        x: 'Q4',
        y: 110
      }]
    }, {
      y: 448,
      quarters: [{
        x: 'Q1',
        y: 70
      }, {
        x: 'Q2',
        y: 100
      }, {
        x: 'Q3',
        y: 140
      }, {
        x: 'Q4',
        y: 138
      }]
    }, {
      y: 470,
      quarters: [{
        x: 'Q1',
        y: 150
      }, {
        x: 'Q2',
        y: 60
      }, {
        x: 'Q3',
        y: 190
      }, {
        x: 'Q4',
        y: 70
      }]
    }, {
      y: 540,
      quarters: [{
        x: 'Q1',
        y: 120
      }, {
        x: 'Q2',
        y: 120
      }, {
        x: 'Q3',
        y: 130
      }, {
        x: 'Q4',
        y: 170
      }]
    }, {
      y: 580,
      quarters: [{
        x: 'Q1',
        y: 170
      }, {
        x: 'Q2',
        y: 130
      }, {
        x: 'Q3',
        y: 120
      }, {
        x: 'Q4',
        y: 160
      }]
    }];

    function makeData() {
      var dataSet = shuffleArray(arrayData)

      var dataYearSeries = [{
        x: "2014",
        y: dataSet[0].y,
        color: colors[0],
        quarters: dataSet[0].quarters
      }, {
        x: "2015",
        y: dataSet[1].y,
        color: colors[1],
        quarters: dataSet[1].quarters
      }, {
        x: "2016",
        y: dataSet[2].y,
        color: colors[2],
        quarters: dataSet[2].quarters
      }, {
        x: "2017",
        y: dataSet[3].y,
        color: colors[3],
        quarters: dataSet[3].quarters
      }, {
        x: "2018",
        y: dataSet[4].y,
        color: colors[4],
        quarters: dataSet[4].quarters
      }, {
        x: "2019",
        y: dataSet[5].y,
        color: colors[5],
        quarters: dataSet[5].quarters
      }];

      return dataYearSeries
    }


    var optionsYear = {
      chart: {
        id: 'barYear',
        height: 450,
        width: '100%',
        type: 'bar',
        toolbar: {
            show: false
        },
      },
      plotOptions: {
        bar: {
          distributed: true,
          horizontal: true,
          barHeight: '20%',
          dataLabels: {
            show: false,
          }
        }
      },
      dataLabels: {
          enabled: false,
      },
      series: [{
        data: makeData()
      }],
      tooltip: {
        x: {
          show: false
        },
      },
      title: {
        text: 'Yearly Results',
        offsetX: 15,
        offsetY: 15
      },
      grid: {
        yaxis: {
          lines: {
            show: false,
          },
        },
        xaxis: {
          lines: {
            show: false,
          },
        }
      },
      yaxis: {
        labels: {
          show: true
        },
        tooltip: {
            enabled: true
        },
      },
      xaxis: {
        labels: {
          show: true
        },
        axisBorder: {
            show: false
        },
      }
    }

    var yearChart = new ApexCharts(
      document.querySelector("#chart-year"),
      optionsYear
    );
    yearChart.render();



// [ small-line chart1 ] start
    var options = {
        chart: {
            height: 180,
            type: 'line',
            shadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 1
            },
            toolbar: {
                show: false
            }
        },
        colors: [pocoAdminConfig.primary],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
            width: 4
        },
        series: [{
                name: "High - 2013",
                data: [20, 25, 20, 36, 32]
            },
        ],
        grid: {
            show: false
        },
        markers: {
            size: 5
        },
        xaxis: {
            tooltip: {
                enabled: false
            },
            axisBorder: {
                show: false
            },
            labels: {
                show: false
            }
        },

        yaxis: {
            title: {
                text: 'Temperature'
            },
            min: 5,
            max: 40
        },

    }

    var chart = new ApexCharts(
        document.querySelector("#small-chart1"),
        options
    );

    chart.render();

// [ small-line chart2 ] start
    var options = {
        chart: {
            height: 180,
            type: 'line',
            shadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 1
            },
            toolbar: {
                show: false
            }
        },
        colors: ['#fd517d'],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
            width: 4
        },
        series: [{
                name: "High - 2013",
                data: [30, 20, 40, 28, 36]
            },
        ],
        grid: {
            show: false
        },
        markers: {
            size: 5
        },
        xaxis: {
            tooltip: {
                enabled: false
            },
            axisBorder: {
                show: false
            },
            labels: {
                show: false
            }
        },

        yaxis: {
            title: {
                text: 'Temperature'
            },
            min: 5,
            max: 40
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#small-chart2"),
        options
    );

    chart.render();

