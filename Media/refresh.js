function setRefresh(timeout) {
    setInterval(function() {
        $.mobile.changePage(
            window.location.href,
            {
            allowSamePageTransition : true,
            transition              : 'none',
            showLoadMsg             : false,
            reloadPage              : true
        });
    }, timeout);
}
