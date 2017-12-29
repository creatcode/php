/**
 * Created by Administrator on 2017/3/18.
 */
jQuery(function() {
    if ($.fn.sparkline) {
        $(".sparkline").removeClass("hidden");
        var a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z, _, ab, bb, cb, db, eb, fb, gb, hb, ib, jb, kb, lb, mb, nb, ob, pb, qb, rb, sb;
        $(".sparkline:not(:has(>canvas))").each(function () {
            var tb = $(this), ub = tb.data("sparkline-type") || "bar";
            if ("bar" == ub && (a = tb.data("sparkline-bar-color") || tb.css("color") || "#0000f0", b = tb.data("sparkline-height") || "26px", c = tb.data("sparkline-barwidth") || 5, d = tb.data("sparkline-barspacing") || 2, e = tb.data("sparkline-negbar-color") || "#A90329", f = tb.data("sparkline-barstacked-color") || ["#A90329", "#0099c6", "#98AA56", "#da532c", "#4490B1", "#6E9461", "#990099", "#B4CAD3"], tb.sparkline("html", {
                    "barColor": a,
                    "type": ub,
                    "height": b,
                    "barWidth": c,
                    "barSpacing": d,
                    "stackedBarColor": f,
                    "negBarColor": e,
                    "zeroAxis": "false"
                }), tb = null), "line" == ub && (b = tb.data("sparkline-height") || "20px", ab = tb.data("sparkline-width") || "90px", g = tb.data("sparkline-line-color") || tb.css("color") || "#0000f0", h = tb.data("sparkline-line-width") || 1, i = tb.data("fill-color") || "#c0d0f0", j = tb.data("sparkline-spot-color") || "#f08000", k = tb.data("sparkline-minspot-color") || "#ed1c24", l = tb.data("sparkline-maxspot-color") || "#f08000", m = tb.data("sparkline-highlightspot-color") || "#50f050", n = tb.data("sparkline-highlightline-color") || "f02020", o = tb.data("sparkline-spotradius") || 1.5, thisChartMinYRange = tb.data("sparkline-min-y") || "undefined", thisChartMaxYRange = tb.data("sparkline-max-y") || "undefined", thisChartMinXRange = tb.data("sparkline-min-x") || "undefined", thisChartMaxXRange = tb.data("sparkline-max-x") || "undefined", thisMinNormValue = tb.data("min-val") || "undefined", thisMaxNormValue = tb.data("max-val") || "undefined", thisNormColor = tb.data("norm-color") || "#c0c0c0", thisDrawNormalOnTop = tb.data("draw-normal") || !1, tb.sparkline("html", {
                    "type": "line",
                    "width": ab,
                    "height": b,
                    "lineWidth": h,
                    "lineColor": g,
                    "fillColor": i,
                    "spotColor": j,
                    "minSpotColor": k,
                    "maxSpotColor": l,
                    "highlightSpotColor": m,
                    "highlightLineColor": n,
                    "spotRadius": o,
                    "chartRangeMin": thisChartMinYRange,
                    "chartRangeMax": thisChartMaxYRange,
                    "chartRangeMinX": thisChartMinXRange,
                    "chartRangeMaxX": thisChartMaxXRange,
                    "normalRangeMin": thisMinNormValue,
                    "normalRangeMax": thisMaxNormValue,
                    "normalRangeColor": thisNormColor,
                    "drawNormalOnTop": thisDrawNormalOnTop
                }), tb = null), "pie" == ub && (p = tb.data("sparkline-piecolor") || ["#B4CAD3", "#4490B1", "#98AA56", "#da532c", "#6E9461", "#0099c6", "#990099", "#717D8A"], q = tb.data("sparkline-piesize") || 90, r = tb.data("border-color") || "#45494C", s = tb.data("sparkline-offset") || 0, tb.sparkline("html", {
                    "type": "pie",
                    "width": q,
                    "height": q,
                    "tooltipFormat": '<span style="color: {{color}}">&#9679;</span> ({{percent.1}}%)',
                    "sliceColors": p,
                    "borderWidth": 1,
                    "offset": s,
                    "borderColor": r
                }), tb = null), "box" == ub && (t = tb.data("sparkline-width") || "auto", u = tb.data("sparkline-height") || "auto", v = tb.data("sparkline-boxraw") || !1, w = tb.data("sparkline-targetval") || "undefined", x = tb.data("sparkline-min") || "undefined", y = tb.data("sparkline-max") || "undefined", z = tb.data("sparkline-showoutlier") || !0, A = tb.data("sparkline-outlier-iqr") || 1.5, B = tb.data("sparkline-spotradius") || 1.5, C = tb.css("color") || "#000000", D = tb.data("fill-color") || "#c0d0f0", E = tb.data("sparkline-whis-color") || "#000000", F = tb.data("sparkline-outline-color") || "#303030", G = tb.data("sparkline-outlinefill-color") || "#f0f0f0", H = tb.data("sparkline-outlinemedian-color") || "#f00000", I = tb.data("sparkline-outlinetarget-color") || "#40a020", tb.sparkline("html", {
                    "type": "box",
                    "width": t,
                    "height": u,
                    "raw": v,
                    "target": w,
                    "minValue": x,
                    "maxValue": y,
                    "showOutliers": z,
                    "outlierIQR": A,
                    "spotRadius": B,
                    "boxLineColor": C,
                    "boxFillColor": D,
                    "whiskerColor": E,
                    "outlierLineColor": F,
                    "outlierFillColor": G,
                    "medianColor": H,
                    "targetColor": I
                }), tb = null), "bullet" == ub) {
                var vb = tb.data("sparkline-height") || "auto";
                J = tb.data("sparkline-width") || 2, K = tb.data("sparkline-bullet-color") || "#ed1c24", L = tb.data("sparkline-performance-color") || "#3030f0", M = tb.data("sparkline-bulletrange-color") || ["#d3dafe", "#a8b6ff", "#7f94ff"], tb.sparkline("html", {
                    "type": "bullet",
                    "height": vb,
                    "targetWidth": J,
                    "targetColor": K,
                    "performanceColor": L,
                    "rangeColors": M
                }), tb = null
            }
            "discrete" == ub && (N = tb.data("sparkline-height") || 26, O = tb.data("sparkline-width") || 50, P = tb.css("color"), Q = tb.data("sparkline-line-height") || 5, R = tb.data("sparkline-threshold") || "undefined", S = tb.data("sparkline-threshold-color") || "#ed1c24", tb.sparkline("html", {
                "type": "discrete",
                "width": O,
                "height": N,
                "lineColor": P,
                "lineHeight": Q,
                "thresholdValue": R,
                "thresholdColor": S
            }), tb = null), "tristate" == ub && (T = tb.data("sparkline-height") || 26, U = tb.data("sparkline-posbar-color") || "#60f060", V = tb.data("sparkline-negbar-color") || "#f04040", W = tb.data("sparkline-zerobar-color") || "#909090", X = tb.data("sparkline-barwidth") || 5, Y = tb.data("sparkline-barspacing") || 2, Z = tb.data("sparkline-zeroaxis") || !1, tb.sparkline("html", {
                "type": "tristate",
                "height": T,
                "posBarColor": _,
                "negBarColor": V,
                "zeroBarColor": W,
                "barWidth": X,
                "barSpacing": Y,
                "zeroAxis": Z
            }), tb = null), "compositebar" == ub && (b = tb.data("sparkline-height") || "20px", ab = tb.data("sparkline-width") || "100%", c = tb.data("sparkline-barwidth") || 3, h = tb.data("sparkline-line-width") || 1, g = tb.data("data-sparkline-linecolor") || "#ed1c24", _ = tb.data("data-sparkline-barcolor") || "#333333", tb.sparkline(tb.data("sparkline-bar-val"), {
                "type": "bar",
                "width": ab,
                "height": b,
                "barColor": _,
                "barWidth": c
            }), tb.sparkline(tb.data("sparkline-line-val"), {
                "width": ab,
                "height": b,
                "lineColor": g,
                "lineWidth": h,
                "composite": !0,
                "fillColor": !1
            }), tb = null), "compositeline" == ub && (b = tb.data("sparkline-height") || "20px", ab = tb.data("sparkline-width") || "90px", bb = tb.data("sparkline-bar-val"), cb = tb.data("sparkline-bar-val-spots-top") || null, db = tb.data("sparkline-bar-val-spots-bottom") || null, eb = tb.data("sparkline-line-width-top") || 1, fb = tb.data("sparkline-line-width-bottom") || 1, gb = tb.data("sparkline-color-top") || "#333333", hb = tb.data("sparkline-color-bottom") || "#ed1c24", ib = tb.data("sparkline-spotradius-top") || 1.5, jb = tb.data("sparkline-spotradius-bottom") || ib, j = tb.data("sparkline-spot-color") || "#f08000", kb = tb.data("sparkline-minspot-color-top") || "#ed1c24", lb = tb.data("sparkline-maxspot-color-top") || "#f08000", mb = tb.data("sparkline-minspot-color-bottom") || kb, nb = tb.data("sparkline-maxspot-color-bottom") || lb, ob = tb.data("sparkline-highlightspot-color-top") || "#50f050", pb = tb.data("sparkline-highlightline-color-top") || "#f02020", qb = tb.data("sparkline-highlightspot-color-bottom") || ob, thisHighlightLineColor2 = tb.data("sparkline-highlightline-color-bottom") || pb, rb = tb.data("sparkline-fillcolor-top") || "transparent", sb = tb.data("sparkline-fillcolor-bottom") || "transparent", tb.sparkline(bb, {
                "type": "line",
                "spotRadius": ib,
                "spotColor": j,
                "minSpotColor": kb,
                "maxSpotColor": lb,
                "highlightSpotColor": ob,
                "highlightLineColor": pb,
                "valueSpots": cb,
                "lineWidth": eb,
                "width": ab,
                "height": b,
                "lineColor": gb,
                "fillColor": rb
            }), tb.sparkline(tb.data("sparkline-line-val"), {
                "type": "line",
                "spotRadius": jb,
                "spotColor": j,
                "minSpotColor": mb,
                "maxSpotColor": nb,
                "highlightSpotColor": qb,
                "highlightLineColor": thisHighlightLineColor2,
                "valueSpots": db,
                "lineWidth": fb,
                "width": ab,
                "height": b,
                "lineColor": hb,
                "composite": !0,
                "fillColor": sb
            }), tb = null)
        });
    }
});