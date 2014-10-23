<script>
    $(document).ready(function() {
        var questions = $("#questions"),
        answers = $("#answers");
        $.ajax( {
            type: "GET",
            url: "faq.xml",
            dataType: "xml",
            success: function(xml) { 
                $(xml).find('q').each( function(){
                    var id = $(this).parent().attr('id');
                    answers.append(
                    $("<div/>")
                    .attr("id", id)
                    .css("margin-bottom", "45px")
                    .append(
                    $("<h5/>")
                    .text($(this).text())
                )
                );
                    questions.append(
                    $("<p/>").append(
                        $("<a/>")
                        .attr("href", "#"+id)
                        .html($(this).text() + "")
                    )
                );
                });
                $(xml).find('an').each( function(){
                    var id = $(this).parent().attr('id');
                    $("#"+id).append($(this).text());
                });
            }
        }); 
    })
</script>
<div class="row">
<h4>Frequently asked questions</h4><hr>
    <div id="faq">
        <div id="questions"></div>
        <hr>
        <div class="cLine"></div>
        <div id="answers"></div>
    </div>
</div>