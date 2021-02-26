//small-chart1
new Chartist.Bar('.small-chart1', {
    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7'],
    series: [
    [400, 900, 800, 1000, 700, 1000],
    [1000, 500, 600, 400, 700, 400]
    ]
}, {
    plugins: [
    Chartist.plugins.tooltip({
        appendToBody: false,
        className: "ct-tooltip"
    })
    ],
    stackBars: true,
    axisX: {
        showGrid: false,
        showLabel: false,
        offset: 0
    },
    axisY: {
        low: 0,
        showGrid: false,
        showLabel: false,
        offset: 0,
        labelInterpolationFnc: function(value) {
            return (value / 1000) + 'k';
        }
    },
    height: 80,
    width: 120,



}).on('draw', function(data) {
    if(data.type === 'bar') {
        data.element.attr({
            style: 'stroke-width: 5px ; stroke-linecap: round'
        });
    }
});


//small-chart2
new Chartist.Bar('.small-chart2', {
    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7'],
    series: [
    [400, 900, 800, 1000, 700, 1000],
    [1000, 500, 600, 400, 700, 400]
    ]
}, {
    plugins: [
    Chartist.plugins.tooltip({
        appendToBody: false,
        className: "ct-tooltip"
    })
    ],
    stackBars: true,
    axisX: {
        showGrid: false,
        showLabel: false,
        offset: 0
    },
    axisY: {
        low: 0,
        showGrid: false,
        showLabel: false,
        offset: 0,
        labelInterpolationFnc: function(value) {
            return (value / 1000) + 'k';
        }
    },
    height: 80,
    width: 120,

    colors:['#ffffff'],
    fill: {
        opacity: 1

    }

}).on('draw', function(data) {
    if(data.type === 'bar') {
        data.element.attr({
            style: 'stroke-width: 5px ; stroke-linecap: round'
        });
    }
});


//small-chart3
new Chartist.Bar('.small-chart3', {
    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7'],
    series: [
    [400, 900, 800, 1000, 700, 1000],
    [1000, 500, 600, 400, 700, 400]
    ]
}, {
    plugins: [
    Chartist.plugins.tooltip({
        appendToBody: false,
        className: "ct-tooltip"
    })
    ],
    stackBars: true,
    axisX: {
        showGrid: false,
        showLabel: false,
        offset: 0
    },
    axisY: {
        low: 0,
        showGrid: false,
        showLabel: false,
        offset: 0,
        labelInterpolationFnc: function(value) {
            return (value / 1000) + 'k';
        }
    },
    height: 80,
    width: 120,



}).on('draw', function(data) {
    if(data.type === 'bar') {
        data.element.attr({
            style: 'stroke-width: 5px ; stroke-linecap: round'
        });
    }
});


//small-chart4 
new Chartist.Bar('.small-chart4', {
    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7'],
    series: [
    [400, 900, 800, 1000, 700, 1000],
    [1000, 500, 600, 400, 700, 400]
    ]
}, {
    plugins: [
    Chartist.plugins.tooltip({
        appendToBody: false,
        className: "ct-tooltip"
    })
    ],
    stackBars: true,
    axisX: {
        showGrid: false,
        showLabel: false,
        offset: 0
    },
    axisY: {
        low: 0,
        showGrid: false,
        showLabel: false,
        offset: 0,
        labelInterpolationFnc: function(value) {
            return (value / 1000) + 'k';
        }
    },
    height: 80,
    width: 120,

    colors:['#ffffff'],
    fill: {
        opacity: 1

    }

}).on('draw', function(data) {
    if(data.type === 'bar') {
        data.element.attr({
            style: 'stroke-width: 5px ; stroke-linecap: round'
        });
    }
});


// apex chart
var options = {

    chart: {
        height: 350,
        type: 'area',
        toolbar: {
            show: false
        },
    },
    dataLabels: {
        enabled: false
    },
    grid: {
        borderColor: '#f0f7fa',
    },
    stroke: {
        curve: 'smooth',
        width: 4
    },
    series: [{
        name: 'series1',
        data: [50, 45, 55, 50, 60, 56, 58, 50, 65, 60, 50, 60, 52, 55, 52]
    }],

    xaxis: {
        low: 0,
        offsetX: 0,
        offsetY: 0,
        show: false,
        type: 'datetime',
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
        categories: ["2021-09-19T00:00:00", "2021-09-19T01:30:00", "2021-09-19T02:30:00", "2021-09-19T03:30:00", "2021-09-19T04:30:00", "2021-09-19T05:30:00", "2021-09-19T06:30:00", "2021-09-19T07:30:00", "2021-09-19T08:30:00", "2021-09-19T09:30:00", "2021-09-19T10:30:00", "2021-09-19T11:30:00" , "2021-09-19T12:30:00", "2021-09-19T13:30:00", "2021-09-19T14:30:00"],
    },
    yaxis:{
        labels: {
            show: false
        }
    },
    tooltip: {
        x: {
            format: 'dd/MM/yy HH:mm'
        },
    },
    colors: ['#fe80b2'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.6,
            opacityTo: 1.0,
            stops: [0, 85, 100]
        }
    }
}

var chart = new ApexCharts(
    document.querySelector("#area-spaline"),
    options
    );

chart.render();

// earning chart

var optionsearningchart = {
    chart: {
        height: 400,
        type: 'radialBar',
    },
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 20,
                size: "50%"
            },
            startAngle: -135,
            endAngle: 135,
            dataLabels: {
                name: {
                    fontSize: '16px',
                    color: '#ffffff',
                    offsetY: 10
                },
                value: {
                    offsetY: -40,
                    fontSize: '22px',
                    color: '#ffffff',
                    formatter: function (val) {
                        return val + "/100";
                    }
                }
            }
        }
    },

    fill: {
        opacity: 1
    },
    colors:['#ffffff'],
    series: [8*12],
    stroke: {        
        dashArray: 5,
    },
    labels: ['Customer Ratio'],
}

var chartearningchart = new ApexCharts(
    document.querySelector("#customers-ratio"),
    optionsearningchart
    );
chartearningchart.render();


// rounded cap chart
var lineArea = new Chartist.Bar('.ct-10', {
    labels: ['J', 'F', 'M', 'A', 'M', 'J'],
    series: [
    [400, 580, 200, 450, 650, 800]
    ],
}, {
    chartPadding: {
        left: 0,
    },
    axisY: {
        labelInterpolationFnc: function(value) {
            return (value / 1000) + 'k';
        },
        showLabel: false,
        showGrid: false,
        offset: 0
    },
    axisX: {
        showGrid: false,
    }

}).on('draw', function(ctx) {
    if(ctx.type === 'bar') {
        ctx.element.attr({
            x1: ctx.x1 + 0.05,
            style: 'stroke-width: 15px ; stroke-linecap: round'
        });
    }
});
lineArea.on('created', function (ctx) {
    var defs = ctx.svg.elem('defs');
    defs.elem('linearGradient', {
        id: 'gradient',
        x1: 0,
        y1: 1,
        x2: 0,
        y2: 0
    }).elem('stop', {
        offset: 0,
        'stop-color': '#ff9ac2'
    }).parent().elem('stop', {
        offset: 1,
        'stop-color': '#fe6aa4'
    });
});

new Chartist.Bar('.ct-11', {
    labels: ['J', 'A', 'S', 'O', 'N', 'F'],
    series: [
    [250, 150, 200, 100, 400, 150]
    ],
}, {
    seriesBarDistance:150,
    low: 0,
    offset: 0,
    axisY: {
        labelInterpolationFnc: function(value) {
            return (value / 1000) + 'k';
        },
        showLabel: false,
        showGrid: false,
        offset: -26
    },
    chartPadding: {
        right: 0,
    },
    axisX: {
        showGrid: false,
    }
}).on('draw', function(ctx) {
    if(ctx.type === 'bar') {
        ctx.element.attr({
            x1: ctx.x1 + 0.05,
            style: 'stroke-width: 15px ; stroke-linecap: round'
        });
    }
});

