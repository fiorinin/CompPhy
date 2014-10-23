<div class="container">
    <?php
    $id = 0;
    if (isset($_GET['id']))
        $id = intval($_GET['id']);

    $projet = Projet::getP($db, $id);
    $result_dir = $res = $projet->getRepertoire();

    if ($projet->getPublic() == 1 || (isset($membre) && $projet->canAccess($membre->getId()))) {

        $projet->update();

        //if(isset($membre) && $projet->canAccess($membre->getId()))
        //    $projet->verifHand($membre);
        // remove invalid characters
        $result_dir = preg_replace('/[^\w\-]/', '', $result_dir);

        if ('' != $result_dir) {
            // "compile" directory
            $result_dir = EXECPATH . $result_dir . "/";
        }

        $left_tree = null;
        $right_tree = null;

        // check if directory exists
        if (('' != $result_dir)
                && file_exists($result_dir)
                && is_dir($result_dir)
                && is_readable($result_dir)) {

            $sortedList = Arbre::getSortedList($db, $projet->getId());
            $g_max = Arbre::CountTrees($db, $projet->getId(), 1);
            $s_max = Arbre::CountTrees($db, $projet->getId(), 2);
            $taxaList = Arbre::getTaxaList($db, $projet->getId());

            $maing = $projet->getMaing();
            $maind = $projet->getMaind();
            if (($maing || $maind) && ($maing != 0 && $maind != 0)) {
                $left_tree = Arbre::getA($db, $maing);
                $right_tree = Arbre::getA($db, $maind);
            } else {
                if ($g_max >= 1) {
                    foreach ($sortedList['genetrees'] as $key => $value) {
                        if (null == $left_tree)
                            $left_tree = $right_tree = $value;
                        else if ($left_tree == $right_tree && $s_max >= 1)
                            $right_tree = current($sortedList['supertrees']);
                        else if ($left_tree == $right_tree)
                            $right_tree = $value;
                    }
                }
                elseif ($s_max >= 1) {
                    foreach ($sortedList['supertrees'] as $key => $value) {
                        if (null == $left_tree)
                            $left_tree = $right_tree = $value;
                        else if ($left_tree == $right_tree)
                            $right_tree = $value;
                    }
                }
                if ($right_tree)
                    $projet->setMaind($right_tree->getId());
                if ($left_tree)
                    $projet->setMaing($left_tree->getId());
                if ($right_tree || $left_tree)
                    $projet->update();
            }

            $mbrnb = 0;
            $usersonline = array();
            foreach ($projet->getUsers() as $key => $user) {
                if ($user->isOnline()) {
                    $mbrnb++;
                    array_push($usersonline, $user);
                }
            }
            if ($projet->getPublic() == 1 && (!isset($membre) || !$projet->canAccess($membre->getId()))) {
                echo "<div data-alert='' class='alert-box info' style='font-size:16px;line-height:20px'>You are visiting this project in <b>guest</b> mode: you are welcome to have a look, but most tools are only available for <b>members</b> of the project to ensure the project stays in an effective state. To fully test CompPhy, we encourage you to register and create your own project.<a href='#' class='close'>×</a></div>";
            }
            if (isset($membre) && $projet->canAccess($membre->getId())) {
                /* echo "<div id='refreshUsers'><a href='#'><img src='img/reload.png' alt='reload' align='absmiddle' /></a></div>";
                  echo "<ul class='salon'>";
                  echo "<li class='item1'><a href='#' class='headlinkusers'>Who is online ? <span>$mbrnb</span></a>";
                  echo "<ul>";
                  foreach ($usersonline as $user) {
                  echo "<li class='subitem1' id='uid_" . $user->getId() . "'><table width='300'><tr><td width='40'>";
                  if ($projet->getMain() == $user->getId())
                  echo "<img src='img/hand.png' alt='Is in control' align='absmiddle' id='handman' title='Is in control'/>";
                  echo "</td><td><span>" . ($user->getId() == $membre->getId() ? "<b style='color:#000;'>" . $user->getPrenom() . " " . $user->getNom() . "</b>" : $user->getPrenom() . " " . $user->getNom()) . "</span></td><td width='40'>";
                  if ($projet->getLead()->getId() == $user->getId())
                  echo "<img id='leader' title=\"Project's administrator\" src='img/crown-icon.png' alt='Head of the project' align='absmiddle'/>";
                  echo "</td></tr></table></li>";
                  }
                  echo "</ul></li>";
                  echo "</ul>"; */
                ?>
                <div class="small-2 small-offset-10 overlay" style="margin-top:70px;">
                    <?
                    echo "<div  id='syncB'>";
                    echo "<input type='hidden' id='inputhashand' value='" . ($membre && $projet->getMain() == $membre->getId() ? "1" : "0") . "'/>";
                    echo "<input type='hidden' id='clientlastupdate' value='" . $projet->getLast_update() . "'/>";
                    echo "<form class='custom' style='margin-bottom:1px;'>";
                    if ($membre && $projet->canAccess($membre->getId()))
                        echo "<label for='extmodif' style='display:inline;font-size:12px;'><input type='checkbox' id='extmodif' checked class='custom' style='display:none;'><span id='extmodifS' class='custom checkbox checked'></span> Synchronize view with partners</label>";
                    if ($membre && $projet->isLead($membre->getId()))
                        echo "<span style='display:none;font-size:12px;'> | <a href='#' id='gethand'>Take the control</a></span>";
                    elseif ($membre && $projet->canAccess($membre->getId()))
                        echo "<span style='display:none;font-size:12px;'> | <a href='#' id='askforhand'>Request the control</a></span>";
                    echo "<div id='hashand' style='display:inline;font-size:12px;'></div>";
                    if ($membre && $projet->canAccess($membre->getId()))
                        echo "<div id='askhand' style='display:none;font-size:12px;' title='Control request'></div>";
                    echo "</form>";
                    echo "</div>";
                    ?>
                    <div class="section-container salon">
                        <section class="section">
                            <p class="title" id="whoisonline"><a href="#"><span><?= $mbrnb;?></span> <?= ($mbrnb>1?"people are":"person is");?> online <i class="icon-chevron-down"></i></a></p>
                            <div class="content">
                                <ul id="users" class="side-nav text-center">
                                    <?
                                    $i = 0;
                                    $total = sizeof($usersonline);
                                    foreach ($usersonline as $user) {
                                        echo "<li id='uid_";
                                        echo $user->getId();
                                        echo "'>";
                                        if ($projet->getMain() == $user->getId())
                                            echo "<img src='img/hand.png' alt='Is in control' id='handman' title='Is in control'>";
                                        echo ($user->getId() == $membre->getId() ? "<b>" . $user->getPrenom() . " " . $user->getNom() . "</b>" : $user->getPrenom() . " " . $user->getNom());
                                        if ($projet->getLead()->getId() == $user->getId())
                                            echo "<img id='leader' title=\"Project's administrator\" src='img/crown-icon.png' alt='Head of the project'>";
                                        echo "</li>";
                                        if($i < $total-1)
                                            echo "<li class=\"divider\"></li>";
                                        $i++;
                                    }
                                    ?>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
                <?
            }
            ?>
            <script>
                $("#whoisonline").click(function() {
                    $(this).parent().children("div").toggle("slow");
                });
            </script>
            <script type="text/javascript">
                $(function() {
                                                                                            
                    $("#refreshUsers > a").click(function(e) {
                        e.preventDefault();
                        $("#hashand").checkAsks(<?= $projet->getId(); ?>, "refreshusers");
                    })

                });
            </script>
            <?
            echo "<h4 class='subheader' id='titleProject'>";
            if ($projet->getPublic() == 1)
            //echo "<img class='privacyimg' src='img/unlocked.png' alt='Public project' title='This is a public project. Communicate the URL displayed in the web browser will allow others to access this project.'/>";
                echo "<i class='foundicon-unlock foundicon'></i>";
            else
            //echo "<img class='privacyimg' src='img/locked.png' alt='Private project' title='This is a private project. No one but the invited members can see it.'/>";
                echo "<i class='foundicon-lock foundicon'></i>";
            echo " Project: " . $projet->getTitre() . "</h4><hr>";
            
            include (HEREPATH . 'includes/project_menu.php');

            // output control panel and views...
            ?>
            <script>
                $(document).ready(function() {    
                    $("#help").click(function() {
                        $(".joyride-tip-guide").remove();
                        $(document).foundation('joyride', 'start');
                    });
                    $(".iconswap").click(function() {
                        if($("#inputhashand").val() === "1") {
                            $(this).toggleClass('activateda');
                            var side;
                            if($(this).hasClass("activateda")) {
                                if($(this).parent().parent().parent().parent().parent().attr("id") == "sub_view_left")
                                    side = "left";
                                else {
                                    side = "right";
                                }
                            }
                            else { side = "0"}
                            $("#swapEN").val(side); 
                            return false;
                        } else { return false; }
                    });
                    
                    var timerId = 0;
                    var dragging = false;
                    timerId = clientHandler(<?= $projet->getId(); ?><?= $membre ? ',' . $membre->getId() : ''; ?>);

                    $("#divgene").sortable({
                        handle: ".handle",
                        cursor: 'move',
                        axis: 'x',
                        start: function() {
                            clearInterval(timerId);
                        },
                        stop: function() {
                            timerId = clientHandler(<?= $projet->getId(); ?><?= $membre ? ',' . $membre->getId() : ''; ?>);
                        },
                        update : function () {
                            var order = $('#divgene').sortable('serialize');
                            $("#hashand").checkAsks(<?= $projet->getId(); ?>+"&"+order, "sort");
                        }
                    });
                    $("#divsuper").sortable({
                        handle: ".handle",
                        cursor: 'move',
                        axis: 'x',
                        start: function() {
                            clearInterval(timerId);
                        },
                        stop: function() {
                            timerId = clientHandler(<?= $projet->getId(); ?><?= $membre ? ',' . $membre->getId() : ''; ?>);
                        },
                        update : function () {
                            var order = $('#divsuper').sortable('serialize');
                            $("#hashand").checkAsks(<?= $projet->getId(); ?>+"&"+order, "sort");
                        }
                    });
                                                                                                
                    refreshSortable();
                    $("#extmodif").parent().click(function() {
                        $("#hashand").checkAsks(<?= $projet->getId(); ?>, "gettrees", 1);
                        refreshSortable();
                    })
                    
                    $(".caption").live('mouseover',function(){
                        $(this).draggable({ 
                            appendTo: 'body', 
                            scroll: false,
                            distance: 50,
                            helper: 'clone',
                            cursor: 'move',
                            start: function() {
                                clearInterval(timerId);
                                dragging = true;
                            },
                            stop: function() {
                                timerId = clientHandler(<?= $projet->getId(); ?><?= $membre ? ',' . $membre->getId() : ''; ?>);
                            }
                        });
                    });
                    
                    $('.caption').live('mouseup', function(event) {
                        if(!dragging) {
                            var $caption = $(this);
                            switch (event.which) {
                                case 1:
                                    if($caption.tagName() !== "undefined")
                                        switchCaptions(<?= $projet->getId() ?>, $caption.attr('id'), $caption.parent().find("span:not(.move)").text(), $caption.attr("alt"), $caption, "left", 0);
                                    break;
                                case 3:
                                    if($caption.tagName() !== "undefined")
                                        switchCaptions(<?= $projet->getId() ?>, $caption.attr('id'), $caption.parent().find("span:not(.move)").text(), $caption.attr("alt"), $caption, "right", 0);
                                    break;
                            }
                        }
                    });
                    
                    $(".caption").live("contextmenu",function(e){
                        return false;
                    }); 
                    
                    $(".subviewdrop").live('mouseover',function(){
                        $(this).droppable({
                            drop: function( event, ui ) {
                                var objet_drop = $(ui.draggable);
                                if(objet_drop.hasClass('caption'))
                                    switchCaptions(<?= $projet->getId() ?>, objet_drop.attr('id'), objet_drop.parent().find("span:not(.move)").text(), objet_drop.attr("alt"), objet_drop, $(this), 0);
                                dragging = false;
                            }
                        });
                    });
                    $(".leftarrow").click(function(){
                        // Find caption
                        var side;
                        if($(this).hasClass("leftT")) side ="left";
                        else side = "right";
                        var idl = $("#"+side+"_nb").val();
                        var $caption = $("#"+idl).parent().prev().find("img");
                        // Switch caption
                        if($caption.tagName() != "undefined")
                            switchCaptions(<?= $projet->getId() ?>, $caption.attr('id'), $caption.parent().find("span:not(.move)").text(), $caption.attr("alt"), $caption, side, 0);
                        return false;
                    });
                    $(".rightarrow").click(function(){
                        // Find caption
                        var side;
                        if($(this).hasClass("leftT")) side ="left";
                        else side = "right";
                        var idl = $("#"+side+"_nb").val();
                        var $caption = $("#"+idl).parent().next().find("img");
                        // Switch caption
                        if($caption.tagName() != "undefined")
                            switchCaptions(<?= $projet->getId() ?>, $caption.attr('id'), $caption.parent().find("span:not(.move)").text(), $caption.attr("alt"), $caption, side, 0);
                        return false;
                    });
                            
                    $("#askforhand").click(function() {
                        $("#askhand").checkAsks(<?= $projet->getId(); ?>, "askhand"<?= $membre ? ',' . $membre->getId() : ''; ?>);
                        return false;
                    })
                    $("#gethand").click(function() {
                        $("#askhand").checkAsks(<?= $projet->getId(); ?>, "gethand"<?= $membre ? ',' . $membre->getId() : ''; ?>);
                        return false;
                    })
                    changeTitle("CompPhy - <? echo $projet->getTitre(); ?> project");
                    $("#divgenel").click(function(){ 
                        $("#divgene").toggle('drop');
                        $(this).toggleIcon();
                        return false;
                    })
                    $("#divsuperl").click(function(){ 
                        $("#divsuper").toggle('drop'); 
                        $(this).toggleIcon();
                        return false;
                    })
                    $("#divworkbenchl").click(function(){ 
                        $("#workbench").addClass("magictime");
                        $("#workbench").toggleClass(function() {
                            if ($(this).hasClass("swashOut")) {
                                return 'swashIn';
                            } else {
                                return 'swashOut';
                            }
                        });
                        if($("#workbench").hasClass("swashIn")) {
                            $("#workbench").show();
                        } else {
                            $("#workbench").delay(1000).hide('fast');
                        }
                        //$("#workbench").delay(1500).toggle('slow'); 
                        $(this).toggleIcon();
                        return false;
                    })
                    $("#compute_dist").click(function() { computeDist($("#left_nb"), $("#right_nb")); return false;});
                    $("#compute_mast, #compute_swapauto").click(function() { 
                        if($("#inputhashand").val() === "1") {
                            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
                            if ($(this).attr("id") == "compute_mast")
                                $("#mast").submit();
                            else
                                window.location = "?p=exehandler&id=<?= $projet->getId() ?>&exe=autoswap";
                            return false;
                        } else { return false; }
                    });
                    $(".pickSize").slider({
                        step: 1,
                        min: 3,
                        max: 15,
                        value: 8,
                        slide: function(event, ui) {
                            //get the id of this slider
                            var id = $(this).attr("id");
                            //select the input box that has the same id as the slider within it and set it's value to the current slider value. 
                            $("input[id*=" + id + "]").val(ui.value);
                        }
                    });
                    $("#right_tree_svg").svg();
                    $("#left_tree_svg").svg();
        <?
        if ($right_tree && $left_tree) {
            ?>
                        var changeSize = true;
                        if($.browser.mozilla || $.browser.webkit)
                            changeSize = false;
                        var svg1 = $('#right_tree_svg').svg('get'); 
                        svg1.load("<? echo ROOT . 'compphy/?p=getresult&id=' . $projet->getId() . '&f=' . $right_tree->getImageR(); ?>", {changeSize: changeSize, onLoad: enableSVG}); 
                        var svg2 = $('#left_tree_svg').svg('get'); 
                        svg2.load("<? echo ROOT . 'compphy/?p=getresult&id=' . $projet->getId() . '&f=' . $left_tree->getImage(); ?>", {changeSize: changeSize, onLoad: enableSVG});
        <? } ?>
                                                                                                    
                $("a.symmetry").click(function() {
                    // Find caption
                    var side;
                    var sideR;
                    if($(this).hasClass("leftT")) side ="left";
                    else side = "right";
                    var idl = $("#"+side+"_nb").val();
                    if($("#"+side+"Reverse").val() == "0") sideR = side+"R";
                    else sideR = side;
                    var $caption = $("#"+idl).parent().find("img");

                    switchCaptions(<?= $projet->getId() ?>, $caption.attr('id'), $caption.parent().find("span:not(.move)").text(), $caption.attr("alt"), $caption, sideR, 0);
                    return false;
                });
                
                $( "#left_tree_svg" ).resizable({
                    handles: 'se, sw, e, w',
                    helper: "ui-resizable-helper",
                    stop: function(e, ui) {
                        e.stopPropagation(); 
                        var svg = $("#left_tree_svg").svg('get');
                        var transform = $("g", svg.root()).attr("transform");
                        $("#left_params").text(transform);
                        svg.clear().load($("#left_url").text(), {changeSize: false, onLoad: enableSVGNoSizeL});
                    }
                });
                $( "#right_tree_svg" ).resizable({
                    handles: 'se, sw, e, w',
                    helper: "ui-resizable-helper",
                    stop: function(e, ui) {
                        e.stopPropagation(); 
                        var svg = $("#right_tree_svg").svg('get');
                        var transform = $("g", svg.root()).attr("transform");
                        $("#right_params").text(transform);
                        $(this).svg('get').clear().load($("#right_url").text(), {changeSize: false, onLoad: enableSVGNoSizeR});
                    }
                });
                $('.controls').bind('mousedown', function() {
                    var self = $(this);
                    var action = "";
                    if(self.hasClass("downc"))
                        action = "down";
                    if(self.hasClass("upc"))
                        action = "up";
                    if(self.hasClass("rightc"))
                        action = "right";
                    if(self.hasClass("leftc"))
                        action = "left";
                    if(self.hasClass("icon-zoom-in"))
                        action = "in";
                    if(self.hasClass("icon-zoom-out"))
                        action = "out";
                    var svg = self.parent().parent().parent().svg('get');
                    controlSVG(action,svg);  
                    this.iid = setInterval(function() {
                        controlSVG(action,svg);  
                    },400);
                }).bind('mouseup', function(){
                    this.iid && clearInterval(this.iid);
                });
                $(".icon-camera").click(function() {
                    if($("#inputhashand").val() === "1") {
                        $(this).animate({color:"#5da423"},{duration:0,queue:true}).animate({color:"#2ba6cb"},{duration:2000,queue:true});

                        var side = "right";
                        if($(this).hasClass("leftTreeTag")) {
                            side = "left";
                        }
                        var svg = $(this).parent().parent().parent().svg('get');
                        var width = $("#"+side+"_tree_svg").css("width");
                        var height = $("#"+side+"_tree_svg").css("height");
                        saveSVG($(".tint"+side).find("img:first").attr("id"),svg,$("#"+side+"Reverse").val(),side,width,height);
                    } else { return false; }
                });
                $(".icon-bullseye").click(function() {
                    var svg = $(this).parent().parent().parent().svg('get');
                    var params = $("g", svg.root()).attr("transform");
                    $(this).parent().parent().parent().focusOn(svg);
                    if($(this).hasClass("leftTreeTag")) {
                        svg.clear().load($("#left_url").text(), {changeSize: false, onLoad: enableSVGNoSizeL});
                        $("#left_params").text(params);
                    } else {
                        svg.clear().load($("#right_url").text(), {changeSize: false, onLoad: enableSVGNoSizeR});
                        $("#right_params").text(params);
                    }
                });
            });
            </script>
            <div class="small-12"><h6 class="subheader">Tree collection 1
                    <?php
                    //echo "          <iframe id=\"control_panel_frame\" scrolling=\"auto\" name=\"control_panel\" longdesc=\"Physic view control panel\" src=\"?p=controlpanel&t=gene&id=" . $projet->getId() . "\" frameborder=\"1\">Your browser does not support internal frames!</iframe>\n";
                    if ($g_max >= 1)
                        echo " <a href='#' id='divgenel' class='divl foundicon foundicon-minus'></a>";
                    else
                        echo " <a href='#' id='divgenel' class='divl foundicon foundicon-plus'></a>";
                    echo ( $g_max >= 1) ? " <a href='?p=getresult&id=" . $projet->getId() . "&f=" . $left_tree->getImage() . "&d=1&tar=collection_1&col=1' data-tooltip title='Download the whole collection' class='foundicon foundicon-inbox'></a></h6>" : "" . "</h6>";
                    echo "<div id='divgene' style='" . ($g_max < 1 ? "display:none;" : "") . "'>";
                    if ($g_max >= 1) {
                        $init = 0;
                        foreach ($sortedList['genetrees'] as $key => $value) {
                            $img_class = "";

                            if ($maing || $maind) {
                                if ($value->getId() == $left_tree->getId())
                                    $img_class .= " tintleft";
                                if ($value->getId() == $right_tree->getId())
                                    $img_class .= " tintright";
                            }
                            else {
                                if ($init == 0) {
                                    $img_class .= " tintleft";
                                } elseif ($init == 1 && $s_max <= 0) {
                                    $img_class .= " tintright";
                                }
                            }

                            echo "<div class=\"vignette " . $img_class . "\" id='v_" . $value->getId() . "'>";
                            echo "<span class='handle'>" . $value->getNom() . "</span><hr>";
                            echo "<img class=\"th caption\" id=\"" . $value->getId() . "\" title=\"Drag to a workbench to enlarge\" alt=\"" . $value->getType() . "\" src=\"?p=getresult&id=" . $projet->getId() . "&amp;f=" . $value->getMiniature() . "\" />";
                            echo "</div>";

                            $init++;
                        }
                    }
                    $txt = "";
                    $txt .= ( $g_max >= 1) ? "" : "<p>No tree in collection 2.</p>";
                    echo $txt;
                    echo "</div>";
                    ?>
            </div><hr>
            <div class="small-12"><h6 class="subheader">Tree collection 2
                    <?php
                    //echo "          <iframe id=\"control_panel_frame_s\" name=\"control_panel_s\" longdesc=\"Physic view control panel\" src=\"?p=controlpanel&t=uper&id=" . $projet->getId() . "\" frameborder=\"1\">Your browser does not support internal frames!</iframe>\n";
                    if ($s_max >= 1)
                        echo " <a href='#' id='divsuperl' class='divl foundicon foundicon-minus'></a>";
                    else
                        echo " <a href='#' id='divsuperl' class='divl foundicon foundicon-plus'></a>";
                    echo ( $s_max >= 1) ? " <a href='?p=getresult&id=" . $projet->getId() . "&f=" . $left_tree->getImage() . "&d=1&tar=collection_2&col=1' data-tooltip title='Download the whole collection' class='foundicon foundicon-inbox'></a></h6>" : "" . "</h6>";
                    echo "<div id='divsuper' style='" . ($s_max < 1 ? "display:none;" : "") . "'>";
                    if ($s_max >= 1) {
                        $init = 0;
                        foreach ($sortedList['supertrees'] as $key => $value) {
                            $img_class = "";

                            if ($maing || $maind) {
                                if ($value->getId() == $left_tree->getId())
                                    $img_class .= " tintleft";
                                if ($value->getId() == $right_tree->getId())
                                    $img_class .= " tintright";
                            }
                            else {
                                if ($init == 0 && $g_max >= 1) {
                                    $img_class .= " tintright";
                                } elseif ($init == 0 && $g_max <= 0) {
                                    $img_class .= " tintleft";
                                } elseif ($init == 1 && $g_max <= 0) {
                                    $img_class .= " tintright";
                                }
                            }
                            echo "<div class=\"vignette " . $img_class . "\" id='v_" . $value->getId() . "'>";
                            echo "<span class='handle'>" . $value->getNom() . "</span><hr>";
                            echo "<img class=\"th caption\" id=\"" . $value->getId() . "\" title=\"Drag to a workbench to enlarge\" alt=\"" . $value->getType() . "\" src=\"?p=getresult&id=" . $projet->getId() . "&amp;f=" . $value->getMiniature() . "\" />";
                            echo "</div>";

                            $init++;
                        }
                    }
                    $txt = "";
                    $txt .= ( $s_max >= 1) ? "" : "<p>No tree in collection 2.</p>";
                    echo $txt;
                    echo "</div>";
                    ?>
            </div><hr>
            <h6 class="subheader">Workbenches <a href='#' id='divworkbenchl' class='divl foundicon foundicon-minus'></a></h6>
            <div id="workbench">
                <div id="left_params" class="hide"></div>
                <div id="right_params" class="hide"></div>
                <div class="small-12 cf">
                    <div class="small-5 columns">
                        <?
                        if (null == $left_tree)
                            echo "<div class='label secondary'>No loaded tree.</div><br><br>";
                        else {
                            ?>
                            <div id="left_url"><? echo ROOT . 'compphy/?p=getresult&id=' . $projet->getId() . '&f=' . $left_tree->getImage(); ?></div>
                            <div class="small-1 columns">
                                <a class="leftarrow leftT icon-arrow-left arrowStyle" href="#"></a>
                            </div>
                            <div class="wblabel text-center small-10 columns" id="swapleft">
                                <!--<a id="left_tree_label" href="?p=getresult&id=<? //= $projet->getId()   ?>&amp;f=<? //= $left_tree->getImage()   ?>" target="sub_view_left" title="Will open the tree in a new tab, then zoom in with  CTRL (or CMD) and +, or zoom out with CTRL and -">
                                    <img class="noborder" alt="Zoom on source tree" src="img/search32.png" align="absmiddle"/>
                                </a>-->
                                <span class="nametree" id="nametreeleft"><?= $left_tree->getNom(); ?></span>
                            </div>
                            <div class="small-1 columns">
                                <a class="rightarrow leftT icon-arrow-right arrowStyle" href="#"></a>
                            </div>
                        <? } ?>
                    </div>
                    <div class="small-5 small-offset-2 columns">
                        <?
                        if (null == $right_tree)
                            echo "<div class='label secondary'>No loaded tree.</div><br><br>";
                        else {
                            ?>
                            <div id="right_url"><? echo ROOT . 'compphy/?p=getresult&id=' . $projet->getId() . '&f=' . $right_tree->getImageR(); ?></div>
                            <div class="small-1 columns">
                                <a class="leftarrow rightT icon-arrow-left arrowStyle" href="#"></a>
                            </div>
                            <div class="wblabel text-center small-10 columns" id="swapright">
                                <!--<a id="right_tree_label" href="?p=getresult&id=<? //= $projet->getId()   ?>&amp;f=<? //= $right_tree->getImage()   ?>" target="sub_view_right" title="Will open the tree in a new tab, then zoom in with  CTRL (or CMD) and +, or zoom out with CTRL and -">
                                    <img class="noborder" alt="Zoom on source tree" src="img/search32.png" align="absmiddle"/>
                                </a>-->
                                <span class="nametree" id="nametreeright"><?= $right_tree->getNom(); ?></span>
                            </div>
                            <div class="small-1 columns">
                                <a class="rightarrow rightT icon-arrow-right arrowStyle" href="#"></a>
                            </div>
                        <? } ?>
                    </div>
                    
                        <?
                        if (null != $left_tree || null != $right_tree) {
                            ?>
                    <div class="wrapperva small-12">
                        <?
                        if (null != $left_tree) {
                            ?>
                        <div id="sub_view_left" class="small-5 va subviewdrop">
                            <div class="small-12 text-center">
                                <input type="hidden" id="leftReverse" style="display:none;" value ="0">

                                <div id="left_tree_svg" style="width:100%;height:5000px;">
                                    <div class="toolbarimg">
                                        <div class="toolbarsection">
                                            <a data-tooltip href="#" title="Horizontal symmetry" class="icon-32 symmetry leftT has-tip tip-top noradius">
                                                <img src="img/tourner.png" alt="Horizontal symmetry" border="0" width="25"/>
                                            </a>
                                        </div>
                                        <? //UPD if ($membre && $projet->canAccess($membre->getId())) { ?>
                                        <div class="toolbarsection">
                                            <a href="#" class="icon-32 has-tip tip-top noradius iconswap" data-tooltip title="Swap chosen branches by selecting two taxa names">
                                                <img alt="Swap leaves" src="img/swapdual.png" border="0"width="25" class="img-disablable"/>
                                            </a>
                                        </div>
                                        <? //UPD } ?>
                                        <div class="toolbarsection">
                                            <a class="icon-zoom-in controls"></a>
                                        </div>
                                        <div class="toolbarsection">
                                            <a class="icon-zoom-out controls"></a>
                                        </div>
                                        <div class="toolbarsection arrows">
                                            <a class="icon-chevron-down downc controls"></a>
                                            <a class="icon-chevron-up upc controls"></a>
                                            <a class="icon-chevron-right rightc controls"></a>
                                            <a class="icon-chevron-left leftc controls"></a>
                                        </div>
                                        <div class="toolbarsection">
                                            <a class="icon-bullseye leftTreeTag" data-tooltip title='Adapts the grahical view to the current height of the tree.'></a>
                                        </div>
                                        <? //UPD if ($membre && $projet->canAccess($membre->getId())) { ?>
                                        <div class="toolbarsection">
                                            <a class="icon-camera leftTreeTag noTransition icon-disablable" data-tooltip title='Saves the picture with this configuration. This update will be visible by all users accessing the project.'></a>
                                        </div>
                                        <? //UPD } ?>
                                    </div>
                                </div>
                                
                                <!--<embed src="<? //echo ROOT . 'compphy/?p=getresult&id=' . $projet->getId() . '&f=' . $left_tree->getImage();       ?>" type="image/svg+xml" />-->
                            </div>
                        </div>
                        <? } ?>
                        <div class="small-1 va text-center">
                            <? //UPD if (isset($membre) && $projet->canAccess($membre->getId())) { ?>
                                <div class="boxappear first" id="minitools">
                                    <a href="#" id="compute_dist" data-tooltip class="icon-32 has-tip tip-top noradius" title="Symmetrical (RF) distance between the two trees">
                                        <img src="img/milestone.png" alt="RF distance" border="0" width="32"/>
                                    </a>
                                </div>
                                <div class="boxappear">
                                    <a href="#" id="compute_mast" data-tooltip class="icon-32 has-tip tip-top noradius" title="Maximum Agreement SubTree Consensus">
                                        <img src="img/mast.png" alt="MAST" border="0" width="32" class="img-disablable" />
                                    </a>
                                </div>
                                <div class="boxappear">
                                    <a href="#" id="compute_swapauto" data-tooltip class="icon-32 has-tip tip-top noradius" title="Auto swap">
                                        <img src="img/swapauto.png" alt="Swap" border="0" width="32" class="img-disablable"/>
                                    </a>
                                </div>
                            <? //UPD } ?>
                        </div>
                        <?
                        if (null != $right_tree) {
                            ?>
                        <div id="sub_view_right" class="small-5 va  subviewdrop">
                            <div class="small-12 text-center">
                                <input type="hidden" id="rightReverse" style="display:none;" value ="0">

                                <div id="right_tree_svg" style="width:100%;height:5000px;">
                                    <div class="toolbarimg">
                                        <div class="toolbarsection">
                                            <a data-tooltip href="#" title="Horizontal symmetry" class="icon-32 symmetry rightT has-tip tip-top noradius" data-tooltip>
                                                <img src="img/tourner.png" alt="Horizontal symmetry" border="0" width="25"/>
                                            </a>
                                        </div>
                                        <? //UPD if ($membre && $projet->canAccess($membre->getId())) { ?>
                                        <div class="toolbarsection">
                                            <a href="#" data-tooltip class="icon-32 has-tip tip-top noradius iconswap" title="Swap chosen branches by selecting two taxa names">
                                                <img alt="Swap leaves" src="img/swapdual.png" width="25" class="img-disablable"/>
                                            </a>
                                        </div>
                                        <? //UPD } ?>
                                        <div class="toolbarsection">
                                            <a class="icon-zoom-in controls"></a>
                                        </div>
                                        <div class="toolbarsection">
                                            <a class="icon-zoom-out controls"></a>
                                        </div>
                                        <div class="toolbarsection arrows">
                                            <a class="icon-chevron-down downc controls"></a>
                                            <a class="icon-chevron-up upc controls"></a>
                                            <a class="icon-chevron-right rightc controls"></a>
                                            <a class="icon-chevron-left leftc controls"></a>
                                        </div>
                                        <div class="toolbarsection">
                                            <a class="icon-bullseye rightTreeTag" data-tooltip title="Adapts the grahical view to the current height of the tree."></a>
                                        </div>
                                         <? //UPD if ($membre && $projet->canAccess($membre->getId())) { ?>
                                        <div class="toolbarsection">
                                            <a class="icon-camera rightTreeTag noTransition icon-disablable" data-tooltip title='Saves the picture with this configuration. This update will be visible by all users accessing the project.'></a>
                                        </div>
                                        <? //UPD } ?>
                                    </div>
                                </div>
                                <!--<embed src="<? //echo ROOT . 'compphy/?p=getresult&id=' . $projet->getId() . '&f=' . $right_tree->getImageR();       ?>" type="image/svg+xml" />-->
                            </div>
                        </div>
                        <? } ?>
                    </div>
                    <? }
                    if ($projet->getPublic() == 1 || $membre) {
                        if ($right_tree && $left_tree) {
                            ?>
                            
                            <div class="small-5 text-center columns">
                                <p>
                                    <a href="?p=getresult&id=<?= $projet->getId() ?>&f=<?= $left_tree->getImage() ?>&d=<?= $left_tree->getId() ?>" id="left_tree_dl">Download image</a>&nbsp; &nbsp; &nbsp; &nbsp; 
                                    <a href="?p=getresult&id=<?= $projet->getId() ?>&f=<?= $left_tree->getImage() ?>&d=<?= $left_tree->getId() ?>&tar=<?= $left_tree->getId() ?>" id="left_tree_sources_dl">Download files</a>
                                </p>
                            </div>
                            <div class="small-5 small-offset-2 text-center columns">
                                <p>
                                    <a href="?p=getresult&id=<?= $projet->getId() ?>&f=<?= $right_tree->getImage() ?>&d=<?= $right_tree->getId() ?>" id="right_tree_dl">Download image</a>&nbsp; &nbsp; &nbsp; &nbsp; 
                                    <a href="?p=getresult&id=<?= $projet->getId() ?>&f=<?= $right_tree->getImage() ?>&d=<?= $right_tree->getId() ?>&tar=<?= $right_tree->getId() ?>" id="right_tree_sources_dl">Download files</a>
                                </p>
                            </div>
                            <?
                        }
                    }
                    ?>
                </div>
            </div>
            <?
            //UPD if ($membre && $projet->canAccess($membre->getId()))
                include(TOOLS);
        }
        else {
            $projet->delete();
            Navigate::redirectMessage("projects", "Due to a long inactivity, this project does not exist anymore.", 2);
        }
    }
    else
        Navigate::redirectMessage("login", "You do not have access to this project. Maybe your account has been disconnected.", 2);
    ?>
</div>
<div class="reveal-modal small text-center" id="grantControl"><div id="grantInfo"></div><br><br><div><a href="#" id="acceptGrant" class="button success">Accept</a> <a href="#" id="denyGrant" class="button alert">Decline</a></div><a id="denyClose" class="close-reveal-modal">&#215;</a></div>
<div class="reveal-modal small text-center" id="informationMessage"><div id="infocontent"></div><a id="informationClose" class="close-reveal-modal">&#215;</a></div>

