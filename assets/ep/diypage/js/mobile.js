define(['core', 'tpl'], function (core, tpl) {
    var modal = {location: {lat: '0', lng: '0'}};
    modal.init = function (params) {
        modal.initNotice();
        modal.initSwiper();
        modal.initLocation();
        modal.initAudio();

        $("form").submit(function () {
            $(this).find("input[name='keywords']").blur();
        });
    };
    modal.initNotice = function () {
        if ($(".fui-notice").length > 0) {
            $(".fui-notice").each(function () {
                var _this = $(this);
                var speed = _this.data('speed') * 1000;
                setInterval(function () {
                    var length = _this.find("li").length;
                    if (length > 1) {
                        _this.find("ul").animate({marginTop: "-1rem"}, 500, function () {
                            $(this).css({marginTop: "0px"}).find("li:first").appendTo(this)
                        })
                    }
                }, speed)
            })
        }
    };
    modal.initSwiper = function () {

        if($('[data-toggle="timer"]').length>0){
            require(['../addons/ewei_shopv2/plugin/seckill/assets/js/timer.js'],function(timerUtil){
                timerUtil.initTimers();
            });
        }

        if ($(".swiper").length > 0) {
            require(['swiper'], function (modal) {
                $(".swiper").each(function () {
                    var obj = $(this);
                    var ele = $(this).data('element');
                    var container = ele + " .swiper-container";
                    var view = $(this).data('view');
                    var btn = $(this).data('btn');
                    var free = $(this).data('free');
                    var space = $(this).data('space');
                    var callback = $(this).data('callback');
                    var slideTo = $(this).data('slideto');
                    var options = {
                        pagination: container + ' .swiper-pagination',
                        slidesPerView: view,
                        paginationClickable: true,
                        autoHeight: true,
                        nextButton: container + ' .swiper-button-next',
                        prevButton: container + ' .swiper-button-prev',
                        spaceBetween: space > 0 ? space : 0,
                        //preventClicks : false,
                        preventLinksPropagation : true,
                        onSlideChangeEnd: function (swiper) {
                            if (swiper.isEnd && callback) {
                                if (callback == 'seckill') {
                                     location.href = core.getUrl('seckill');
                                }
                            }
                        }
                    };
                    if (!btn) {
                        delete options.nextButton;
                        delete options.prevButton;
                        $(container).find(".swiper-button-next").remove();
                        $(container).find(".swiper-button-prev").remove()
                    }
                    if (free) {
                        options.freeMode = true
                    }
                    var swiper = new Swiper(container, options);
                    if(slideTo){
                        swiper.slideTo(slideTo, 0, false);
                    }
                });
            })
        }
    };
    modal.initLocation = function () {
        if ($(".merchgroup[data-openlocation='1']").length > 0) {
            var geoLocation = new BMap.Geolocation();
            window.modal = modal;
            geoLocation.getCurrentPosition(function (result) {
                if (this.getStatus() == BMAP_STATUS_SUCCESS) {
                    modal.location.lat = result.point.lat;
                    modal.location.lng = result.point.lng;
                    modal.initMerch()
                } else {
                    FoxUI.toast.show("位置获取失败!");
                    return
                }
            }, {enableHighAccuracy: true})
        }
    };
    modal.initMerch = function () {
        $(".merchgroup").each(function () {
            var _this = $(this);
            var item = _this.data('itemdata');
            if (!item || !item.params.openlocation) {
                return
            }
            core.json('diypage/getmerch', {
                lat: modal.location.lat,
                lng: modal.location.lng,
                item: item
            }, function (result) {
                if (result.status == 1) {
                    var list = result.result.list;
                    if (list) {
                        _this.empty();
                        $.each(list, function (id, merch) {
                            var thumb = merch.thumb ? merch.thumb : '../addons/ewei_shopv2/plugin/diypage/assets/ep/images/default/logo.jpg';
                            var html = '';
                            html = '<div class="fui-list jump">';
                            html += '<a class="fui-list-media" href="' + core.getUrl("merch", {merchid: merch.id}) + '" data-nocache="true"><img src="' + thumb + '"/></a>';
                            html += '<a class="fui-list-inner" href="' + core.getUrl("merch", {merchid: merch.id}) + '" data-nocache="true">';
                            html += '<div class="title" style="color: ' + item.style.titlecolor + ';">' + merch.name + '</div>';
                            if (merch.desc) {
                                html += '<div class="subtitle" style="color: ' + item.style.textcolor + ';">' + merch.desc + '</div>'
                            }
                            if (merch.distance && item.params.openlocation) {
                                html += '<div class="subtitle" style="color: ' + item.style.rangecolor + '; font-size: 0.6rem"><i class="icon icon-dingwei1" style="color: ' + item.style.rangecolor + '; font-size: 0.6rem;"></i>距离您: ' + merch.distance + 'km</div>'
                            }
                            html += '</a>';
                            html += '<a class="fui-remark jump" style="padding-right: 0.2rem; height: 2rem; width: 2rem; text-align: center; line-height: 2rem;" href="' + core.getUrl("merch/map", {merchid: merch.id}) + '" data-nocache="true">';
                            html += '</a>';
                            html += '</div>';
                            _this.append(html)
                        });
                        _this.show()
                    }
                }
            }, true, true)
        })
    };
    modal.initAudio = function () {
        if ($(".play-audio").length > 0) {
            $(".play-audio").each(function () {
                var _this = $(this);
                var autoplay = _this.data('autoplay');
                var audio = _this.find("audio")[0];
                var duration = audio.duration;
                if(!isNaN(duration)){
                    var time = modal.formatSeconds(duration);
                    _this.find(".time").text(time).show();
                }

                if (autoplay) {
                    //modal.playAudio(_this)
                }
                $(_this).click(function () {
                    if (!audio.paused) {
                        modal.stopAudio(_this)
                    } else {
                        modal.playAudio(_this)
                    }
                })
            })
        }
    };
    modal.playAudio = function (_this) {
        _this.siblings().find("audio").each(function () {
            var __this = $(this).closest(".play-audio");
            modal.stopAudio(__this)
        });
        var audio = _this.find("audio")[0];
        var duration = audio.duration;

        if(!isNaN(duration)){
            var time = modal.formatSeconds(duration);
            _this.find(".time").text(time).show();
        }

        audio.play();
        _this.find(".horn").addClass('playing');
        if (audio.paused) {
            _this.find(".speed").css({width: '0px'})
        }
        var timer = setInterval(function () {
            var currentTime = audio.currentTime;
            if (currentTime >= duration) {
                modal.stopAudio(_this);
                clearInterval(timer)
            }
            var _thiswidth = _this.outerWidth();
            var _width = (currentTime / duration) * _thiswidth;
            _this.find(".speed").css({width: _width + 'px'})
        }, 1000)
    };
    modal.stopAudio = function (_this) {
        var audio = _this.find("audio")[0];
        if (audio) {
            var stop = _this.data('pausestop');
            if (stop) {
                audio.currentTime = 0
            }
            audio.pause();
            _this.find(".horn").removeClass('playing')
        }
    };
    modal.formatSeconds = function (value) {
        var theTime = parseInt(value);
        var theTime1 = 0;
        var theTime2 = 0;
        if (theTime > 60) {
            theTime1 = parseInt(theTime / 60);
            theTime = parseInt(theTime % 60);
            if (theTime1 > 60) {
                theTime2 = parseInt(theTime1 / 60);
                theTime1 = parseInt(theTime1 % 60)
            }
        }
        var result = "" + parseInt(theTime) + "''";
        result = "" + parseInt(theTime1) + "'" + result;
        if (theTime2 > 0) {
            result = "" + parseInt(theTime2) + "'" + result
        }
        return result
    };
    return modal
});
