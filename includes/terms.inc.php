<?
$file = ROOTPATH . "compphy/termsofuse.html";
$content = file_get_contents($file);
?>
<script>
$(document).ready(function() {
    changeTitle("CompPhy - Terms of use");
})
</script>

<?
echo $content; 
?>