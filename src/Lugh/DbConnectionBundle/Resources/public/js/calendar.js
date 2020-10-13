function loadCalendar(url) {
	
	var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        
        $('#calendar-holder').empty();
        $('#calendar-holder').fullCalendar({
                header: {
                        left: 'prev, next',
                        center: 'title',
                        right: 'month,basicWeek,basicDay,'
                },
                events: url,
                eventClick: function(calEvent, jsEvent, view) {

                    //setFormCalendar(calEvent);
                    var url = ApiBase + "/calendar/event/" + calEvent.id;
                    $.getJSON( url, function( data ) {
                        setFormCalendar(data);
                        $('#datepicker-inline').datepicker('setDate', new Date(data.date));
                        if (!$('.accordion-body'). hasClass('in'))
                        {
                            $('.accordion-body').collapse('show');
                        }
                        
                    });

                    // change the border color just for fun
                    $(this).css('border-color', 'red');

                },
        });
	
}

function loadMiniCalendar() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!

    var yyyy = today.getFullYear();
    if(dd<10){dd='0'+dd} if(mm<10){mm='0'+mm} var today = mm+'/'+dd+'/'+yyyy;
    $('#input01').val(today);
    
    $('#datepicker-inline').datepicker({
        onSelect: function(dateText) {
            $('#input01').val(dateText);
        }
    });
    
    $('#timepicker-basic').timepicker ({});
    
    $('#myForm').validate({
	    rules: {
	      "timepicker-basic": {
	        minlength: 2,
	        required: true
	      },
	      input02: {
	        required: true,
	        required: true
	      },
	      select01: {
	      	required: true
              },
	    },
	    focusCleanup: false,
	    
	    highlight: function(label) {
	    	$(label).closest('.control-group').removeClass ('success').addClass('error');
	    },
	    success: function(label) {
	    	label
	    		.text('OK!').addClass('valid')
	    		.closest('.control-group').addClass('success');
		},
		errorPlacement: function(error, element) {
	     error.appendTo( element.parents ('.controls') );
	   },
           submitHandler: function(form) {
                event.preventDefault();
                $.post( $(form).attr('action'), $(form).serialize(), function(data) {
                    // just try to see the outputs
                    console.log(data)
                    if(data.success === "1") {
                        loadCalendar($(form).attr('return'));
                    } else {
                        // Error code here
                    }
                }, 'json');
            }
	  });
          
          $('#collapseFour').on('hidden.bs.collapse', function () {
              $('#myForm :input')
                .not(':button, :submit, :reset')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');
          });
}

function setFormCalendar(event)
{
    $('#myForm :input[name="input_id"]').val(event._id);
    $('#myForm :input[name="input01"]').val(event.date);
    $('#myForm :input[name="timepicker-basic"]').val(event.time);
    $('#myForm :input[name="input02"]').val(event.name);
    $('#myForm :input[name="input03"]').val(event.app);
    $('#myForm :input[name="select01"]').val('act');

}


