'use strict';
! function(maps) {
    "use strict";
    var b = function() {};
    b.prototype.init = function() {
        maps("#world-map").vectorMap({
            map: "world_mill_en",
            scaleColors: ["#8a41e5", "#7e37d8"],
            normalizeFunction: "polynomial",
            hoverOpacity: .7,
            hoverColor: !1,
            regionStyle: {
                initial: {
                    fill: "#7e37d8"
                }
            },
            backgroundColor: "transparent",
        }), maps("#asia").vectorMap({
            map: "asia_mill",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#7e37d8"
                } 
            }
        }), maps("#india").vectorMap({
            map: "in_mill",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#06b5dd"
                }
            }
        }), maps("#usa").vectorMap({
            map: "us_aea_en",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#7e37d8"
                }
            }
        }), maps("#uk").vectorMap({
            map: "uk_mill_en",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#FF5370"
                }
            }
        }), maps("#canada").vectorMap({
            map: "uk_mill_en",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#fd517d"
                }
            }
        }),maps("#chicago").vectorMap({
            map: "us-il-chicago_mill_en",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#fb6d9d"
                }
            }
        }),maps("#australia").vectorMap({
            map: "au_mill",
            backgroundColor: "transparent",
            regionStyle: {
                initial: {
                    fill: "#263238"
                }
            }
        })
    }, maps.VectorMap = new b, maps.VectorMap.Constructor = b
}(window.jQuery),
function(maps) {
    "use strict";
    maps.VectorMap.init()
}(window.jQuery);
