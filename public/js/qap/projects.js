$(document).ready(function () {
	
	$('#createproject').on('click', function () {
		var finish = false;
		
		$( "#upps-no" ).show();
		$( "#uppsworkflow" ).hide();
		$( "#uppsname" ).hide();
		
		if($("#scrum").is(':checked') || $("#kanban").is(':checked'))
			finish = true;
		else{
			$( "#upps-no" ).hide();
			$( "#uppsworkflow" ).show();
		}
		
		if( $("#project_name").val().length === 0 && finish == true ){
			finish = false;
			$( "#upps-no" ).hide();
			$( "#uppsname" ).show();
		}
		
		if( finish == true ){
			
			var project_name = $('#project_name').val();
			var scrum = 0;
			var kanban = 0;
			
			if($("#scrum").is(':checked')){
				scrum = 1;
			}
			if($("#kanban").is(':checked')){
				kanban = 1;
			}
			
			var formData = {project_name:project_name,scrum:scrum,kanban:kanban};
			
			$.ajax({
           	    url : "/admin/createproject",
           	    type: "POST",
           	    dataType: 'json',
           	    data : formData,
           	    success: function(data, textStatus, jqXHR)
           	    	{
           	    		$( "#upps-no" ).hide();
           	    		$( "#coolproject" ).show();
        			
           	    		$( "#createproject" ).hide();
           	    		$( "#manageproject" ).show();
           	        	
           	    	},
           	    error: function (jqXHR, textStatus, errorThrown)
           	    	{
           	 
           	    	}
           	});
			
		}
		    
    });
	
	$('#myModal').on('hidden.bs.modal', function (e) {
		$( "#upps-no" ).show();
		$( "#uppsworkflow" ).hide();
		$( "#uppsname" ).hide();
		$( "#coolproject" ).hide();
		$( "#createproject" ).show();
		$( "#manageproject" ).hide();
		
		$("#project_name").val('');
		$("#scrum").prop('checked', false); 
		
		$("#kanban").removeAttr('checked');
	})
	
});