var pkBaseURL = ApiAddress;

function loadScript(url, callback)
{
    // Adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback;
    script.onload = callback;

    // Fire the loading
    head.appendChild(script);
}

loadScript(pkBaseURL + 'piwik.js', function() {
    try {
        var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", SiteId);
        if (NameController !== 'home')
        {
            piwikTracker.setCustomVariable( 1, 'uid', UserName, 'visit' );
        }
        piwikTracker.trackPageView();
        piwikTracker.enableLinkTracking();
    } catch( err ) {}
});




