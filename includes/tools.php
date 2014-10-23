<script>
    window.onload = function() {
        document.onselectstart = function() {
            return false;
        }
    }
    $(document).ready(function() {
        $("#divtoolsl").click(function(){ 
            $("#divtools").toggle('drop', {direction: 'up'}); 
            $(this).toggleIcon();
            return false;
        })
        $("#blue").click(function() { $(this).updateColor('#26a2f1') });
        $("#green").click(function() { $(this).updateColor('#60cd3a') });
        $("#yellow").click(function() { $(this).updateColor('#f1db26') });
        $("#orange").click(function() { $(this).updateColor('#f1ac26') });
        $("#red").click(function() { $(this).updateColor('#cd1919') });
        $("#brown").click(function() { $(this).updateColor('#7c3c19') });
        $("#purple").click(function() { $(this).updateColor('#c036e7') });
        $("#grey").click(function() { $(this).updateColor('#b0b0b0') });
        $('.toggling').click(function() { 
            $(this).parent().find('.toToggle').slideToggle('slow');
        });
        $("#link_load_left").click(function() { getToColorize('#left_nb'); });
        $("#link_load_right").click(function() { getToColorize('#right_nb'); });
        $("#link_unload").click(function() { getToColorize('none'); });
        $("#tuning_left").click(function() { getData('left'); });
        $("#tuning_right").click(function() { getData('right'); });
        //$('.tipsyhelp').tipsy({gravity : "s"});
        //$("#idTabs ul").idTabs();
        $("#restrict_form, #reroot_form, #taxacolor_form, #subtreecolor_form, #super_form, #display_form, #formname, #taxaname_form, #delete_form").find('label.selectable').shiftSelectable();
        $('#cSubmit').click(function() { 
            handleHistory('<? echo $projet->getId(); ?>',0,$('#cTextarea').val(),'add'); 
            return false; 
        });
        $('.deltime').click(function() { 
            handleHistory('<? echo $projet->getId(); ?>',$(this).splitId(),"",'remove'); 
            return false; 
        });
        //TODO
        //$("#formname").validationEngine();
        //$("#formsubtree").validationEngine();
        
        //$('.boxappear').tipsy({gravity : "n", fade : true});
        //$("#displayscale").click(function(){$(this).handleDisplay();});
        $("#displayscalel").click(function(){$(this).handleDisplay(true);});
        $("#displaybootstrapl").click(function(){$(this).handleDisplay(true);});
        $("#displaybranchesl").click(function(){$(this).handleDisplay(true);});
        $("#changeSizel").click(function(){$(this).handleDisplay();});
        $("#changeInterleafl").click(function(){$(this).handleDisplay();});
        $(".colorTaxon").click(function() { 
            $(this).GetColor(); 
        });
        $('.selectora').click(function() { $(this).SelectAll(true); return false; });
        $('.deselectora').click(function() { $(this).SelectAll(false); return false; });
        $('#linkleaf').click(function() { $('#colorleaf').toggle('slow'); return false; });
        $('#linksubtree').click(function() { $('#colorsubtree').toggle('slow'); return false; });
        $(".pickInterleaf").slider({
            step: 1,
            min: 5,
            max: 100,
            value: 20,
            slide: function(event, ui) {
                //get the id of this slider
                var id = $(this).attr("id");
                //select the input box that has the same id as the slider within it and set it's value to the current slider value. 
                $("input[id*=" + id + "]").val(ui.value);
            }
        });
        $(".errors").css("display","none");
    });
</script>
<div <? //UPD if(!$membre || !$projet->canAccess($membre->getId())) echo "style='display:none;'"; ?>>
    <hr>
    <h6 class="subheader">Tools <a href='#' id='divtoolsl' class='divl foundicon foundicon-minus'></a></h6>
    <div id="divtools">
<?
if ($right_tree && $left_tree) {
    ?>
        <div class="cf wrapperva small-12">
            <div class="small-5 va">
                <fieldset>
                    <legend style="cursor:pointer;" class ="toggling" id="tuning_left">Manual tuning of the picture</legend>
                    <div id="loader_left" style="display:none;margin:auto;width:32px;height:32px;"><img src="img/loader.gif" alt="Loader"/></div>
                    <div style="display:none;" id="left_form" class="toToggle">
                        <p>This box allows you to manually modify the scripts of the tree showed above.</p>
                        <form action="?id=<?= $projet->getId() ?>&p=new" method="POST" id="left" name="<?= $left_tree->getId(); ?>">
                            <label>Tree</label>
                            <textarea id="left_tree" name="left_tree" rows="5" cols="55" ></textarea>
                            <label>Script</label>
                            <textarea id="left_script" name="left_script" rows="5" cols="55" ></textarea>
                            <label>Annotations</label>
                            <textarea id="left_annotation" name="left_annotation" rows="5" cols="55" ></textarea>
                            <input type="hidden" style="display:none;" value="1" name="left_supertree" id="left_supertree"/>
                            <!-- c'etait ici -->
                            <input type="hidden" style="display:none;" value="<?= $left_tree->getId() ?>" name="treenb" id="left_nb"/>
                            <div class="text-center">
                                <button type="submit" class="small button disablable">Apply changes</button>
                            </div>
                        </form>
                    </div>
                </fieldset>
            </div>
            <div class="small-2 va"></div>
            <div class="small-5 va">
                <fieldset>
                    <legend style="cursor:pointer;" class ="toggling" id="tuning_right">Manual tuning of the picture</legend>
                    <div id="loader_right" style="display:none;margin:auto;width:32px;height:32px;"><img src="img/loader.gif" alt="Loader"/></div>
                    <div style="display:none;" id="right_form" class="toToggle">
                        <p>This box allows you to manually modify the scripts of the tree showed above.</p>
                        <form action="?id=<?= $projet->getId() ?>&p=new" method="POST" id="right" name="<?= $right_tree->getId(); ?>">
                            <label>Tree</label>
                            <textarea id="right_tree" name="right_tree" rows="5" cols="55" ></textarea>
                            <label>Script</label>
                            <textarea id="right_script" name="right_script" rows="5" cols="55" ></textarea>
                            <label>Annotations</label>
                            <textarea id="right_annotation" name="right_annotation" rows="5" cols="55" ></textarea>
                            <input type="hidden" style="display:none;" value="1" name="right_supertree" id="right_supertree"/>
                            <input type="hidden" style="display:none;" value="<?= $right_tree->getId() ?>" name="treenb" id="right_nb"/>
                            <div class="text-center">

                                <button type="submit" class="small button disablable">Apply changes</button>
                            </div>
                        </form>
                    </div>
                </fieldset>
            </div>
        </div>
        <? } ?>
    <div class="section-container auto" id="maxTools" data-section>
        <?
if ($right_tree && $left_tree) {
    ?>
        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Timeline of the project"><img src="img/horloge.png" alt="Timeline" border="0" width="48"/></a></p>
            <div class="content cf" data-section-content>
                <div class="small-6 columns">
                    <h5 class="subheader">Timeline</h5><hr>
                    <p>This tool allows you to keep records of the important steps performed during the analysis of your data. You can register a comment each time an important step is achieved and store a backup of the project at this precise time.</p>

                    <form class="custom">
                        <label>Comment describing a new historical point</label>
                        <textarea class="cTextarea" id="cTextarea"></textarea>
                        <label for="saveproject" style="display:inline;"><input type="checkbox" name="save" id="saveproject" style="display:none;"/><span class="custom checkbox"></span> Backup the project in its current state</label>
                    </form>
                    <button class="tiny button disablable2" id="cSubmit">Add a historical point</button>
                    <hr>
                    <div id="timeline_content">
                        <?
                        $historique = Historique::getList($db, $projet->getId());
                        if (count($historique) == 0) {
                            echo "<p>There is no message in the timeline</p>";
                        } else {
                            foreach ($historique as $key => $value) {
                                $author = Utilisateur::getM($db, $value->getUser_id());
                                ?>
                                <div class="cMessage cf" id="timeline<?= $value->getId(); ?>">
                                    <div class="right small-6 text-right">
                                        <? if ($value->getSave()) { ?>
                                            <a href="?p=exehandler&exe=restore&idsave=<?= $value->getSave(); ?>" class="tiny button cDL disablable2"><span class='icon uparrow'></span>Restore backup</a>
                                            <a href="?p=exehandler&exe=removesvg&idsave=<?= $value->getSave(); ?>" class="tiny button alert cDL disablable2"><span class='icon cross'></span>Remove backup</a>
                                        <? } ?>
                                        <a href="#" id="deltime_<?= $value->getId(); ?>" class="tiny button alert deltime disablable2">Remove whole historical point</a>
                                    </div>
                                    <div class="small-6 columns">
                                        <strong><? echo $author->getPrenom() . ' ' . $author->getNom(); ?> </strong><small>&nbsp; &nbsp; <? echo date('d-m-Y H:i', strtotime($value->getDate())); ?></small><br><p><? echo $value->getDescription(); ?></p>
                                    </div>
                                </div>
                                <?
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="small-6 columns">
                    <h5 class="subheader">To do list</h5><hr>
                    <p>Here is a to do list to note the future tasks for you and your partners.</p>

                    <ul class="todoList">
                        <?
                        $todolist = ToDo::getList($db, $projet->getId());
                        foreach ($todolist as $todo) {
                            echo $todo;
                        }
                        ?>
                    </ul>
                    <button id="addButton" class="tiny button disablable2" style="margin:6px;">Add a to do item</button>
                    <div id="dialog-confirm" title="Delete to do Item?">Are you sure you want to delete this to do item?</div>

                    <ul class="todoListOld">
                        <?
                        $todolist = ToDo::getListOld($db, $projet->getId());
                        foreach ($todolist as $todo) {
                            echo $todo->toStringOld();
                        }
                        ?>
                    </ul>
                    <script>
                        $(document).ready(function() {
                            $(".todoList").sortable({
                                axis : 'y',
                                update : function(){	
                                    var arr = $(".todoList").sortable('toArray');
                                    arr = $.map(arr,function(val,key){
                                        return val.replace('todo-','');
                                    });
                                    $.get('ajax/ajax_todolist.php',{'idproj':'<?= $projet->getId(); ?>',action:'rearrange',positions:arr});
                                }
                            });

                            // The Add New ToDo button:
                            $('#addButton').click(function(e){

                                $.get("ajax/ajax_todolist.php",{'idproj':'<?= $projet->getId(); ?>','action':'new','text':'New to do item. Doubleclick to edit.'},function(msg){
                                    $(msg).hide().appendTo('.todoList').fadeIn();
                                }, 'html');

                                e.preventDefault();
                            });
                        });
                    </script>
                    <script type="text/javascript" src="js/jquery.todolist.js"></script>
                </div>
            </div>

            <?
            if ($right_tree && $left_tree) {
                ?>
                <div style="display:none;">
                    <form id="mast" action="?p=exehandler&id=<?= $projet->getId() ?>&exe=mast" method="POST">
                        <input type="hidden" name="trees[]" id="mast_right" value="<?= $right_tree->getId(); ?>" />
                        <input type="hidden" name="trees[]" id="mast_left" value="<?= $left_tree->getId(); ?>" />
                    </form>
                </div>
            <? } ?>
            <div style="display:none;">
                <form id="swap" action="?p=exehandler&id=<?= $projet->getId() ?>&exe=swap" method="POST">
                    <input type="hidden" name="taxa1" id="taxa1" />
                    <input type="hidden" name="taxa2" id="taxa2" />
                    <input type="hidden" id="swapEN" value="0" />
                    <input type="hidden" id="Dswap" value="0" />
                    <input type="hidden" name="treeswap" id="treeswap" value="0" />
                </form>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Restrict trees to common taxa"><img src="img/filtre.png" alt="Restrict trees" border="0"  width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Restrict trees</h5><hr>
                    <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=restrict" method="POST" class="custom" id="restrict_form">
                        <p>This tool allows you to restrict several trees to the taxa they have in common. You can select two or more trees but the whole set still must have a non-empty common leaf set and each selected source tree has to possess at least two such leaves. The restriction of each selected tree will appear as an additional tree in the same collection.</p>

                        <div id="restrict_err"></div>
                        <?
                        Utils::displayCollections($sortedList);
                        ?>

                        <input type="hidden" style="display:none;" name="exe" value="1"/>
                        <button type="submit" class="small button disablable">Apply the tree restriction</button>
                    </form>
                    <span class="errors label alert"></span>
                </div>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Reroot trees"><img src="img/tree.png" alt="Reroot trees" border="0" width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Reroot trees</h5><hr>
                    <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=reroot" method="POST" class="custom" id="reroot_form">
                        <p>This tool allows you to reroot specified trees. You have to select the trees you want to reroot, then write a list of desired outgroups.</p>

                        <div id="reroot_err"></div>
                        <?
                        Utils::displayCollections($sortedList);
                        ?>

                        <label>Outgroup specification <a href="outgroup.txt" target="_blank" class="icon-16 has-tip tip-top noradius" data-tooltip title="Click here for an example file"><img align="absmiddle" class="tipsyhelp" alt="Help" src="img/help.png" border="0" width="16"/></a></label>
                        <textarea name="outgroups" cols="50" rows="3"></textarea><br>

                        <label for="replace"><input type="checkbox" name="replace" id="replace" value="1" style="display:none;"/><span class="checkbox custom"></span> Replace the trees with new rooted trees</label>

                        <input type="hidden" style="display:none;" name="exe" value="1"/>
                        <button type="submit" class="small button disablable">Reroot the trees</button>
                    </form>
                    <span class="errors label alert"></span>
                </div>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Color your trees"><img src="img/treeleaves.png" alt="Color" border="0" width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Color trees</h5><hr>
                    <!--<a href="#" id="linkleaf">Color tree leaves</a><hr>-->
                    <div id="colorleaf">
                        <p>This tool allows you to color the trees. First select a color below, then chose the taxa you want to highlight. Then, select the tree(s) to apply the tool on.</p>


                        <div id="color_err"></div>
                        <p><a id="link_load_left" style="cursor:pointer;">Get left tree colors</a> | <a id="link_load_right" style="cursor:pointer;">Get right tree colors</a> | <a id="link_unload" style="cursor:pointer;">Deselect all colors</a></p>

                        <?
                        if (count($taxaList >= 1)) {
                            ?>

                            <p>Select a color :</p> 
                            <input type="hidden" style="display:none;" value="black" id="color_setter"/>
                            <div class="cf">
                                <div class="colorbox columns small-1"><img src="img/blue.jpg" id="blue" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/green.jpg" id="green" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/yellow.jpg" id="yellow" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/orange.jpg" id="orange" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/red.jpg" id="red" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/brown.jpg" id="brown" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/purple.jpg" id="purple" class="th"/></div>
                                <div class="colorbox columns small-1"><img src="img/grey.jpg" id="grey" class="th"/></div>
                                <div class="small-4 columns"></div>
                            </div><br>

                            <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=colorize" method="POST" class="custom" id="taxacolor_form">

                                <p>Select taxa to color:</p>

                                <?
                                if (sizeof($taxaList) > 0) {
                                    $quarternb = intval(sizeof($taxaList) / 4);
                                    $count = 0;
                                    echo "<div class='small-3 columns'>";
                                    foreach ($taxaList as $key => $value) {
                                        if ($count % $quarternb == 0 && $count != 0) {
                                            echo "</div><div class='small-3 columns'>";
                                        }
                                        echo "<label for='color_" . $key . "' id='div_" . $key . "' class='colorTaxon' style='background-color:#ffffff;'><input id='color_" . $key . "' type='checkbox' name='taxa[]' value='" . $key . "' style='display:none;' class='colorLabel'/><span class='custom checkbox customSpanCheckbox' id='colorspan_$key'></span> " . $key . "</label>
                          <input type=\"hidden\" style=\"display:none;\" value=\"black\" id=\"c_" . $key . "\" name=\"c_" . $key . "\" class=\"hiddenColor\"/>";
                                        $count++;
                                    }
                                echo "</div><div class='cf'></div><p></p>";
                                }
                                ?>
                                <label for="colorBackground"><input type="checkbox" id="colorBackground" name="colorBackground" value="1" style="display:none;"><span class="custom checkbox"></span> Color subtree background</label>
                                <p>Be careful, this option hides taxon names of colored subtrees.</p>

                                <?
                                Utils::displayCollections($sortedList);
                                ?>
                                <input type="hidden" style="display:none;" name="exe" value="1"/>
                                <button type="submit" class="small button disablable">Color tree(s)</button>
                            </form>
                            <span class="errors label alert"></span>
                            <?
                        }
                        ?>
                    </div>
<? /*
                    <a href="#" id="linksubtree">Color a subtree</a><hr>
                    <div id="colorsubtree" style="display:none;">
                        <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=colorize&k=subtree" method="POST" id="formsubtree" class="custom" id="subtreecolor_form">
                            <div class="small-6 columns">
                                <label>Choose a name for the parameter to highlight (for example, "origin"):</label>
                                <input type="text" name="opname" id="opname" class="validate[length[1,50]]"/>
                            </div>
                            <div class="small-6 columns">
                                <label>Choose a value for this parameter (for example, Africa) :</label>
                                <input type="text" name="tname" id="tname" class="validate[length[1,50]]"/>
                            </div>
                            <p>Select taxa you want to color:</p>

                            <?
                            if (sizeof($taxaList) > 0) {
                                $quarternb = intval(sizeof($taxaList) / 4);
                                $count = 0;
                                echo "<div class='small-3 columns'>";
                                foreach ($taxaList as $key => $value) {
                                    if ($count % $quarternb == 0 && $count != 0) {
                                        echo "</div><div class='small-3 columns'>";
                                    }
                                    echo "<label for='subtree_" . $key . "'><input type='checkbox' name='taxa[]' value='" . $key . "' id='subtree_" . $key . "' style='display:none;'/><span class='custom checkbox'></span> " . $key . "</label>
                          <input type=\"hidden\" style=\"display:none;\" value=\"black\" name=\"c_" . $key . "\"/>";
                                    $count++;
                                }
                            echo "</div><div class='cf'></div><p></p>";
                            }
                            ?>

                            <?
                            Utils::displayCollections($sortedList);
                            ?>
                            <input type="hidden" style="display:none;" name="exe" value="1"/>
                            <button type="submit" class="small button disablable wait"><span class="iconr rightarrow"></span>Color tree(s)</button>
                        </form>
                    </div>
                </div>
*/
?>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Compute a supertree"><img src="img/superman.png" alt="Compute an additional supertree" border="0" width="48"/></a></p>
            <div class="content cf" data-section-content>
                <div class="small-12 columns">
                    <p>You can compute a supertree by selecting the trees you want to aggregate. Supertrees will be displayed in collection 2.</p>
                </div>
                <div class="small-6 columns">
                    <h5 class="subheader">Compute an additionnal supertree by using PhySIC_IST</h5><hr>
                    <form method="post" action="?p=exehandler&id=<?= $projet->getId() ?>&exe=physicist" class="custom" id="super_form">

                        <div id="super_err"></div>
                        <?
                        if (isset($sortedList['genetrees'])) {
                            Utils::displayCollections($sortedList, false);
                            ?>

                        <div class="small-4">
                            <label for="bootstrap">
                                        <a href="http://www.atgc-montpellier.fr/physic_ist/#bootstrap" target="_blank">Support threshold for source clade selection</a>
                                        <input type="text" name="bootstrap" id="bootstrap" value="0" />
                            </label>
                            <label for="threshold">
                                        <a href="http://www.atgc-montpellier.fr/physic_ist/#correction" target="_blank">Correction threshold used by STC </a>
                                        <input type="text" name="correction" id="threshold" value="0.9"/>
                            </label>
                        </div>
                            <label for="newtrees" data-tooltip title="Each source tree can be examined in the light of the others and 'corrected' (signaled by a red circle) for anomalies in regards of the topological message delivered by the other trees (the amount of correction is tuned by the STC threshold, see PhySIC_IST help)"><input type="checkbox" id="newtrees" name="newtrees" value="1" style="display:none;"><span class="custom checkbox"></span> Create corrected trees</label>
                            <br><br>
                            <button type="submit" class="small button disablable">Compute the supertree</button><br>
                            
<? } else echo "You have no tree uploaded in collection 1."; ?>
                    </form>
                    <span class="errors label alert"></span>
                </div>
                <div class="small-6 columns">
                    <h5 class="subheader">Compute an additionnal supertree by using MRP</h5><hr>
                    <form method="post" action="?p=exehandler&id=<?= $projet->getId() ?>&exe=mrp" class="custom" id="super_form_MRP">
                        <div id="super_err_MRP"></div>
                        <?
                        if (isset($sortedList['genetrees'])) {
                            Utils::displayCollections($sortedList, false);
                            ?>
                        <label for="modeSuper">Consensus of most parsimonious trees</label>
                        <select id="modeSuper" name="mode" class="medium">
                          <option value="greedy">Greedy</option>
                          <option value="majority">Majority rule</option>
                          <option value="strict">Strict</option>
                        </select>

                        <button type="submit" class="small button disablable">Compute the supertree</button><br>
                        <? } else echo "You have no tree uploaded in collection 1."; ?>
                    </form>
                </div>
                <div class="small-12 columns">
                    <span class="alert label">Beware! Computing a supertree can take quite some time depending on the number of taxa and trees. During this time, your CompPhy interface will be unavailable!</span>
                </div>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Display options"><img src="img/ecran.png" alt="Trees' display options" border="0" width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Display options</h5><hr>
                    <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=display" method="POST" class="custom" id="display_form">
                        <div id="display_err"></div>
                        <div class="dBlocks2">
                            <label for="displayscale" id="displayscalel"><input type="checkbox" value="1" name="displayscale" id="displayscale" style="display:none;"/><span class="custom checkbox displayOptions"></span> Change display of evolutionary scale (relative to branch lengths)</label>
                            <div class="disabledDisplay cf">
                                <br>
                                <div class="small-2 columns">
                                    <label for="addScaleYes"><input type="radio" name="addScale" value="1" id="addScaleYes" style="display:none;"/><span class="custom radio"></span> Display</label>
                                </div>
                                <div class="small-2 columns">
                                    <label for="addScaleNo"><input type="radio" name="addScale" value="0" id="addScaleNo" style="display:none;"/><span class="custom radio"></span> Hide</label>
                                </div>
                                <div class="small-8"></div>
                            </div>
                        </div>
                        <br>
                        <div class="dBlocks">
                            <label for="displaybootstrap" id="displaybootstrapl"><input type="checkbox" value="1" name="displaybootstrap" id="displaybootstrap"  style="display:none;"/><span class="custom checkbox displayOptions"></span> Change display of support values</label>
                            <div class="disabledDisplay cf">
                                <br>
                                <div class="small-2 columns">
                                    <label for="addBootstrapYes"><input type="radio" name="addBootstrap" value="1" id="addBootstrapYes" style="display:none;" /><span class="custom radio"></span> Display</label>
                                </div>
                                <div class="small-2 columns">
                                    <label for="addBootstrapNo"><input type="radio" name="addBootstrap" value="0" id="addBootstrapNo" style="display:none;" /><span class="custom radio"></span> Hide</label>
                                </div>
                                <div class="small-8"></div>
                            </div>
                        </div>
                        <br>
                        <div class="dBlocks">
                            <label for="displaybranches" id="displaybranchesl"><input type="checkbox" value="1" name="displaybranches" id="displaybranches" style="display:none;" /><span class="custom checkbox displayOptions"></span> Change display of branch lengths</label>
                            <div class="disabledDisplay cf">
                                <br>
                                <div class="small-2 columns">
                                    <label for="addBranchesYes"><input type="radio" name="addBranches" value="1" id="addBranchesYes" style="display:none;" /><span class="custom radio"></span> Display</label>
                                </div>
                                <div class="small-2 columns">
                                    <label for="addBranchesNo"><input type="radio" name="addBranches" value="0" id="addBranchesNo" style="display:none;" /><span class="custom radio"></span> Hide</label>
                                </div>
                                <div class="small-8"></div>
                            </div>
                        </div>
                        <br>
                        <div class="dBlocks">
                            <label for="changeSize" id="changeSizel"><input type="checkbox" value="1" name="changeSizeT" id="changeSize" style="display:none;" /><span class="custom checkbox displayOptions"></span> Modify font size?</label>
                            <div class="disabledDisplay cf">
                                <div class="pickSize" id="pickSize">       
                                </div>
                                <input type="hidden" id="pickSize-txt" name="changeSize" value="8"/>
                            </div>
                        </div>
                        <br>
                        <div class="dBlocks">
                            <label for="changeInterleaf" id="changeInterleafl"><input type="checkbox" value="1" name="changeInterleafT" id="changeInterleaf" style="display:none;" /><span class="custom checkbox displayOptions"></span> Modify interleaf space?</label>
                            <div class="disabledDisplay cf">
                                <div class="pickInterleaf" id="pickInterleaf">       
                                </div>
                                <input type="hidden" id="pickInterleaf-txt"  name="changeInterleaf" value="20"/>
                            </div>
                        </div>
                        <br><br>
                        <?
                        Utils::displayCollections($sortedList);
                        ?>

                        <input type="hidden" style="display:none;" name="exe" value="1"/>
                        <button type="submit" class="small button disablable">Apply display options</button>
                    </form>
                    <span class="errors label alert"></span>
                </div>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Name your trees"><img src="img/editer.png" alt="Change tree names" border="0"  width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Change tree names</h5><hr>
                    <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=treenames" method="POST" id="formname" class="custom">

                        <div id="name_err"></div>
                        <?
                        if (isset($sortedList['genetrees'])) {
                            echo "<p>Collection 1 list:</p>";
                            $quarternb = intval(sizeof($sortedList['genetrees']) / 4);
                            $count = 0;
                            echo "<div class='small-3 columns'>";
                            foreach ($sortedList['genetrees'] as $key => $value) {
                                if ($quarternb != 0 && $count % $quarternb == 0 && $count != 0) {
                                    echo "</div><div class='small-3 columns'>";
                                }
                                echo "<div class='rename'><label>" . $value->getNom() . "</label><input type='text' class='treenames' id='treename_" . $value->getId() . "' size='16' name='trees_" . $value->getId() . "' value='" . $value->getNom() . "'/></div>";
                                $count++;
                            }
                            echo "</div><div class='cf'></div><p></p>";
                        }
                        if (isset($sortedList['supertrees'])) {
                            echo "<p>Collection 2 list:</p>";
                            $quarternb = intval(sizeof($sortedList['supertrees']) / 4);
                            $count = 0;
                            echo "<div class='small-3 columns'>";
                            foreach ($sortedList['supertrees'] as $key => $value) {
                                if ($quarternb != 0 && $count % $quarternb == 0 && $count != 0) {
                                    echo "</div><div class='small-3 columns'>";
                                }
                                echo "<div class='rename'><label>" . $value->getNom() . "</label><input type='text' class='treenames' id='treename_" . $value->getId() . "' size='16' name='trees_" . $value->getId() . "' value='" . $value->getNom() . "'/></div>";
                                $count++;
                            }
                            echo "</div><div class='cf'></div><p></p>";
                        }
                        ?>

                        <input type="hidden" style="display:none;" name="exe" value="1"/>
                        <button type="submit" class="small button disablable">Apply new names</button>
                    </form>
                    <span class="errors label alert"></span>

                    If you want to load your own name file, you can upload the file below. <a href="names.txt" target="_blank" data-tooltip class="icon-16 has-tip tip-top noradius" title="Click here for a sample file" ><img align="absmiddle" alt="Help" src="img/help.png" border="0" width="16"/></a><br>
                    <form method="POST" action="?p=exehandler&id=<?= $projet->getId() ?>&exe=treenamesf" enctype="multipart/form-data">
                        <input type="file" name="names"/>
                        <button type="submit" class="small button disablable">Load</button>
                    </form>
                    <span class="errors label alert"></span>
                </div>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Change taxa names"><img src="img/arbre.png" alt="Modify taxa names" border="0" width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Change taxa names</h5><hr>
                    <p>You can rename the leaves you want. First type the new name for the leaves you want to rename, then select the trees you want to apply the modification on.</p>

                        <div id="taxaname_err"></div>
                        <? if (count($taxaList >= 1)) { ?>

                        <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=taxanames" method="POST" class="custom" id="taxaname_form">
                            <?
                            $quarternb = intval(sizeof($taxaList) / 4);
                            $count = 0;
                            echo "<div class='small-3 columns'>";
                            foreach ($taxaList as $key => $value) {
                                if ($quarternb != 0 && $count % $quarternb == 0 && $count != 0) {
                                    echo "</div><div class='small-3 columns'>";
                                }
                                echo "<div class='rename'><label>" . $key . "</label><input type='text' size='16' name='names_" . $key . "' value='" . $key . "'/></div>";
                                $count++;
                            }
                            echo "</div><div class='cf'></div><p></p>";
                            ?>

                            <?
                            Utils::displayCollections($sortedList);
                            ?>

                            <input type="hidden" style="display:none;" name="exe" value="1"/>
                            <button type="submit" class="small button disablable">Rename leaves</button>
                        </form>
                        <span class="errors label alert"></span>
                        <?
                    }
                    else
                        echo "There is no tree in any collection, the tool cannot know species names.";
                    ?>
                </div>
            </div>
        </section>

        <!-- ############################################################################################################################################ -->
<? } ?>
        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Upload new trees"><img src="img/telecharger.png" alt="Upload trees" border="0" width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Upload more trees</h5><hr>
                    <p>You can upload more trees for your collections. In order to do this, you have to upload a file containing your tree(s) for each desired collection.
                        Files must be in Newick format.</p>
                    <form method="POST" action="index.php?id=<? echo $projet->getId(); ?>&p=new" enctype="multipart/form-data" id="uploadTrees"> 
                        
                        <div class="cf">
                            <div class="small-6 columns">
                                <div class="panel">
                                    <h5>Import in collection 1</h5>
                                    <label>Newick file</label>
                                    <input type="file" name="tree_nwk"/>
                                    <!--
                                    Script file : <input type="file" name="tree_tds"/>
                                    Annotation file : <input type="file" name="tree_tlf"/>
                                    -->
                                </div>
                            </div>
                            <div class="small-6 columns">
                                <div class="panel">
                                    <h5>Import in collection 2</h5>
                                    <label>Newick file</label>
                                    <input type="file" name="supertree_nwk"/>
                                    <!--
                                    Script file : <input type="file" name="supertree_tds"/>
                                    Annotation file : <input type="file" name="supertree_tlf"/>
                                    -->
                                </div>
                            </div>
                        </div><br>
                        <button type="submit" class="small button disablable">Upload tree(s)</button>
                    </form>
                    <span class="errors label alert"></span>
                    
                    <h5 class="subheader">Or paste more trees</h5><hr>
                    <form method="POST" action="index.php?id=<? echo $projet->getId(); ?>&p=new" id="pasteTrees"> 
                        
                        <div class="cf">
                            <div class="small-6 columns">
                                <h5>Import in collection 1</h5>
                                <label>Newick data</label>
                                <textarea name="tree_nwk_txt"></textarea>
                            </div>
                            <div class="small-6 columns">
                                <h5>Import in collection 2</h5>
                                <label>Newick data</label>
                                <textarea name="supertree_nwk_txt"/></textarea>
                            </div>
                        </div><br>
                        <button type="submit" class="small button disablable">Upload tree(s)</button>
                    </form>
                    <span class="errors label alert"></span>
                </div>
            </div>
        </section>
<?
if ($right_tree && $left_tree) {
    ?>
        <!-- ############################################################################################################################################ -->

        <section>
            <p class="title" data-section-title><a href="#" data-tooltip title="Delete trees"><img src="img/delete_big.png" alt="Delete trees" border="0" width="48"/></a></p>
            <div class="content" data-section-content>
                <div class="small-12">
                    <h5 class="subheader">Remove trees</h5><hr>
                    <form action="?p=exehandler&id=<?= $projet->getId() ?>&exe=remove" method="POST" class="custom" id="delete_form">
                        <p><strong>Beware, this tool permanently deletes the selected trees.</strong></p>
                        <div id="delete_err"></div>
                        <?
                        Utils::displayCollections($sortedList);
                        ?>

                        <input type="hidden" style="display:none;" name="exe" value="1"/>
                        <button type="submit" class="small button disablable"><span class="iconr rightarrow"></span>Delete selected trees</button>
                    </form>
                    <span class="errors label alert"></span>
                </div>
            </div>
        </section>
    </div>
</div>
    </div>
<script>
$(document).ready(function(){    
    $.validator.setDefaults({
        ignore: []
    });
    $("#right").validate({
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        }
    });
    $("#left").validate({
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        }
    });
    $("#restrict_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:2          
            }
        }, 
        messages: {
            "trees[]": "You must pick at least two trees."
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#restrict_err"));
            $("#restrict_err").show();
        }
    });
    $("#reroot_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:1         
            },
            outgroups: {
                required: true
            }
        }, 
        messages: {
            "trees[]": "You must pick at least one tree.",
            outgroups: "You must define an outgroup for rooting the tree."
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#reroot_err"));
        }
    });
    $("#taxacolor_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:1         
            }
        }, 
        messages: {
            "trees[]": "You must pick at least one tree."
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#color_err"));
        }
    });
    $("#super_form_MRP").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:2       
            }
        }, 
        messages: {
            "trees[]": "You must pick at least two trees."
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#super_err_MRP"));
        }
    });
    $("#super_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:2       
            }
        }, 
        messages: {
            "trees[]": "You must pick at least two trees."
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#super_err"));
        }
    });
    $("#display_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:1       
            }
        }, 
        messages: {
            "trees[]": "You must pick at least one tree.",
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#display_err"));
        }
    });
    
    $.validator.addClassRules({
        treenames: {
            required: true,
            maxlength: 12
        } 
    });
    $("#formname").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:1       
            }
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
    });
    $("#taxaname_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:1       
            }
        }, 
        messages: {
            "trees[]": "You must pick at least one tree.",
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#taxaname_err"));
        }
    });
    $("#uploadTrees").validate({
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        }
    });
    $("#pasteTrees").validate({
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        }
    });
    $("#delete_form").validate({ 
        rules: {
            "trees[]": {
                required: true,
                minlength:1       
            }
        }, 
        messages: {
            "trees[]": "You must pick at least one tree.",
        },
        submitHandler: function(form) {
            $('#submitProject').foundation('reveal', 'open', {closeOnBackgroundClick: false});
            form.submit();
        },
        errorPlacement: function(error, element) {
            error.appendTo($("#delete_err"));
        }
    });
});

</script>
<? } ?>

<ol class="joyride-list" data-joyride>
  <li data-id="titleProject" data-text="Next" data-options="tipLocation:left">
      <h4>Hello</h4>
      <p>Welcome to your project main page. This is the name of your project (a closed, resp. open, lock indicates a private, resp. public, project).</p>
  </li>
  <li data-id="whoisonline" data-text="Next" data-options="tipLocation:left">
      <h4>Collaboration!</h4>
      <p>Here is the list of online users. Click on it. The user with the crown is the administrator of the project, and the hand icon shows who currently controls the project's display.</p>
  </li>
  <li data-id="syncB" data-text="Next">
      <h4>(Un-)synchronize</h4>
      <p>When <i>synchronized</i> you see the display's changes applied by the person currently in control, while <i>unsynchronized</i> you can choose which trees are displayed on the workbench (but not edit them though), even if you don't have the hand on the project.</p>
  </li>
  <li data-id="divgene" data-text="Next">
      <h4>Tree collections</h4>
      <p>Two collections of trees can host your trees (e.g. gene trees / supertrees, or host /parasite trees, ...). You can reorder trees within a collection, or drag them to the workbench below.</p>
  </li>
  <li data-id="workbench" data-text="Next">
      <h4>Visualize</h4>
      <p>The workbench displays side by side two trees to work on.</p>
  </li>
  <li data-id="minitools" data-text="Next">
      <h4>Pairwise tools</h4>
      <p>Here are displayed some tools helping pairwise comparison of the trees currently on the workbench: RF distance, Maximum Agreement SubTree and Auto-Swap to reorder leaves in a similar way (while still respecting the topology).</p>
  </li>
  <li data-id="maxTools" data-text="Next">
      <h4>More tools</h4>
      <p>Here are tools for groupwise tree analysis and project management.</p>
  </li>
  <li data-button="End">
      <h4>The end</h4>
      <p>Enjoy using CompPhy!</p>
  </li>
</ol>
