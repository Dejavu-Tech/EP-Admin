/*bitcoin chart1*/
var optionslinechart = {
    chart: {
        toolbar: {
            show: false
        },
        height: 180,
        type: 'area'
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: false
    },
    xaxis: {
        show: false,
        offset: 0,
        low: 0,
        type: 'datetime',
        categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00", "2018-09-19T07:30:00", "2018-09-19T08:30:00"],
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
            left: 0,
            right: 0,
            top: 0,
            bottom: -40,
        }
    },
    fill: {
        gradient: {
            shade: 'light',
            type: 'horizontal',
            shadeIntensity: 0,
            inverseColors: false,
            opacityTo: 1,
            opacityFrom: 0.8,
            stops: [0, 100]
        },

    },
    colors:['#fe68a3'],


    series: [
    {
        name: 'series1',
        data: [2.5, 2, 3.1, 3.8, 2.6, 3.1, 2.1, 2.4, 1.8, 0]
    }
    ],
    tooltip: {
        x: {
            format: 'dd/MM/yy HH:mm'
        }
    }
};

var chartlinechart = new ApexCharts(
    document.querySelector("#bitcoinchart-1"),
    optionslinechart
    );
chartlinechart.render();


/*bitcoin chart2*/
var optionslinechart = {
    chart: {
        toolbar: {
            show: false
        },
        height: 180,
        type: 'area'
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: false
    },
    xaxis: {
        show: false,
        type: 'datetime',
        categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00", "2018-09-19T07:30:00"],
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
            left: 0,
            right: 0,
            top: 0,
            bottom: -40,
        }
    },
    fill: {
        gradient: {
            shade: 'light',
            type: 'horizontal',
            shadeIntensity: 0,
            inverseColors: false,
            opacityTo: 1,
            opacityFrom: 0.8,
            stops: [0, 100]
        },

    },
    colors:[pocoAdminConfig.primary],


    series: [
    {
        name: 'series1',
        data: [2.2, 3.2, 2.6, 2, 2.3, 3.8, 3, 2.6, 0]
    }
    ],
    tooltip: {
        x: {
            format: 'dd/MM/yy HH:mm'
        }
    }
};

var chartlinechart = new ApexCharts(
    document.querySelector("#bitcoinchart-2"),
    optionslinechart
    );
chartlinechart.render();

/*bitcoin chart3*/
var optionslinechart = {
    chart: {
        toolbar: {
            show: false
        },
        height: 180 ,
        type: 'area'
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: false
    },
    xaxis: {
        show: false,
        type: 'datetime',
        categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00", "2018-09-19T07:30:00", "2018-09-19T08:30:00"],
        labels: {
            show: false,
        },
        tooltip: {
          enabled: false,
      },
      axisBorder: {
        show: false,
    }
},
grid: {
    show: false,
    padding: {
        left: 0,
        right: 0,
        top: 0,
        bottom: -40,
    }
},
fill: {
    gradient: {
        shade: 'light',
            type: 'horizontal',
            shadeIntensity: 0,
            inverseColors: false,
            opacityTo: 1,
            opacityFrom: 0.8,
            stops: [0, 100]
    },

},
colors:['#ffc200'],


series: [
{
    name: 'series1',
    data: [2.5, 2, 2.6, 3.2, 3.8, 3.1, 3.2, 2.4, 1.8, 0]
}
],
tooltip: {
    x: {
        format: 'dd/MM/yy HH:mm'
    }
}
};

var chartlinechart = new ApexCharts(
    document.querySelector("#bitcoinchart-3"),
    optionslinechart
    );
chartlinechart.render();

/*candlestick chart*/
var options4 = {
    labels: ['J', 'F', 'M', 'A', 'M', 'J'],
    chart: {
        height: 350,
        type: 'candlestick',
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            top: 10,
            left: 0,
            blur: 8,
            opacity: 0.08,
            color: '#168df7',
        },
    },
    plotOptions: {
        candlestick: {
            colors: {
                upward: pocoAdminConfig.primary,
                downward: '#fe80b2'
            }
        }
    },
    series: [{
        data: [{
                x: new Date(1538789400000),
                y: [6624.61, 6632.2, 6617, 6626.02]
            },
            {
                x: new Date(1538791200000),
                y: [6627, 6627.62, 6584.22, 6603.02]
            },
            {
                x: new Date(1538793000000),
                y: [6605, 6608.03, 6598.95, 6604.01]
            },
            {
                x: new Date(1538794800000),
                y: [6604.5, 6614.4, 6602.26, 6608.02]
            },
            {
                x: new Date(1538796600000),
                y: [6608.02, 6610.68, 6601.99, 6608.91]
            },
            {
                x: new Date(1538798400000),
                y: [6608.91, 6618.99, 6608.01, 6612]
            },
            {
                x: new Date(1538800200000),
                y: [6612, 6615.13, 6605.09, 6612]
            },
            {
                x: new Date(1538802000000),
                y: [6612, 6624.12, 6608.43, 6622.95]
            },
            {
                x: new Date(1538803800000),
                y: [6623.91, 6623.91, 6615, 6615.67]
            },
            {
                x: new Date(1538805600000),
                y: [6618.69, 6618.74, 6610, 6610.4]
            },
            {
                x: new Date(1538807400000),
                y: [6611, 6622.78, 6610.4, 6614.9]
            },
            {
                x: new Date(1538809200000),
                y: [6614.9, 6626.2, 6613.33, 6623.45]
            },
            {
                x: new Date(1538811000000),
                y: [6623.48, 6627, 6618.38, 6620.35]
            },
            {
                x: new Date(1538812800000),
                y: [6615, 6627.40, 6584.10, 6603.00]
            },
            {
                x: new Date(1538814600000),
                y: [6615.53, 6617.93, 6610, 6615.19]
            },
            {
                x: new Date(1538816400000),
                y: [6615.19, 6621.6, 6608.2, 6620]
            },
            {
                x: new Date(1538818200000),
                y: [6619.54, 6625.17, 6614.15, 6620]
            },
            {
                x: new Date(1538820000000),
                y: [6620.33, 6634.15, 6617.24, 6624.61]
            },
            {
                x: new Date(1538821800000),
                y: [6625.95, 6626, 6611.66, 6617.58]
            },
        ]
    }],
    xaxis: {
        type: 'datetime',
        show: false,
        labels: {
            low: 0,
            offsetX: 0,
            show: true,
        },
        axisBorder: {
            low: 0,
            offsetX: 0,
            show: false,
        },
        axisTicks: {
            show: false,
        },
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'light',
            type: 'horizontal',
            shadeIntensity: 0.2,
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
        }
    },
    grid: {
        show: false
    },
    yaxis: {
        type: 'datetime',
        show: false,
        labels: {
            low: 0,
            offsetX: 0,
            show: false,
        },
        tooltip: {
            enabled: true
        }
    }
}

var chart4 = new ApexCharts(
    document.querySelector("#candlestick-chart"),
    options4
    );

chart4.render();


/*btc-earning chart*/
new Chartist.Line('.btc-earning', {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    series: [
    [1.3, 2, 1, 2.5, 1.9, 3.0],
    [1, 0.7, 1.2, 2, 1.2, 2.3]
    ]
}, {
    fullWidth: true,
    lineSmooth: Chartist.Interpolation.simple({
        divisor: 2
    }),
    low: 0,
    offset: 0,
    showArea: false,
    showPoint: false,
    chartPadding: {
        left: -22,
        right: 0,
        bottom: 0,
        top: 30
    },
    axisY: {
        low: 0,
        showGrid: false,
        showLabel: false,
        offset: 0,
        scaleMinSpace: 40
    },
    axisX: {
        showGrid: false,
        showLabel: false,
        offset: 0
    }
});



var owl_carousel_custom = {
    init: function() {
        $('.crypto-slider').owlCarousel({
            loop:true,
            margin:30,
            dots: false,
             autoplay:true,
            autoplayTimeout:1000,
            autoplayHoverPause:true,
            nav:true,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:1
                },
                768:{
                    items:2,
                    dots: true            
                },
                1600:{
                    items:3,
                    dots: true            
                }
            }
        })
    }
};

(function($) {
    "use strict";
    owl_carousel_custom.init();
})(jQuery);