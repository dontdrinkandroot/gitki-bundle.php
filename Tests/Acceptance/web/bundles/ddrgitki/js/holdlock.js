function HoldLock(holdLockUrl) {

    var self = this;
    var timeOut = null;

    var refresh = function () {
        $.get(holdLockUrl)
            .done(function (data) {
                timeOut = window.setTimeout(refresh, 10000);
            })
            .fail(function (data) {
                console.error(data);
                alert("We are sorry. Holding the page lock failed (saving won't work anymore), copy your data to the clipboard, go back and try to reedit the page.")
            });
    };

    this.cancel = function () {
        clearTimeout(timeOut);
    };

    refresh();
}