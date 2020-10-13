$(document).on("click", ".open-ResetDialog", function () {
     var platformId = $(this).data('platform_id');
     $(".modal-body #platform_id").val( platformId );
     // As pointed out in comments, 
     // it is superfluous to have to manually call the modal.
     // $('#addBookDialog').modal('show');
});

function resetPlatform(url, form, modalview, input)
{
    var values = {};
    $.each($(form).serializeArray(), function(i, field) {
        values[field.name] = field.value;
    });
    $(modalview).modal('hide');
    input.val('')
    
    $.getJSON(url, { form: values }, function(data){
        if (data.success === "1")
        {
            msgCorrect('Save','Plataforma reiniciada correctamente','success');
        }
        else if(data.success === "0")
        {
            msgCorrect('Save','No ha introducido correctamente la rista de digitos','error');
        }
        else
        {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    });

}