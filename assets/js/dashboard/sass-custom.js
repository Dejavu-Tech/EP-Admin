// small charts
new Chartist.Bar('.small-sasschart', {
  labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
  series: [
    [200, 400, 300, 100, 250]
  ]
}, {
  plugins: [
    Chartist.plugins.tooltip({
      appendToBody: false,
      className: "ct-tooltip"
    })
  ],
  axisY: {
    low: 0,
    showGrid: false,
    showLabel: false,
    offset: 0
  },
  axisX: {
    showGrid: false,
    showLabel: false,
    offset: 0
  }
}).on('draw', function(data) {
  if(data.type === 'bar') {
    data.element.attr({
      style: 'stroke-width: 2px'
    });
  }
});
new Chartist.Bar('.small-sasschart-1', {
  labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
  series: [
    [200, 400, 300, 100, 250]
  ]
}, {
  plugins: [
    Chartist.plugins.tooltip()
  ],
  axisY: {
    low: 0,
    showGrid: false,
    showLabel: false,
    offset: 0
  },
  axisX: {
    showGrid: false,
    showLabel: false,
    offset: 0
  }
}).on('draw', function(data) {
  if(data.type === 'bar') {
    data.element.attr({
      style: 'stroke-width: 2px'
    });
  }
});
new Chartist.Bar('.small-sasschart-2', {
  labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
  series: [
    [200, 400, 300, 100, 250]
  ]
}, {
  plugins: [
    Chartist.plugins.tooltip()
  ],
  axisY: {
    low: 0,
    showGrid: false,
    showLabel: false,
    offset: 0
  },
  axisX: {
    showGrid: false,
    showLabel: false,
    offset: 0
  }
}).on('draw', function(data) {
  if(data.type === 'bar') {
    data.element.attr({
      style: 'stroke-width: 2px'
    });
  }
});
new Chartist.Bar('.small-sasschart-3', {
  labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
  series: [
    [200, 400, 300, 100, 250]
  ]
}, {
  plugins: [
    Chartist.plugins.tooltip()
  ],
  axisY: {
    low: 0,
    showGrid: false,
    showLabel: false,
    offset: 0
  },
  axisX: {
    showGrid: false,
    showLabel: false,
    offset: 0
  }
}).on('draw', function(data) {
  if(data.type === 'bar') {
    data.element.attr({
      style: 'stroke-width: 2px'
    });
  }
});
new Chartist.Bar('.small-sasschart-4', {
  labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
  series: [
    [200, 400, 300, 100, 250]
  ]
}, {
  plugins: [
    Chartist.plugins.tooltip()
  ],
  axisY: {
    low: 0,
    showGrid: false,
    showLabel: false,
    offset: 0
  },
  axisX: {
    showGrid: false,
    showLabel: false,
    offset: 0
  }
}).on('draw', function(data) {
  if(data.type === 'bar') {
    data.element.attr({
      style: 'stroke-width: 2px'
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
    curve: 'smooth'
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
      show: false,
    },
    axisBorder: {
      low: 0,
      offsetX: 0,
      show: false,
    },
    axisTicks: {
      show: false,
    },
    categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00", "2018-09-19T07:30:00", "2018-09-19T08:30:00", "2018-09-19T09:30:00", "2018-09-19T10:30:00", "2018-09-19T11:30:00" , "2018-09-19T12:30:00", "2018-09-19T13:30:00", "2018-09-19T14:30:00"],
  },
  tooltip: {
    x: {
      format: 'dd/MM/yy HH:mm'
    },
  },
  colors: ['#2bd175'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.5,
      opacityTo: 0.4,
      stops: [0, 95, 100]
    }
  }
}

var chart = new ApexCharts(
    document.querySelector("#area-spaline"),
    options
);

chart.render();

// map js
! function(maps) {
  "use strict";
  var b = function() {};
  b.prototype.init = function() {
    maps("#usa").vectorMap({
      map: "us_aea_en",
      backgroundColor: "transparent",
      regionStyle: {
        initial: {
          fill: "#e8f4fe"
        }
      }
    })
  }, maps.VectorMap = new b, maps.VectorMap.Constructor = b
}(window.jQuery),
    function(maps) {
      "use strict";
      maps.VectorMap.init()
    }(window.jQuery);

// radial apex chart
var options1 = {
  chart: {
    height: 350,
    type: 'radialBar',
  },
  plotOptions: {
    radialBar: {
      hollow: {
        size: '30%',
      },
      dataLabels: {
        name: {
          fontSize: '28px',
        },
        value: {
          fontSize: '20px',
        },
        total: {
          show: true,
          label: 'Total',
          formatter: function (w) {
            return 75
          }
        }
      },
    }
  },
  series: [ 80, 20],
  labels: ['Profit', 'Loss'],
  colors: ['#188ef7', '#fa4977'],
  stroke: {
    lineCap: "round",
  }
}

var chart1 = new ApexCharts(
    document.querySelector("#radial"),
    options1
);

chart1.render();

// chartist chart
var lineArea = new Chartist.Bar('.ct-10', {
  labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8', 'Q9', 'Q10', 'Q11', 'Q13', 'Q14'],
  series: [
    [300, 600, 500, 800, 500, 400, 650, 650, 650, 900, 300, 600, 300],
    [400, 200, 100, 100, 300, 200, 50, 200, 50, null, 100, 200, 400]
  ]
}, {
  stackBars: true,
  axisY: {
    labelInterpolationFnc: function(value) {
      return (value / 1000) + 'k';
    }
  },
  axisX: {
    showLabel: false,
    showGrid: false,
    offset: 0
  }
}).on('draw', function(ctx) {
  if(ctx.type === 'bar') {
    ctx.element.attr({
      x1: ctx.x1 + 0.05,
      style: 'stroke-width: 10px ; stroke-linecap: round'
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
    'stop-color': 'rgba(234, 57, 103, 1)'
  }).parent().elem('stop', {
    offset: 1,
    'stop-color': 'rgba(255, 79, 96, 1)'
  });
});

