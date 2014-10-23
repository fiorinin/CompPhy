$.fn.shiftSelectable = function() {
    var lastChecked,
    boxes = $(this);
 
    boxes.click(function(evt) {
        if(!lastChecked) {
            lastChecked = $(this);
            return;
        }
        if(evt.shiftKey) {
            var start = boxes.index($(this)),
                end = boxes.index(lastChecked);
            boxes.slice(Math.min(start, end), Math.max(start, end)).each(function() { 
                if(boxes.index($(this)) != Math.min(start, end)) {
                    var checked = lastChecked.children('input')[0].checked
                    $(this).find('input')
                        .attr('checked', checked)
                    if(checked)
                        $(this).find('span').addClass('checked');
                    else 
                        $(this).find('span').removeClass('checked');
                }
                //.trigger('change');
            })
            document.getSelection().removeAllRanges();
        }
 
        lastChecked = $(this);
    });
};