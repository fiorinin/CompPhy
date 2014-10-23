(function($) {

    roundNumber = function(num, dec) {
        var result = Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
        return result;
    }

    changeTitle = function(t) {
        $('title').text(t);
    };

    hideTabs = function() {
        $('.tabmenu').each(function() {
            $(this).hide('slow');
        });
    }

    getData = function(div) {
        var id = $('#' + div).attr('name');
        var side = $('#' + div).attr('id');
        $.ajax({
            type: "GET",
            dataType: "xml",
            url: "ajax/ajax_data.php?id=" + id,
            error: function() {
                //alert( "Erreur AJAX, veuillez contacter un administrateur.");
            },
            complete: function(data, status) {
                var retour = data.responseXML;
                $(retour).find('item').each(function() {
                    //alert($(this).text());
                    if ($(this).attr('id') == "newick") {
                        //alert("#"+side+"_tree");
                        var newick = $(this).text();
                        $("#" + side + "_tree").val(newick);
                    }
                    else if ($(this).attr('id') == "script") {
                        var script = $(this).text();
                        $("#" + side + "_script").val(script);
                    }
                    else if ($(this).attr('id') == "annotation") {
                        var annotation = $(this).text();
                        $("#" + side + "_annotation").val(annotation);
                    }
                });
            }
        });
    };

    getToColorize = function(field) {
        var id = $(field).attr('value');
        if (field !== "none") {
            $.ajax({
                type: "GET",
                dataType: "xml",
                url: "ajax/ajax_colorize.php?id=" + id,
                error: function() {
                    //alert( "Erreur AJAX, veuillez contacter un administrateur." );
                },
                complete: function(data, status) {
                    var retour = data.responseXML;
                    $(retour).find('item').each(function() {
                        var taxon = $(this).attr('id');
                        var tocolor = $(this).text();

                        $("#div_" + taxon).css("background-color", tocolor);
                        $("#color_" + taxon).attr('checked', true);
                        $("#c_" + taxon).attr('value', tocolor);
                        $("#colorspan_" + taxon).addClass("checked");
                    });
                }
            });
        }
        else {
            $(".colorTaxon").css("background-color", "#ffffff");
            $(".colorLabel").attr('checked', false);
            $(".hiddenColor").attr('value', "black");
            $(".customSpanCheckbox").removeClass("checked");
        }
    };

    computeDist = function(tree1, tree2) {
        var id1 = $(tree1).attr('value');
        var id2 = $(tree2).attr('value');
        $.ajax({
            type: "GET",
            dataType: "xml",
            url: "ajax/ajax_distance.php?id1=" + id1 + "&id2=" + id2,
            error: function() {
                //alert( "Erreur AJAX, veuillez contacter un administrateur." );
            },
            complete: function(data) {
                var retour = data.responseXML;
                var title = $(retour).find('#title').text();
                if (title != '') {
                    var content = $(retour).find('#content').text().replace("\n", "<br>");
                    var value = $(retour).find('#value').text();
                    var note = $(retour).find('#note').text();
                    $("#infocontent").html('<h4 class="subheader">' + title + '</h4><strong>' + content + (value != '' ? value : '') + '</strong><br><br><small>' + note + '</small>');
                    $('#informationMessage').foundation('reveal', 'open');
                } else {
                    var error = $(retour).find('#error').text();
                    $("#infocontent").html('<h4 class="subheader">Error</h4><strong>' + error + '</strong>');
                    $('#informationMessage').foundation('reveal', 'open');
                }
            }
        });
    }

    $.fn.makeVisible = function(forcehide) {
        if ($(this).css('font-weight') != 'bold' && !forcehide) {
            $(this).css('font-weight', 'bold');
            $(this).css('color', '#069');
            $(this).parent().css('border', '3px solid #069');
        }
        else {
            $(this).css('font-weight', 'normal');
            $(this).css('color', '#000');
            $(this).parent().css('border', '1px solid #a0a0a0');
        }
    };

    $.fn.updateColor = function(color) {
        var $old = $('img');
        var i;
        $("#color_setter").attr('value', RGBToHex(color));
        for (i = 0; i < $old.length; i++)
        {
            if ($old[i].id == 'blue' || $old[i].id == 'green' || $old[i].id == 'yellow' || $old[i].id == 'purple' || $old[i].id == 'grey' || $old[i].id == 'orange' || $old[i].id == 'red' || $old[i].id == 'brown')
                $old[i].style.border = 'solid 4px white';
            if ($old[i].id == $(this).attr('id'))
                $old[i].style.border = 'solid 4px #cccccc';
        }
    };

    $.fn.splitId = function() {
        return $(this).attr('id').split('_')[1];
    }

    handleHistory = function(idproj, id, content, requete) {
        var save = false;
        if ($("#saveproject").is(':checked')) {
            save = true;
        }
        var dataString = 'idproj=' + idproj + '&id=' + id + '&content=' + content + '&requete=' + requete + "&save=" + save;
        $.ajax({
            type: "POST",
            url: "ajax/ajax_history.php",
            data: dataString,
            complete: function(data) {
                var retour = data.responseXML;
                if (requete == "add" || requete == "addspec") {
                    $(retour).find('item').each(function() {
                        var id = $(this).attr('id'),
                                prenom = $(this).attr('prenom'),
                                nom = $(this).attr('nom'),
                                date = $(this).attr('date'),
                                idsave = $(this).attr('idsave'),
                                content = $(this).text(),
                                box = $("#timeline_content");
                        if ($("#timeline_content div").length == 0)
                            box.html('');

                        var message = $("<div/>").addClass("cMessage cf")
                                .attr('id', 'timeline' + id)
                                .css('display', 'none');
                        message
                                .append(
                                $("<div/>")
                                .addClass("right small-6 text-right")
                                );

                        if (idsave != "no") {
                            message.children("div").eq(0)
                                    .append(
                                    $("<a/>")
                                    .addClass("tiny button")
                                    .attr('href', '?p=exehandler&exe=restore&idsave=' + idsave)
                                    .html("Restore backup")
                                    )
                                    .append(" ")
                                    .append(
                                    $("<a/>")
                                    .addClass("tiny button alert")
                                    .attr('href', '?p=exehandler&exe=removesvg&idsave=' + idsave)
                                    .html("Remove backup")
                                    );
                        }
                        message.children("div").eq(0)
                                .append(" ")
                                .append(
                                $("<a/>")
                                .addClass("deltime tiny button alert")
                                .attr("href", "#")
                                .attr("id", "deltime_" + id)
                                .text('Remove whole historical point')
                                .click(function() {
                            handleHistory(idproj, id, "", "remove");
                            return false;
                        })
                                )
                        message
                                .append(
                                $("<div/>")
                                .addClass("small-6 columns")
                                .append(
                                $("<strong/>").text(prenom + ' ' + nom)
                                )
                                .append(
                                $("<small/>")
                                .html('&nbsp; &nbsp;  ' + date)
                                )
                                .append($("<br>"))
                                .append(
                                $("<p/>")
                                .text(content)
                                )
                                );
                        box.prepend(message);
                        message.show('slow');
                    });
                    $("#cTextarea").val("");
                }
                else if (requete == "remove") {
                    $("#timeline" + id).remove();
                }
            }
        });
    };

    $.fn.handleDisplay = function(verif) {
        var other;
        if (verif == true) {
            if ($(this).find('input').attr("name") == "displaybootstrap")
                other = "displaybranchesl";
            if ($(this).find('input').attr("name") == "displaybranches")
                other = "displaybootstrapl";
        }
        if (!$(this).find('input').attr("checked")) {
            $(this).siblings('div').show();
            $("#" + other).siblings('div').hide();
            if ($("#" + other).find('input').attr("checked")) {
                $("#" + other).find('span').toggleClass('checked');
                $("#" + other).find('input').attr("checked", false);
            }
        }
        else {
            $(this).siblings('div').hide();
        }
    };

    $.fn.GetColor = function() {
        var color = $('#color_setter').attr("value");
        if (color !== "black") {
            $(this).next().attr("value", RGBToHex(color));
            if (RGBToHex($(this).css('backgroundColor')) == RGBToHex(color) && $(this).find('span').hasClass('checked'))
            {
                $(this).css('backgroundColor', '#ffffff');
            }
            else if (RGBToHex($(this).css('backgroundColor')) == RGBToHex(color) && !$(this).find('span').hasClass('checked'))
            {
                $(this).css('backgroundColor', '#ffffff');
            }
            else if (RGBToHex($(this).css('backgroundColor')) != RGBToHex(color) && !$(this).find('span').hasClass('checked'))
            {
                $(this).css('backgroundColor', color);
            }
            else if (RGBToHex($(this).css('backgroundColor')) != RGBToHex(color) && $(this).find('span').hasClass('checked'))
            {
                $(this).css('backgroundColor', color);

                if (false == $(this).find('input').is(':disabled')) {
                    $(this).find('input')[0].checked = (($(this).find('input')[0].checked) ? false : true);
                    $(this).find('span').toggleClass('checked');
                    $(this).find('input').trigger('change');
                }

            }
        } else {
            $(this).find('span').toggleClass('checked');
            $(this).find('input').trigger('change');
            $("#color_err").html("<span class='label alert'>You must first pick a color before (un)coloring an item.</span>");
        }
    }

    $.fn.SelectAll = function(select) {
        if (select) {
            $(this).parent().parent().find('.selectable input').each(function() {
                $(this).attr('checked', true);
                $(this).next("span").each(function() {
                    $(this).addClass("checked");
                })
            });
        }
        else {
            $(this).parent().parent().find('.selectable input').each(function() {
                $(this).attr('checked', false);
                $(this).next("span").each(function() {
                    $(this).removeClass("checked");
                })
            });
        }
    }

    switchCaptions = function(proj_id, id, name, type, jqobject, drop, invoke) {
        //|| !!$("#extmodif").length
        if (!$("#extmodif").is(':checked') || invoke == 1 || ($("#extmodif").is(':checked') && $("#inputhashand").attr('value') == 1)) {
            var side;
            var orientation = "none";

            // Side init
            if (($.type(drop) != "string" && drop.attr("id") == "sub_view_left") || drop == "left" || drop == "leftR") {
                side = "left";
                if (drop == "leftR")
                    orientation = "left";
            }
            else {
                side = "right";
                if (drop == "rightR")
                    orientation = "right";
            }

            // Orientation
            if (orientation == "none" && side == "right")
                orientation = "left";
            else if (orientation == "none" && side == "left")
                orientation = "right";

            if (orientation == "left" && side == "right" || orientation == "right" && side == "left")
                $("#" + side + "Reverse").val("0");
            else if (orientation == "right" && side == "right" || orientation == "left" && side == "left")
                $("#" + side + "Reverse").val("1");

            // reset thumbnails design
            $(".tint" + side).removeClass("tint" + side);

            // Reset manual tuning display
            $('#tuning_' + side).parent().find('div.toToggle').hide('slow')
                    .makeVisible(true);

            // Picture URL depending on the side
            if (orientation == "right") {
                var picturl = "./?p=getresult&id=" + proj_id + "&f=treepict_" + id + ".svg";
            }
            else {
                var picturl = "./?p=getresult&id=" + proj_id + "&f=treepict_" + id + "_R.svg";
            }

            // Change large picture
            var changeSize = true;
            if ($.browser.mozilla || $.browser.webkit)
                changeSize = false;
            $('#' + side + '_tree_svg').svg('get').clear()
                    .load(picturl, {
                changeSize: changeSize,
                onLoad: enableSVG
            });
            $("#" + side + "_url").text(picturl);
            //$('#'+side+'_tree_svg').svg('destroy');
            //$('#'+side+'_tree_svg').svg({loadURL: picturl, onLoad: enableSVG}); 
            //$("#"+side+"_tree_svg", body).svg('destroy');

            // Update picture label
            $("#" + side + "_tree_label")
                    .attr("href", picturl);

            // Update name on the top of the picture
            $("#nametree" + side).text(name);

            // Change picture download URL
            $("#" + side + "_tree_dl").attr("href", picturl + '&d=' + id);

            // Update the tree number in the manual tuning form
            $("#" + side + "_nb").attr("value", id);

            // Update the link for downloading sources
            $("#" + side + "_tree_sources_dl").attr("href", picturl + '&d=' + id + '&tar=' + id);

            // Update AJAX sources load form
            $("#" + side).attr("name", id);

            // Update MAST form
            $("#mast_" + side).attr("value", id);

            // Update type in manual tuning form
            $("#" + side + "_supertree").attr("value", type);

            // Update visual style of the thumbnail
            jqobject.parent().addClass('tint' + side);

            // Update the source data for the color import (colorize)
            $("#" + "link_load_" + side).click(function() {
                getToColorize(id, proj_id)
            });

            // Refresh params
            $("#" + side + "_params").text("translate(10, 10) scale(1.5)");

            // AJAX request to update the project and right/left captions
            if ($("#inputhashand").val() == '1') {
                var idl = $("#left").attr('name');
                var idr = $("#right").attr('name');
                $("#hashand").checkAsks(proj_id, "switchcaptions", idr, idl);
            }
        }
    }

    checkRegexp = function(o, regexp, n) {
        if (!(regexp.test(o.val()))) {
            o.addClass("error");
            if (o.siblings().length < 2) {
                o.parent().append("<small class='error'>" + n + '</small>');
            }
            //updateTips( n );
            return false;
        } else {
            return true;
        }
    }

    updateTips = function(t) {
        var tips = $(".validateTips");
        tips.text(t)
                .show();
        setTimeout(function() {
            tips.fadeOut("slow");
        }, 1500);
    }

    $.fn.editable = function(iclass, name, id, type) {
        $(this).click(function() {
            $(this).unbind('click');
            var val = $(this).text();
            $(this).text('')
            if (type == "text") {
                $(this).append(
                        $('<input/>')
                        .addClass(iclass)
                        .attr('name', name)
                        .attr('id', id)
                        .attr('value', val)
                        );
            }
            else if (type == "textarea") {
                $(this).append(
                        $('<textarea/>')
                        .addClass(iclass)
                        .attr('name', name)
                        .attr('id', id)
                        .val(val)
                        );
            }
        })
    }

    $.fn.answerInvite = function(a) {
        var id = $(this).attr('title');
        var dataString = 'answer=' + a;
        $.ajax({
            type: "POST",
            url: "ajax/ajax_answerinvite.php?id=" + id,
            data: dataString,
            complete: function(data) {
                var retour = data.responseXML;
                var title, desc, date;
                $(retour).find('item').each(function() {
                    if ($(this).attr('id') == 'title')
                        title = $(this).text();
                    if ($(this).attr('id') == 'desc')
                        desc = $(this).text();
                    if ($(this).attr('id') == 'date')
                        date = $(this).text();
                });
                $("#iproject" + id).remove();

                if (a == "true") {
                    $(".noP").remove();
                    var t = $("<div class='panel'><div class='row'><div class='small-4 columns'><h5><a href='index.php?id=" + id + "&p=project' class='projectlink'>" + title + "</a></h5></div><div class='small-2 columns'><small>" + date + "</small></div><div class='small-4 columns'><ul class='button-group even-3'><li><a href='index.php?id=" + id + "&p=project' class='tiny success secondary button'>Access</a></li></ul></div></div><p>" + desc + "</p></div>");
                    t.hide()
                            .appendTo("#projectlist")
                            .fadeIn();
                    //.append($("<hr>"));
                }

                if ($("#invitedin").children().length == 2) {
                    $("#invitedin").remove();
                }
            }
        });
    }

    $.fn.loadDeleteDialogBtn = function() {
        var id = $(this).attr('id');
        $("#delpconf").dialog({
            autoOpen: false,
            height: 200,
            width: 350,
            modal: true,
            buttons: {
                "I confirm": function() {
                    $.ajax({
                        type: "GET",
                        url: "ajax/ajax_delproject.php?id=" + id,
                        dataType: "xml",
                        complete: function() {
                            $("#delpconf").text("The project has successfully been deleted.");
                            setTimeout("window.location='?p=projects'", 2000);
                        }
                    });
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            }
        });
    }

    $.fn.delUser = function(id) {
        var idm = $(this).attr('href');
        var dataString = 'id=' + idm + "&del=true";
        $.ajax({
            type: "POST",
            url: "ajax/ajax_adduser.php?id=" + id,
            data: dataString,
            complete: function() {
                $("#del" + idm).parent().remove();
            }
        });
    }

    clientHandler = function(id, idu) {
        return setInterval(function() {
            $("#askhand").checkAsks(id, "check");
            $("#hashand").checkAsks(id, "checkhand", idu);
            if ($("#extmodif").is(':checked'))
                $("#hashand").checkAsks(id, "gettrees");
        }, 5000);
    }

    refreshSortable = function() {
        if ($("#extmodif").attr('checked') == 'checked' && $("#inputhashand").attr('value') == '0') {
            $("#divgene").sortable("option", "disabled", true);
            $("#divsuper").sortable("option", "disabled", true);
        }
        else if ($("#extmodif").attr('checked') == 'checked' && $("#inputhashand").attr('value') == '1') {
            $("#divgene").sortable("option", "disabled", false);
            $("#divsuper").sortable("option", "disabled", false);
        }
        if ($("#extmodif").is(':checked') == false) {
            $("#divgene").sortable("option", "disabled", false);
            $("#divsuper").sortable("option", "disabled", false);
        }
    }

    $.fn.checkAsks = function(id, a, idu, idl) {
        var bloc = $(this);
        var dataString = 'a=' + a + "&idu=" + idu + "&idl=" + idl;
        if (a != "gettrees" || ($("#inputhashand").attr('value') == '0' && $("#extmodif").is(':checked'))) {
            $.ajax({
                type: "POST",
                url: "ajax/ajax_asksHandler.php?id=" + id,
                data: dataString,
                complete: function(data) {
                    var retour = data.responseXML;
                    $(retour).find('return').each(function() {
                        if ($(this).text() == "err")
                            setTimeout("window.location='?p=projects'", 500);
                    })
                    if (a == 'check') {
                        var asking = $(retour).find('asker').first();
                        var iduser = asking.attr('id');
                        var struser = asking.text();

                        $("#grantInfo").html(struser + " is requesting the control over the edition.")
                        if (struser !== "" && ($("#inputhashand").val() === "1"/* || $("#gethand").text() != ""*/)) {
                            $("#grantControl").foundation('reveal', 'open');
                            $("#acceptGrant").click(function() {
                                bloc.checkAsks(id, "validate", iduser);
                                $("#grantControl").foundation('reveal', 'close');
                            });
                            $("#denyGrant, #denyClose").click(function() {
                                bloc.checkAsks(id, "refuse", iduser);
                                $("#grantControl").foundation('reveal', 'close');
                            });
                        } else {
                            $("#grantControl").hide();
                        }
                    }
                    if (a == 'checkhand') {
                        var hand, idh;
                        $(retour).find('hand').each(function() {
                            hand = $(this).text();
                            idh = $(this).attr('id');
                        });
                        if (idh == idu) {
                            $("#gethand").parent().css("display", "none");
                            $("#askforhand").parent().css("display", "none");
                            $("#inputhashand").val('1');
                            $(".icon-disablable").css("color", "#2ba6cb");
                            $(".img-disablable").each(function() {
                                var name = $(this).attr("src");
                                name = name.replace("_grey.png", ".png");
                                $(this).attr("src", name);
                            });
                        }
                        else {
                            $(".icon-disablable").css("color", "grey");
                            $(".img-disablable").each(function() {
                                var name = $(this).attr("src");
                                if (!name.match(/_grey/)) {
                                    name = name.replace(".png", "_grey.png");
                                    $(this).attr("src", name);
                                }
                            });
                            $("#gethand").parent().css("display", "inline");
                            $("#askforhand").parent().css("display", "inline");
                            $(".disablable").attr('disabled', 'disabled');
                            $(".disablable").each(function() {
                                $(this).parent().parent().find(".errors").first().text('You do not have the control over the project. You cannot currently perform this action.').css("display", "block");
                            });
                            $(".disablable2").attr('disabled', 'disabled');
                            $("#inputhashand").val('0');
                        }
                        if (hand !== "undefined") {
                            $("#handman").remove();
                            $("#uid_" + idh).append("<img src='img/hand.png' alt='Is in control' align='absmiddle' id='handman' title='Is in control'/>");
                            refreshSortable();
                            if (idh == idu) {
                                var refresh = false;
                                $(".disablable").each(function() {
                                    if ($(this).is(":disabled")) {
                                        refresh = true;
                                    }
                                });
                                if (refresh == true) {
                                    location.reload();
                                }
                            }
                        }
                    }
                    if (a == 'refreshusers') {
                        var count = 0;
                        $(retour).find('user').each(function() {
                            count++;
                            var id = $(this).attr("id");
                            var main = $(this).attr("main");
                            var lead = $(this).attr("lead");
                            var me = $(this).attr("me");
                            var NP = $(this).text();

                            $("#users").append(
                                    $("<li/>")
                                    .attr("id", "uid_" + id)
                                    );
                            if (main == "1")
                                $("#uid_" + id).append("<img src='img/hand.png' alt='Is in control' align='absmiddle' id='handman' title='Is in control'/>");
                            if (me == "1")
                                $("#uid_" + id).append($("<b/>").css("color", "#000000").text(NP));
                            else
                                $("#uid_" + id).text(NP)
                            if (lead == "1")
                                $("#uid_" + id).append("<img id='leader' title=\"Project's administrator\" src='img/crown-icon.png' alt='Head of the project' align='absmiddle'/>");
                        });
                        $("#whoisonline > a > span").text(count);
                    }
                    if (a == 'gettrees') {
                        var timestamp = $(retour).find('timestamp').text();
                        var maing = $(retour).find('captions').attr('left');
                        var maind = $(retour).find('captions').attr('right');
                        var nomg = $(retour).find('captions').attr('nleft');
                        var nomd = $(retour).find('captions').attr('nright');
                        var typeg = $(retour).find('captions').attr('tleft');
                        var typed = $(retour).find('captions').attr('tright');
                        if ($("#clientlastupdate").val() !== timestamp) {
                            switchCaptions(id, maing, nomg, typeg, $("#" + maing), $("#sub_view_left"), 1);
                            switchCaptions(id, maind, nomd, typed, $("#" + maind), $("#sub_view_right"), 1);
                            $("#clientlastupdate").val(timestamp);
                            $("#divgene").html('');
                            $("#divsuper").html('');
                            $(retour).find('tree').each(function() {
                                var vignette = $("<div/>")
                                        .addClass("vignette")
                                        .attr("id", 'v_' + $(this).attr('id'))
                                        .append("<span class='handle'>" + $(this).attr("nom") + "</span>")
                                        .append("<hr>")
                                        .append(
                                        $("<img/>")
                                        .addClass("th caption")
                                        .attr('id', $(this).attr('id'))
                                        .attr('title', "Drag to a workbench to enlarge")
                                        .attr('alt', $(this).attr('type'))
                                        .attr('src', '?p=getresult&id=' + id + '&f=' + $(this).text())
                                        )
                                        .append("<span class='move' title='Drag to reorder trees'></span>")
                                if ($(this).attr("type") == "1")
                                    $("#divgene").append(vignette);
                                else if ($(this).attr("type") == "2")
                                    $("#divsuper").append(vignette);
                            });
                            $("#" + maing).parent().addClass("tintleft");
                            $("#" + maind).parent().addClass("tintright");
                            $("#divgene img, #divsuper img").not("#" + maing + ", #" + maind).addClass("default");
                        }
                    }
                }
            });
        }
    }

    $.fn.tagName = function() {
        if ($.type(this.get(0)) == "undefined")
            return "undefined";
        return this.get(0).tagName.toLowerCase();
    }

    jQuery.expr[':'].regex = function(elem, index, match) {
        var matchParams = match[3].split(','),
                validLabels = /^(data|css):/,
                attr = {
            method: matchParams[0].match(validLabels) ?
                    matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels, '')
        },
        regexFlags = 'ig',
                regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g, ''), regexFlags);
        return regex.test(jQuery(elem)[attr.method](attr.property));
    }

    $.fn.toggleIcon = function() {
        if ($(this).hasClass("foundicon-plus")) {
            $(this).removeClass("foundicon-plus");
            $(this).addClass("foundicon-minus");
        } else {
            $(this).removeClass("foundicon-minus");
            $(this).addClass("foundicon-plus");
        }
    }

})(jQuery)
