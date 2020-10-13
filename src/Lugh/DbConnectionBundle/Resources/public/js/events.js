function msgCorrect(header ,msg, type)
{
    $.msgGrowl ({
        type: type
        , title: header
        , text: msg
    });
}
function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    alert('Query Variable ' + variable + ' not found');
}

function importEvent( actionUrl, input, url, div ){

    var values = JSON.stringify(input);
    var p_id = getQueryVariable("platform_id");

    $.getJSON(actionUrl, {params: values, platform_id: p_id}, function (data) {
        if (data.success == 1) {
            LoadContentUrlDiv(url, div);
            msgCorrect('Importacion','Evento Importado Correctamente','success');
        }
        else {
            msgCorrect('Importacion','Ha ocurrido un error','error');
        }
    })
    .error(function (data) {
        if (data.status == 401)
            window.location.href = Routing.generate('_home', true);
    });

}

function toggleActive( actionUrl, input, url, div ){

    var values = JSON.stringify(input);
    var p_id = getQueryVariable("platform_id");

    $.getJSON(actionUrl, {params: values, platform_id: p_id}, function (data) {

        if (data.success == 1) {
            LoadContentUrlDiv(url, div);
            msgCorrect('Activo','Modificado correctamente','success');
        }
        else {
            msgCorrect('Activo','Ha ocurrido un error','error');
        }
    })
    .error(function (data) {
        if (data.status == 401)
            window.location.href = Routing.generate('_home', true);
    });

}

function removeEvent( actionUrl, input, url, div ){

    var values = JSON.stringify(input);
    var p_id = getQueryVariable("platform_id");

    $.getJSON(actionUrl, {params: values, platform_id: p_id}, function (data) {

        if (data.success == 1) {
            LoadContentUrlDiv(url, div);
            msgCorrect('Eliminar','Eliminado correctamente','success');
        }
        else {
            msgCorrect('Eliminar','Ha ocurrido un error','error');
        }
    })
    .error(function (data) {
        if (data.status == 401)
            window.location.href = Routing.generate('_home', true);
    });

}

function refreshProvider( url, div ){

    var p_id = getQueryVariable("platform_id");
    LoadContentUrlDiv(url, div);
    
}
