function msgCorrect(header ,msg, type)
{
    $.msgGrowl ({
            type: type
            , title: header
            , text: msg
    });
}
function alertCorrect(header ,msg, type){
    $.msgAlert ({
        type: type,
        title: header,
        text: msg,
    });
}

function SetParam(url, param)
{
    $.getJSON(url, { param: param }, function(data){
        if (data.success === "1")
        {
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
        else {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    });

}

function SetActive(url, active)
{
    var chk = false;
    if (active==='checked') {
        chk = 1;
    }
    else {
        chk = 0;
    }
    
    $.getJSON(url, { active: chk }, function(data){
        if (data.success === "1")
        {
            msgCorrect('Save','Activación/Desactivación Realizada Correctamente','success');
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
        else {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    });

}

function launchScript(url)
{
    $.getJSON(url, function(data){
        if (data.success === "1")
        {
            msgCorrect('Launch','Script Lanzado Correctamente','success');
        }
        else
        {
            msgCorrect('Launch','Ha ocurrido un error','error');
        }
    })
    .error(function(data) {
        if (data.status == 401)
        {
            window.location.href = Routing.generate('_home', true); 
        }
        else {
            msgCorrect('Save','Ha ocurrido un error','error');
        }
    });

}


function submitForm(url, params)
{
    $.getJSON(url, { params: params }, function(data){
        if (data.success === "1")
        {
            msgCorrect('Save','Parametro Guardado Correctamente','success');
          alertCorrect('Save','Parametro Guardado Correctamente','success');
        }
        else
        {
            msgCorrect('Save','Ha ocurrido un error','error');
          alertCorrect('Save','Ha ocurrido un error','error');
        }
    })
    .error(function(data) {
        if (data.status == 401)
        {
            window.location.href = Routing.generate('_home', true); 
        }
        else {
            msgCorrect('Save','Ha ocurrido un error','error');
          alertCorrect('Save','Ha ocurrido un error','error');
        }
    });
}


