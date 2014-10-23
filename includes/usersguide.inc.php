<?
$file = ROOTPATH . "compphy/userguide.html";
$content = file_get_contents($file);
?>
<script>
    $(document).ready(function() {
        changeTitle("CompPhy - User Guide");
    })
</script>
<div class="row">
<h4>Userguide</h4><hr>
<p>CompPhy is a web platform dedicated to the collaborative handling of phylogenetic trees. Users can freely manage collections of trees, associated documents, and communicate on a common project. CompPhy offers functionalities covering tree edition, tree comparison, supertree inference, data management and, most specifically, collaborative work, e.g. allowing several persons to work together at the same time on same project. CompPhy relies on web technologies to offer shared tree visualization, (a)synchronous manipulation of trees, data exchange/storage, as well as a timeline and a forum on each project to keep track of the dataset analysis progress. </p>

<p>A project can be private or public. When public, any non-member with the link can view the trees of the project with limited functionalities, but cannot modify the trees or anything in the project. Private projects are only accessible by the project members, whose list is maintained by the project administrator.

<p>The main input to a CompPhy's project consist of trees in Newick format, such as:</p>

<p>&nbsp;&nbsp; &nbsp; &nbsp;  (STAAT_2_PE2456:0.01,(STAAE_1_PE2425:0.02,(STAAC_1_PE2417:0.01,STAA8_1_PE2683:0.03)90.5:0.007)20:0.02);</p>

<p>We present below the different parts of CompPhy's interface when on a project page and the functionalities available therein.</p>

    <h4>Interface zones</h4>
    <img src="img/img_compphy_userguide.png" alt="CompPhy online interface" title="CompPhy online interface" /> 
    <?
    echo $content;
    ?>
</div>
