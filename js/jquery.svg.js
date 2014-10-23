(function($) {
    
    computeHeight = function(svg) {
        var height = 0;
        var scale = 1;
        var attr = $("#g[transform]", svg.root()).attr("transform");
        if (typeof attr !== 'undefined' && attr !== false) {
            if (attr.match(/scale\((\d\.?\d*)/i))
                scale = parseFloat(RegExp.$1);
        }

        $("text:visible", svg.root()).each(function() {
            height += 7;
        });
        height = height * scale;
        var minheight = null;
        var maxheight = null;
        $("polyline", svg.root()).each(function() {
            var data = $(this).attr("points");
            if (data.match(/(\d*\.*\d*)[\s/,](\d*\.*\d*)\s+(\d*\.*\d*)[\s/,](\d*\.*\d*)/i)) {
                var max = Math.max(parseFloat(RegExp.$1), parseFloat(RegExp.$3));
                var min = Math.min(parseFloat(RegExp.$1), parseFloat(RegExp.$3));

                var maxH = Math.max(parseFloat(RegExp.$2), parseFloat(RegExp.$4));
                var minH = Math.min(parseFloat(RegExp.$2), parseFloat(RegExp.$4));

                if (maxheight == null || maxH > maxheight)
                    maxheight = maxH;
                if (minheight == null || minH < minheight)
                    minheight = minH;
            }
        });
        var additionalH = 50;
        $("text", svg.root()).each(function() {
            $(this).css("text-decoration", "none");
            var txt = $(this).text();
            if (txt.match(/\_restricted/i)) {
                additionalH += 60;
            }
            if (txt.match(/\_rerooted/i)) {
                additionalH += 30;
            }
        });
        $("circle", svg.root()).each(function() {
            additionalH += 10;
        });
        var adjust = 0;
        var frameHeight = (maxheight - minheight + additionalH) * scale;
        if (frameHeight < 100)
            frameHeight += 10;
        if (frameHeight < 200)
            frameHeight += 35;
        if (frameHeight > 200)
            frameHeight += 60;
        if (frameHeight > 400)
            frameHeight += 20;
        if (frameHeight > 600)
            frameHeight += 20;
        return frameHeight;
    }
    
    $.fn.focusOn = function(svg) {
        var width = "525px";
        var height = computeHeight(svg);
        
        $(this).css("width",width);
        $(this).css("height",height);
    }
    
    roundValues = function(svg) {
        $("text", svg.root()).each(function() {
            if ($(this).attr('id') == "T1 DBL") {
                if ($(this).text().length > 4) {
                    var lgr = parseFloat($(this).text());
                    lgr = roundNumber(lgr, 3);
                    $(this).text(lgr);
                    $(this).attr("x", parseFloat($(this).attr("x")) + 7);
                }
            }
        });
    }

    enableSVG = function(svg) {
        var attr = $("g", svg.root()).attr("width");
        if (typeof attr !== 'undefined' && attr !== false) {
            var width = $("g", svg.root()).attr("width");
            var height = $("g", svg.root()).attr("height");
            $(svg.root()).css("width", width)
                         .css("height", height);
            $(this).css("height", height)
                    .css("margin", "0 auto")
                    .css("width", width);
        } else {
            var frameHeight = computeHeight(svg);
            $(this).css("height", frameHeight + "px")
                    .css("margin", "0 auto")
                    .css("width", "525px")
            $(svg.root()).css("width", "525px")
                    .css("height", frameHeight + "px");
        }
        roundValues(svg);
        $('#Legend', svg.root()).drag();
        $("text", svg.root()).each(function() {
            $(this).css("text-decoration", "none");
        });
        $("text", svg.root()).click(function() {swap($(this), svg);});
    }

    enableSVGNoSizeL = function(svg) {
        roundValues(svg);
        $("text", svg.root()).each(function() {
            $(this).css("text-decoration", "none");
        });
        $('#Legend', svg.root()).drag();
        $("text", svg.root()).click(function() {
            swap($(this), svg)
        });
        if($("#left_params").text() !== "")
            $("g", svg.root()).attr("transform", $("#left_params").text());
        resizeOnBox(svg, "left");
    }

    enableSVGNoSizeR = function(svg) {
        roundValues(svg);
        $("text", svg.root()).each(function() {
            $(this).css("text-decoration", "none");
        });
        $('#Legend', svg.root()).drag();
        $("text", svg.root()).click(function() {
            swap($(this), svg)
        });
        if($("#right_params").text() !== "")
            $("g", svg.root()).attr("transform", $("#right_params").text());
        resizeOnBox(svg, "right");
    }

    resizeOnBox = function(svg, side) {
        $(svg.root()).css("width", $("#" + side + "_tree_svg").css("width"))
                     .css("height", $("#" + side + "_tree_svg").css("height"));
    }

    $.fn.drag = function() {
        var mouseX, mouseY, mousedown;
        $(this).mousedown(function(evt) {
            mouseX = evt.pageX;
            mouseY = evt.pageY;
            mousedown = true;
        });
        $(this).mousemove(function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            $('body').mouseup(function(e) {
                mousedown = false;
            });
            if (mousedown == true) {
                var newmouseX = e.pageX;
                var newmouseY = e.pageY;
                var translateX = (newmouseX - mouseX) * 0.67;
                var translateY = (newmouseY - mouseY) * 0.67;
                $(this).attr('x', parseFloat($(this).attr('x')) + translateX);
                $(this).attr('y', parseFloat($(this).attr('y')) + translateY);
                mouseX = newmouseX;
                mouseY = newmouseY;
            }
        });
    }

    swap = function(elm, svg) {
        if ($("#swapEN").attr("value") != "0") {
            elm.css({'font-weight': 'bold'});
            var taxID = elm.attr("id");
            if ($("#Dswap").attr("value") == "1") {
                if ($("#taxa1").attr("value") == taxID) {
                    $(elm).css('font-weight', 'normal');
                    $("#Dswap").attr("value", "0");
                    $("#taxa1").attr("value", "0");
                    $("#treeswap").attr("value", "0");
                    return false;
                }
                else {
                    $("#taxa2").attr("value", taxID);
                    $("#swap").attr("action", $("#swap").attr("action") + "&treeswap=" + $("#swapEN").val());
                    // form submit
                    $('#submitProject').foundation('reveal', 'open');
                    $("#swap").submit();
                    return false;
                }
            }
            else {
                $("#Dswap").attr("value", "1");
                $("#taxa1").attr("value", taxID);
            }
        }
    }

    controlSVG = function(action, svg) {
        var transform = $("g", svg.root()).attr("transform");
        //translate(10, 10) scale(1.5)
        var params = transform.match(/translate\(([^,]+),\s*([^\)]+)\)\s*scale\(([^\)]+)\)/);
        var tx = parseInt(params[1]);
        var ty = parseInt(params[2]);
        var sc = parseFloat(params[3]);
        // move SVG
        if (action == "up"
                || action == "down"
                || action == "left"
                || action == "right") {
            if (action == "up")
                ty += (30 * sc);
            if (action == "down")
                ty -= (30 * sc);
            if (action == "left")
                tx += (30 * sc)
            if (action == "right")
                tx -= (30 * sc);
        } else { // scale svg
            if (action == "in") {
                sc += 0.2;
            } else {
                if (sc > 0.2)
                    sc -= 0.2;
            }
        }
        transform = "translate(" + tx + ", " + ty + ") scale(" + sc + ")";
        //$("g", svg.root()).attr("transform", transform);
        $("g", svg.root()).animate({svgTransform: transform}, 400, "linear");
    }

    saveSVG = function(tree, svg, reverse, side, width, height) {
        if ($("#inputhashand").attr('value') == '1') {
            var params = $("g", svg.root()).attr("transform");
            var dataString = "tree=" + tree + "&params=" + params + "&reverse=" + reverse + "&side=" + side + "&width=" + width + "&height=" + height;
            $.ajax({
                type: "POST",
                url: "ajax/ajax_saveSVG.php",
                data: dataString
            });
        }
    }

})(jQuery)