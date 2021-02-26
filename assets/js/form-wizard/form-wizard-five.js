(function(a) {
    a.fn.smartWizard = function(m) {
        var c = a.extend({}, a.fn.smartWizard.defaults, m),
        x = arguments;
        return this.each(function() {
            function C() {
                var e = b.children("div");
                b.children("ul").addClass("anchor");
                e.addClass("content");
                n = a("<div>Loading</div>").addClass("loader");
                k = a("<div></div>").addClass("action-bar");
                p = a("<div></div>").addClass("step-container");
                q = a("<a>" + c.labelNext + "</a>").attr("href", "#").addClass("btn btn-primary");
                r = a("<a>" + c.labelPrevious + "</a>").attr("href", "#").addClass("btn btn-primary");
                s = a("<a>" + c.labelFinish + "</a>").attr("href", "#").addClass("btn btn-primary");
                c.errorSteps && 0 < c.errorSteps.length && a.each(c.errorSteps, function(a, b) {
                    y(b, !0)
                });
                p.append(e);
                k.append(n);
                b.append(p);
                b.append(k);
                c.includeFinishButton && k.append(s);
                k.append(q).append(r);
                z = p.width();
                a(q).click(function() {
                    if (a(this).hasClass("buttonDisabled")) return !1;
                    A();
                    return !1
                });
                a(r).click(function() {
                    if (a(this).hasClass("buttonDisabled")) return !1;
                    B();
                    return !1
                });
                a(s).click(function() {
                    if (!a(this).hasClass("buttonDisabled"))
                        if (a.isFunction(c.onFinish)) c.onFinish.call(this,
                            a(f));
                            else {
                                var d = b.parents("form");
                                d && d.length && d.submit()
                            }
                            return !1
                        });
                a(f).bind("click", function(a) {
                    if (f.index(this) == h) return !1;
                    a = f.index(this);
                    1 == f.eq(a).attr("isDone") - 0 && t(a);
                    return !1
                });
                c.keyNavigation && a(document).keyup(function(a) {
                    39 == a.which ? A() : 37 == a.which && B()
                });
                D();
                t(h)
            }
            function D() {
                c.enableAllSteps ? (a(f, b).removeClass("selected").removeClass("disabled").addClass("done"), a(f, b).attr("isDone", 1)) : (a(f, b).removeClass("selected").removeClass("done").addClass("disabled"), a(f, b).attr("isDone",
                    0));
                a(f, b).each(function(e) {
                    a(a(this).attr("href"), b).hide();
                    a(this).attr("rel", e + 1)
                })
            }
            function t(e) {
                var d = f.eq(e),
                g = c.contentURL,
                h = d.data("hasContent");
                stepNum = e + 1;
                g && 0 < g.length ? c.contentCache && h ? w(e) : a.ajax({
                    url: g,
                    type: "POST",
                    data: {
                        step_number: stepNum
                    },
                    dataType: "text",
                    beforeSend: function() {
                        n.show()
                    },
                    error: function() {
                        n.hide()
                    },
                    success: function(c) {
                        n.hide();
                        c && 0 < c.length && (d.data("hasContent", !0), a(a(d, b).attr("href"), b).html(c), w(e))
                    }
                }) : w(e)
            }
            function w(e) {
                var d = f.eq(e),
                g = f.eq(h);
                if (e != h && a.isFunction(c.onLeaveStep) &&
                    !c.onLeaveStep.call(this, a(g))) return !1;
                    c.updateHeight && p.height(a(a(d, b).attr("href"), b).outerHeight());
                if ("slide" == c.transitionEffect) a(a(g, b).attr("href"), b).slideUp("fast", function(c) {
                    a(a(d, b).attr("href"), b).slideDown("fast");
                    h = e;
                    u(g, d)
                });
                    else if ("fade" == c.transitionEffect) a(a(g, b).attr("href"), b).fadeOut("fast", function(c) {
                        a(a(d, b).attr("href"), b).fadeIn("fast");
                        h = e;
                        u(g, d)
                    });
                        else if ("slideleft" == c.transitionEffect) {
                            var k = 0;
                            e > h ? (nextElmLeft1 = z + 10, nextElmLeft2 = 0, k = 0 - a(a(g, b).attr("href"), b).outerWidth()) :
                            (nextElmLeft1 = 0 - a(a(d, b).attr("href"), b).outerWidth() + 20, nextElmLeft2 = 0, k = 10 + a(a(g, b).attr("href"), b).outerWidth());
                            e == h ? (nextElmLeft1 = a(a(d, b).attr("href"), b).outerWidth() + 20, nextElmLeft2 = 0, k = 0 - a(a(g, b).attr("href"), b).outerWidth()) : a(a(g, b).attr("href"), b).animate({
                                left: k
                            }, "fast", function(e) {
                                a(a(g, b).attr("href"), b).hide()
                            });
                            a(a(d, b).attr("href"), b).css("left", nextElmLeft1);
                            a(a(d, b).attr("href"), b).show();
                            a(a(d, b).attr("href"), b).animate({
                                left: nextElmLeft2
                            }, "fast", function(a) {
                                h = e;
                                u(g, d)
                            })
                        } else a(a(g,
                            b).attr("href"), b).hide(), a(a(d, b).attr("href"), b).show(), h = e, u(g, d);
                        return !0
                    }
                    function u(e, d) {
                        a(e, b).removeClass("selected");
                        a(e, b).addClass("done");
                        a(d, b).removeClass("disabled");
                        a(d, b).removeClass("done");
                        a(d, b).addClass("selected");
                        a(d, b).attr("isDone", 1);
                        c.cycleSteps || (0 >= h ? a(r).addClass("buttonDisabled") : a(r).removeClass("buttonDisabled"), f.length - 1 <= h ? a(q).addClass("buttonDisabled") : a(q).removeClass("buttonDisabled"));
                        !f.hasClass("disabled") || c.enableFinishButton ? a(s).removeClass("buttonDisabled") :
                        a(s).addClass("buttonDisabled");
                        if (a.isFunction(c.onShowStep) && !c.onShowStep.call(this, a(d))) return !1
                    }
                    function A() {
                        var a = h + 1;
                        if (f.length <= a) {
                            if (!c.cycleSteps) return !1;
                            a = 0
                        }
                        t(a)
                    }
                    function B() {
                        var a = h - 1;
                        if (0 > a) {
                            if (!c.cycleSteps) return !1;
                            a = f.length - 1
                        }
                        t(a)
                    }
                    function E(b) {
                        a(".content", l).html(b);
                        l.show()
                    }
                    function y(c, d) {
                        d ? a(f.eq(c - 1), b).addClass("error") : a(f.eq(c - 1), b).removeClass("error")
                    }
                    var b = a(this),
                    h = c.selected,
                    f = a("ul > li > a[href^='#step-']", b),
                    z = 0,
                    n, l, k, p, q, r, s;
                    k = a(".action-bar", b);
                    0 == k.length && (k =
                        a("<div></div>").addClass("action-bar"));
                    l = a(".msg-box", b);
                    0 == l.length && (l = a('<div class="msg-box"><div class="content"></div><a href="#" class="close"><i class="icofont icofont-close-line-circled"></i></a></div>'), k.append(l));
                    a(".close", l).click(function() {
                        l.fadeOut("normal");
                        return !1
                    });
                    if (m && "init" !== m && "object" !== typeof m) {
                        if ("showMessage" === m) {
                            var v = Array.prototype.slice.call(x, 1);
                            E(v[0]);
                            return !0
                        }
                        if ("setError" === m) return v = Array.prototype.slice.call(x, 1), y(v[0].stepnum, v[0].iserror), !0;
                        a.error("Method " + m + " does not exist")
                    } else C()
            })
        };
        a.fn.smartWizard.defaults = {
            selected: 0,
            keyNavigation: !0,
            enableAllSteps: !1,
            updateHeight: !0,
            transitionEffect: "fade",
            contentURL: null,
            contentCache: !0,
            cycleSteps: !1,
            includeFinishButton: !0,
            enableFinishButton: !1,
            errorSteps: [],
            labelNext: "Next",
            labelPrevious: "Previous",
            labelFinish: "Finish",
            onLeaveStep: null,
            onShowStep: null,
            onFinish: null
        }
    })(jQuery);
(function($) {
    "use strict";
    $('#wizard').smartWizard({
        transitionEffect: 'slideleft',
        onFinish: onFinishCallback
    });
    function onFinishCallback() {
        $('#wizard').smartWizard('showMessage', 'All Step Done.. !!!');
    }
})(jQuery);