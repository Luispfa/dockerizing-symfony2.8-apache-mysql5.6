$(window).bind("popstate", function (event) {
    if (event.originalEvent.state == null) {
        window.location.href = Routing.generate('_home', true);
    }
    LoadContentUrlDiv(location.href, event.originalEvent.state.div);  
  }); 

function LoadContent(url, div)
{   
    LoadContentUrlDiv(url, div)
    history.pushState({div: div}, "", url); 
}

function LoadContentUrlDiv(url, div)
{   
    jQuery("#" + div).load(url, function(responseText, textStatus, req) {
            if (req.status == 401)
            {
                window.location.href = Routing.generate('_home', true);
            }
    });
}

function doLogout()
{   
    window.location.href = Routing.generate('fos_user_security_logout', true);
}
function LoadContentinNewTab(url)
{
    var win = window.open(url, '_blank');
    win.focus();
}