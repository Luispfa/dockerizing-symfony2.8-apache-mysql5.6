function setP(url, input, msg, fun, urlcontent, div)
{
    var values = $(input).serializeJSON();

    $.post(url, { params: values }, function(data){
        if (data.success === "1")
        {
            msgCorrect('Save',msg,'success');
            fun(urlcontent, div);
        }
        else
        {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    }, 'json')
    .fail(function(data) {
        if (data.status == 401)
        {
            window.location.href = Routing.generate('_home', true); 
        }
    });
}

function SetParams(url, input, urlcontent, div)
{
    setP(url, input, 'Parametros Guardados Correctamente',LoadContentUrlDiv, urlcontent, div);
}

function SetPoints(url, input, urlcontent, div)
{
    setP(url, input, 'Puntos Guardados Correctamente', LoadContentUrlDiv, urlcontent, div);
}

function AddParam(addurl, input, type, url, div)
{
    var values = $(input).serializeJSON();
    $.getJSON(addurl, { params: values, type: type }, function(data){

        if (data.success === "1")
        {
            LoadContentUrlDiv(url, div)
            msgCorrect('Save','Parametro Guardado Correctamente','success');
        }
        else
        {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    })
    .error(function(data) {
        if (data.status == 401)
        {
            window.location.href = Routing.generate('_home', true); 
        }
    });
}
function editParam(id, editurl, input, layouturl, div){
    var values = $(input).serializeJSON();
    $.getJSON(editurl, { id: id, params: values }, function(data){
            if (data.success === "1")
            {
                LoadContentUrlDiv(layouturl, div);
                msgCorrect('Edit','Parametros Guardados Correctamente','success');
            }
            else
            {
                msgCorrect('Edit','Ha ocurrido un error: '+data.error,'error');
            }
        })
        .error(function(data) {
            if (data.status == 401)
            {
                window.location.href = Routing.generate('_home', true);
            }
        });
}

function removeParameter(id, removeurl, url, div)
{
    $.getJSON(removeurl, { id: id }, function(data){

        if (data.success === "1")
        {
            LoadContentUrlDiv(url, div)
            msgCorrect('Save','Parametros Guardados Correctamente','success');
        }
        else
        {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    })
    .error(function(data) {
        if (data.status == 401)
        {
            window.location.href = Routing.generate('_home', true); 
        }
    });
}

function setContentModal(modal, tag, type, data)
{
    var content = '';
    var buttonSave = $("#" + modal + " #button-save");
    var inputTag = $("#" + modal + " #tag");
    if (type === undefined || type == 'view')
    {
        buttonSave.hide();
        inputTag.hide();
    }
    else if(type == 'edit')
    {
        buttonSave.show();
        inputTag.show();
    }

    for (var key in data.success.message){
        if (type === undefined || type == 'view')
        {
            content = data.success.message[key];
        }
        else if(type == 'edit')
        {
            content = '<textarea id="form-tag-' + key + '" name="form-tag-' + key + '" class="textarea-large">' + data.success.message[key] + '</textarea>';
        }
        $("#" + modal + " #tag-text-" + key).html(content);
    }
    inputTag.val(tag);
    $("#" + modal).modal('show');
}

function loadYaml(modal, url, tag, type)
{
    if (tag === '') {
        var data = {success: {message: {ca_es:'', en_gb:'',es_es:'',gl_es:''}}};
        setContentModal(modal, tag, type, data);
    }
    else {
        $.getJSON(url + '/' + tag, function(data){
            if (data.success)
            {
                setContentModal(modal, tag, type, data);
            }
            else
            {
                msgCorrect('Save','Ha ocurrido un error','error');
            }
        })
        .error(function(data) {
            if (data.status == 401)
            {
                window.location.href = Routing.generate('_home', true); 
            }
        });
    }
    
}

function editYaml(editurl, tag, content, url, div)
{
    content = $(content).serializeJSON()
    $.getJSON(editurl + '/' + tag , { content: content }, function(data){

        if (data.success === "1")
        {
            LoadContentUrlDiv(url + '/' + tag, div);
            msgCorrect('Save','Mail Modificado Correctamente','success');
        }
        else
        {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    })
    .error(function(data) {
        if (data.status == 401)
        {
            window.location.href = Routing.generate('_home', true); 
        }
    });
}

