/**
 * Created by hermann.plass on 25/05/2015.
 */
'use strict';
function msgCorrect(header ,msg, type)
{
    $.msgGrowl ({
        type: type,
        title: header,
        text: msg,
    });
}
function alertCorrect(header ,msg, type){
    $.msgAlert ({
        type: type,
        title: header,
        text: msg,
    });
}
function addStyle( actionUrl, input, url, div ){

    var values = $(input).serializeJSON();

    var progressDiv = document.createElement("div");
    progressDiv.innerHTML = '<div class="msgAlert_overlay"></div><div class="msgAlert info"><div class="msgAlert_popup"><div class="msgAlert_header"><h4>En proceso, espere . . .</h4></div><div class="progress progress-primary progress-striped active" style="margin-bottom: 2em;"><div class="bar" style="width: 100%"></div></div></div></div>'; 
    document.body.appendChild(progressDiv);  

    if(values.form['newstyle-modal-name'] !== false && values.form['newstyle-modal-path'] !== false){
        
        $.getJSON( actionUrl, { params: values }, function(data){
        document.body.removeChild(progressDiv);
            if (data.success === "1"){
                LoadContentUrlDiv(url, div);
                msgCorrect('Save','Estilo Registrado Correctamente','success');
                alertCorrect('Save','Estilo Registrado Correctamente','success');
                
            }
            else{
                msgCorrect('Save','Ha ocurrido un error','error');
                alertCorrect('Save','Ha ocurrido un error','error');
            }
        }).error(function(data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
        });
    }
}

function saveStyle( actionUrl, input, url, div ){

    var values = $(input).serializeJSON();
    var t_id = getQueryVariable("template_id");

    var progressDiv = document.createElement("div");
    progressDiv.innerHTML = '<div class="msgAlert_overlay"></div><div class="msgAlert info"><div class="msgAlert_popup"><div class="msgAlert_header"><h4>En proceso, espere . . .</h4></div><div class="progress progress-primary progress-striped active" style="margin-bottom: 2em;"><div class="bar" style="width: 100%"></div></div></div></div>'; 
    document.body.appendChild(progressDiv);  
    

    $.getJSON(actionUrl, {params: values, template_id: t_id}, function (data) {
        document.body.removeChild(progressDiv);
        if (data.success === "1") {
            LoadContentUrlDiv(url, div);
            msgCorrect('Save', 'Estilo guardado correctamente', 'success');
            alertCorrect('Save', 'Estilo guardado correctamente', 'success');
        }
        else {
            msgCorrect('Save', 'Ha ocurrido un error', 'error');
            alertCorrect('Save','Ha ocurrido un error','error');
        }
    }).error(function (data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
        });

}

function saveStyle2( actionUrl, input, url, div ){

    var values = $(input).serializeJSON();
    var t_id = getQueryVariable("platform_id");

    var progressDiv = document.createElement("div");
    progressDiv.innerHTML = '<div class="msgAlert_overlay"></div><div class="msgAlert info"><div class="msgAlert_popup"><div class="msgAlert_header"><h4>En proceso, espere . . .</h4></div><div class="progress progress-primary progress-striped active" style="margin-bottom: 2em;"><div class="bar" style="width: 100%"></div></div></div></div>'; 
    document.body.appendChild(progressDiv);  
    

    $.getJSON(actionUrl, {params: values, platform_id: t_id}, function (data) {
        document.body.removeChild(progressDiv);
        if (data.success === "1") {
            LoadContentUrlDiv(url, div);
            msgCorrect('Save', 'Estilo guardado correctamente', 'success');
            alertCorrect('Save', 'Estilo guardado correctamente', 'success');
        }
        else {
            msgCorrect('Save', 'Ha ocurrido un error', 'error');
            alertCorrect('Save','Ha ocurrido un error','error');
        }
    }).error(function (data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
        });

}


function saveMailTemplate( actionUrl, url, div ){
    var values = tinyMCE.activeEditor.getContent();
    var t_id = getQueryVariable("template_id");

    var progressDiv = document.createElement("div");
    progressDiv.innerHTML = '<div class="msgAlert_overlay"></div><div class="msgAlert info"><div class="msgAlert_popup"><div class="msgAlert_header"><h4>En proceso, espere . . .</h4></div><div class="progress progress-primary progress-striped active" style="margin-bottom: 2em;"><div class="bar" style="width: 100%"></div></div></div></div>'; 
    document.body.appendChild(progressDiv);  

    $.post(actionUrl, {html: values, template_id: t_id}, function(data) {
        document.body.removeChild(progressDiv);
        if (data.success === "1") {
            LoadContentUrlDiv(url, div);
            msgCorrect('Save', 'Estilo guardado correctamente', 'success');
        }
        else {
            msgCorrect('Save', 'Ha ocurrido un error', 'error');
        }
    }, 'json')
        .error(function (data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
        });
}
function saveMailTemplate2( actionUrl, url, div ){
    var values = tinyMCE.activeEditor.getContent();
    var t_id = getQueryVariable("platform_id");

    $.post(actionUrl, {html: values, platform_id: t_id}, function(data) {

        if (data.success === "1") {
            LoadContentUrlDiv(url, div);
            msgCorrect('Save', 'Estilo guardado correctamente', 'success');
        }
        else {
            msgCorrect('Save', 'Ha ocurrido un error', 'error');
        }
    }, 'json')
        .error(function (data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
        });
}

function removeStyle( actionUrl, input, url, div ){

    var value = input;

    var progressDiv = document.createElement("div");
    progressDiv.innerHTML = '<div class="msgAlert_overlay"></div><div class="msgAlert info"><div class="msgAlert_popup"><div class="msgAlert_header"><h4>En proceso, espere . . .</h4></div><div class="progress progress-primary progress-striped active" style="margin-bottom: 2em;"><div class="bar" style="width: 100%"></div></div></div></div>'; 
    document.body.appendChild(progressDiv);  

    $.getJSON(actionUrl, {style: value}, function (data) {
        document.body.removeChild(progressDiv);
        if (data.success === "1") {
            LoadContentUrlDiv(url, div);
            msgCorrect('Rebuild Style', 'Estilo actualizado correctamente', 'success');
            alertCorrect('Rebuild Style', 'Estilo actualizara correctamente', 'success');
        }
        else {
            msgCorrect('Rebuild Style', 'Ha ocurrido un error', 'error');
            alertCorrect('Rebuild Style','Ha ocurrido un error','error');
        }
    }).error(function (data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
        });

}

function cloneMailTemplate( actionUrl, url, div ){
    var values = '1';
    var t_id = getQueryVariable("platform_id");
   
    var progressDiv = document.createElement("div");
    progressDiv.innerHTML = '<div class="msgAlert_overlay"></div><div class="msgAlert info"><div class="msgAlert_popup"><div class="msgAlert_header"><h4>En proceso, espere . . .</h4></div><div class="progress progress-primary progress-striped active" style="margin-bottom: 2em;"><div class="bar" style="width: 100%"></div></div></div></div>'; 
    document.body.appendChild(progressDiv);  

    $.post(actionUrl, {template: $("#templates").val()[0], platform_id: t_id}, function(data) {
        document.body.appendChild(progressDiv);  

        if (data.success === "1") {
            LoadContentUrlDiv(url, div);
            msgCorrect('Save', 'Estilo guardado correctamente', 'success');
            $(".modal-backdrop").toggle();
            $('#templateModal').show(false);
        }
        else {
            msgCorrect('Save', 'Ha ocurrido un error', 'error');
        }
    }, 'json')
        .error(function (data) {
            if (data.status == 401)
                window.location.href = Routing.generate('_home', true);
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
