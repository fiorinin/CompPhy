<?
$file = ROOTPATH . "compphy/links.html";
$content = file_get_contents($file);
?>
<script>
    $(document).ready(function() {
        changeTitle("CompPhy - Usefull links");
    })
</script>
<div class="row">
<h4>References</h4><hr>
    <?
    echo $content;
    ?>
</div>