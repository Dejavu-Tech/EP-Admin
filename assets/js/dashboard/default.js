$(window).on('load',function(){
    $('#loadModal').modal('show');
});
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
// apex widgets card charts
var options = {
    chart: {
        height: 120,
        type: "line",
        stacked: false,
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            enabledOnSeries: undefined,
            top: 2,
            left: 10,
            blur: 2,
            color: '#561ba3',
            opacity: 0.5
        }
    },
    dataLabels: {
        enabled: false
    },
    series: [
        {
            name: "Series 1",
            data: [50, 40, 60, 38, 52]
        }
    ],
    stroke: {
        lineCap: 'butt',
        width: [ 8 ],
        curve: 'smooth'
    },
    colors: ["#ffffff"],
    grid: {
        show: false,
        padding: {
            left: 0,
            right: 0
        }
    },
    yaxis: {
        show: false,
    },
    xaxis: {
        categories: [
            "01 Jan",
            "02 Jan",
            "03 Jan",
            "04 Jan",
            "05 Jan"
        ],
        low: 0,
        offsetX: 0,
        offsetY: 0,
        show: false,
        labels: {
            low: 0,
            offsetX: 0,
            show: false,
        },
        axisBorder: {
            low: 0,
            offsetX: 0,
            show: false,
        }
    }
};

var chart = new ApexCharts(document.querySelector("#area-widget-chart"), options);

chart.render();

// small chart

var options1 = {
    chart: {
        height: 130,
        type: "line",
        stacked: false,
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            enabledOnSeries: undefined,
            top: 2,
            left: 10,
            blur: 2,
            color: '#fd3484',
            opacity: 0.5
        }
    },
    dataLabels: {
        enabled: false
    },
    series: [
        {
            name: "Series 1",
            data: [40, 60, 40, 60, 55, 65]
        }
    ],
    stroke: {
        lineCap: 'butt',
        width: [ 8 ],
        curve: 'smooth'
    },
    colors: ["#ffffff"],
    grid: {
        show: false,
        padding: {
            left: 0,
            right: 0
        }
    },
    yaxis: {
        show: false,
    },
    xaxis: {
        categories: [
            "01 Jan",
            "02 Jan",
            "03 Jan",
            "04 Jan",
            "05 Jan",
            "06 Jan"
        ],
        low: 0,
        offsetX: 0,
        offsetY: 0,
        show: false,
        labels: {
            low: 0,
            offsetX: 0,
            show: false,
        },
        axisBorder: {
            low: 0,
            offsetX: 0,
            show: false,
        }
    }
};

var chart1 = new ApexCharts(document.querySelector("#area-widget-chart-2"),
    options1
);

chart1.render();


var options2 = {
    chart: {
        height: 130,
        type: "line",
        stacked: false,
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            enabledOnSeries: undefined,
            top: 2,
            left: 10,
            blur: 2,
            color: '#fbbc30',
            opacity: 0.5
        }
    },
    dataLabels: {
        enabled: false
    },
    series: [
        {
            name: "Series 1",
            data: [70, 50, 70, 50, 60]
        }
    ],
    stroke: {
        lineCap: 'butt',
        width: [ 8 ],
        curve: 'smooth'
    },
    colors: ["#ffffff"],
    grid: {
        show: false,
        padding: {
            left: 0,
            right: 0
        }
    },
    yaxis: {
        show: false,
    },
    xaxis: {
        categories: [
            "01 Jan",
            "02 Jan",
            "03 Jan",
            "04 Jan",
            "05 Jan"
        ],
        low: 0,
        offsetX: 0,
        offsetY: 0,
        show: false,
        labels: {
            low: 0,
            offsetX: 0,
            show: false,
        },
        axisBorder: {
            low: 0,
            offsetX: 0,
            show: false,
        }
    }
};

var chart2 = new ApexCharts(document.querySelector("#area-widget-chart-3"),
    options2
);

chart2.render();


var options3 = {
    chart: {
        height: 130,
        type: "line",
        stacked: false,
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            enabledOnSeries: undefined,
            top: 2,
            left: 10,
            blur: 2,
            color: '#06b5ca',
            opacity: 0.5
        }
    },
    dataLabels: {
        enabled: false
    },
    series: [
        {
            name: "Series 1",
            data: [30, 20, 70, 40, 60, 50]
        }
    ],
    stroke: {
        lineCap: 'butt',
        width: [ 8 ],
        curve: 'smooth'
    },
    colors: ["#ffffff"],
    grid: {
        show: false,
        padding: {
            left: 0,
            right: 0
        }
    },
    yaxis: {
        show: false,
    },
    xaxis: {
        categories: [
            "01 Jan",
            "02 Jan",
            "03 Jan",
            "04 Jan",
            "05 Jan",
            "06 Jan"
        ],
        low: 0,
        offsetX: 0,
        offsetY: 0,
        show: false,
        labels: {
            low: 0,
            offsetX: 0,
            show: false,
        },
        axisBorder: {
            low: 0,
            offsetX: 0,
            show: false,
        }
    }
};

var chart3 = new ApexCharts(document.querySelector("#area-widget-chart-4"),
    options3
);

chart3.render();

// circle chart
var options4 = {
    chart: {
        height: 372,
        type: 'radialBar',
        fullWidth: true,
    },
    plotOptions: {
        padding: {
            left: 0,
            right: 0
        },
        radialBar: {
            hollow: {
                size: '40%',
            },
            track: {
               show: false
            },
            dataLabels: {
                name: {
                    fontSize: '22px',
                },
                value: {
                    fontSize: '16px',
                },
                total: {
                    show: true,
                    label: 'Total',
                    formatter: function (w) {
                        return 75
                    }
                }
            }
        }
    },
    fill: {
        //pocoAdminConfig.primary
        colors:[pocoAdminConfig.primary, '#fe6aa4', '#168ef7'],
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
    colors: ['#5a1aab', '#fe6aa4', '#168ef7'],
    series: [60, 67, 80],
    labels: ['UK', 'New York', 'Chaina'],
    stroke: {
        lineCap: "round",
    }
}

var chart4 = new ApexCharts(
    document.querySelector("#circlechart"),
    options4
);

chart4.render();

// area range
var options5 = {
    chart: {
        height: 230,
        type: 'area',
        zoom: {
            enabled: false
        },
        toolbar: {
            show: false
        },

    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        width: [ 8 ],
        curve: 'straight'
    },
    fill: {
        opacity: 0.4,
        type: 'solid',
        colors: ['#fd3484']
    },
    series: [{
        name: "STOCK ABC",
        data: [
            8121.85,
            8128.0,
            8122.9,
            8165.5,
            8340.7,
            8514.3,
            8423.5,
            8514.3,
            8481.85,
            8487.7,
            8506.9,
            8626.2,
            8668.95,
            8602.3,
            8607.55,
            8512.9,
            8496.25,
            8876.00,
            9040.65,
            9240.85
        ]
    }],
    labels: series.monthDataSeries1.dates,
    grid: {
        show: false,
        padding: {
            left: 0,
            right: 0,
            bottom: -40,
            top: -30
        }
    },
    xaxis: {
        type: 'datetime',
    },
    yaxis: {
        show: false,
    },
    legend: {
        horizontalAlign: 'left'
    },
    colors:['#fff']

}

var chart5 = new ApexCharts(
    document.querySelector("#area-range"),
    options5
);

chart5.render();


var options6 = {
    chart: {
        height: 230,
        type: 'area',
        zoom: {
            enabled: false
        },
        toolbar: {
            show: false
        },

    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        width: [ 8 ],
        curve: 'straight'
    },
    fill: {
        opacity: 0.4,
        type: 'solid',
        colors: ['#571f9e']
    },
    series: [{
        name: "STOCK ABC",
        data: [
            8121.85,
            8128.0,
            8520.87,
            8620.5,
            8750.7,
            8680.3,
            8640.00,
            8599.45,
            8581.85,
            8689.75,
            8575.7,
            8645.9,
            8899.95,
            8998.86,
            9090.42,
            9040.00,
            9190.85,
            9040.00,
            9140.65,
            9240.85
        ]
    }],
    labels: series.monthDataSeries1.dates,
    grid: {
        show: false,
        padding: {
            left: 0,
            right: 0,
            bottom: -40,
            top: -30
        }
    },
    xaxis: {
        type: 'datetime',
    },
    yaxis: {
        show: false,
    },
    legend: {
        horizontalAlign: 'left'
    },
    colors:['#fff']

}

var chart6 = new ApexCharts(
    document.querySelector("#area-range-1"),
    options6
);

chart6.render();
