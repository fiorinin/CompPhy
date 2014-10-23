$(document).ready(function(){
	var currentTODO;

	// Configuring the delete confirmation dialog
	$("#dialog-confirm").dialog({
		resizable: false,
		height:170,
		modal: true,
		autoOpen:false,
		buttons: {
			'Delete item': function() {
				$.get("ajax/ajax_todolist.php",{
                                    "action":"delete",
                                    "id":currentTODO.data('id'),
                                     complete :function(msg){
                                         currentTODO.fadeOut('fast');
                                     }
				});
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
        
        // When a double click occurs, just simulate a click on the edit button:
        $('.todo').live('dblclick',function(){
                $(this).find('a.edit').click();
        });

        // If any link in the todo is clicked, assign
        // the todo item to the currentTODO variable for later use.

        $('.todo a').live('click',function(e){
                currentTODO = $(this).closest('.todo');
                currentTODO.data('id',currentTODO.attr('id').replace('todo-',''));

                e.preventDefault();
        });
        
        //$(".todo a").tipsy({gravity:"s", live:true});

        // Listening for a click on a delete button:

        $('.todo a.delete').live('click',function(){
                $("#dialog-confirm").dialog('open');
        });

        // Listening for a click on a edit button

        $('.todo a.edit').live('click',function(){
                var container = currentTODO.find('.texttodo');

                if(!currentTODO.data('origText'))
                        currentTODO.data('origText',container.text());
                else
                    return false;

                if(container.text() != "New todo item. Doubleclick to edit.")
                    $('<input type="text">').val(container.text()).appendTo(container.empty());
                else
                    $('<input type="text">').val('').appendTo(container.empty());

                // Appending the save and cancel links:
                container.append(
                        '<div class="editTodo">'+
                                '<a class="saveChanges" href="">Save</a> or <a class="discardChanges" href="">Cancel</a>'+
                        '</div>'
                );

        });
        
	// The cancel edit link:

	$('.todo a.discardChanges').live('click',function(){
		currentTODO.find('.texttodo')
					.text(currentTODO.data('origText'))
					.end()
					.removeData('origText');
	});

	// The save changes link:

	$('.todo a.saveChanges').live('click',function(){
		var text = currentTODO.find("input[type=text]").val();

		$.get("ajax/ajax_todolist.php",{'action':'edit','id':currentTODO.data('id'),'text':text});

		currentTODO.removeData('origText')
					.find(".texttodo")
					.text(text);
	});
        
	// The validate link:

	$('.todo a.validatelogo').live('click',function(){
		$.get("ajax/ajax_todolist.php",{'action':'validate','id':currentTODO.data('id')},function(msg){
                    currentTODO.fadeOut('fast');
                    $(msg).hide().prependTo('.todoListOld').show('slow');
                }, 'html');
	});
        
	// The timeline link:
        
        $('.todo a.timelinelogo').live('click',function(){
            var content = currentTODO.find('.texttodo').text();
            handleHistory(currentTODO.data('id'), 0, content, "addspec");
            setTimeout(function() {
                $.get("ajax/ajax_todolist.php",{'action':'delete','id':currentTODO.data('id')},function(msg){
                    currentTODO.fadeOut('fast');
                });
            },2000)
        });
});