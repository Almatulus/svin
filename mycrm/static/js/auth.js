var cur = {
    _csrf: false,
    forgotLogin: false,
    forgotHash: false,
    forgotFormSend: function () {
        cur._csrf = document.getElementsByName("_csrf")[0].value;
        var login = document.getElementById("restoreform-phone").value;
        var recaptcha = document.getElementById("restoreform-recaptcha").value;
        validatePhone(login, recaptcha, cur._csrf);
    },
    sendCode: function () {
        var key = document.getElementById("restoreform-code").value;
        validateKey(cur.forgotLogin, key, cur._csrf);
    },
    newPassSend: function () {
        var form = document.getElementById("restore-form");
        var passWrap = document.getElementById("pass-wrap");
        if (passWrap.getElementsByClassName(".has-error").length == 0) {
            var pass = document.getElementById("pass").value;
            var repass = document.getElementById("repass").value;
            var body = "_csrf=" + encodeURIComponent(cur._csrf)
                + "&login=" + encodeURIComponent(cur.forgotLogin)
                + "&password=" + encodeURIComponent(pass)
                + "&repassword=" + encodeURIComponent(repass);
            var url = "reset-password";
            var success = function (response) {
                showMessage(response.message, function () {
                    window.location.href = "login"
                });
            };
            var error = function (response) {
                var errorMessage = response.message;
                showMessage(errorMessage);
            };
            sendRequest(body, url, success, error);
        }
    }
};

function validatePhone(login, captcha, _csrf) {
    var body = "_csrf=" + encodeURIComponent(_csrf)
        + "&recaptcha=" + encodeURIComponent(recaptcha)
        + "&login=" + encodeURIComponent(login);
    var method = "POST";
    var url = "validate-phone";
    var success = function (responseText) {
        cur.forgotLogin = login;
        document.getElementById("phone-wrap").innerHTML = "";
        document.getElementById("key-wrap").style.display = "";
        document.getElementById("btn-restore").onclick = function () {
            cur.sendCode();
        };
    };
    var error = function (response) {
        var errorMessage = response.message;
        showMessage(errorMessage);
    };
    sendRequest(body, url, success, error);
}

function validateKey(login, key, _csrf) {
    var body = "_csrf=" + encodeURIComponent(_csrf)
        + "&key=" + encodeURIComponent(key)
        + "&login=" + encodeURIComponent(login);
    var method = "POST";
    var url = "validate-key";
    var success = function (responseText) {
        document.getElementById("key-wrap").style.display = "none";
        document.getElementById("pass-wrap").style.display = "";
        document.getElementById("btn-restore").onclick = function () {
            cur.newPassSend();
        };
    };
    var error = function (response) {
        var errorMessage = response.message;
        showMessage(errorMessage);
    };
    sendRequest(body, url, success, error);
}

function sendRequest(body, url, success, error) {
    var xhr = new XMLHttpRequest();
    var method = "POST";
    xhr.open(method, url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            success(JSON.parse(this.responseText));
        } else if (this.readyState == 4 && this.status == 400) {
            error(JSON.parse(this.responseText))
        }
    };
    xhr.send(body);
}

function showMessage(message, callback) {
    bootbox.alert(message, callback);
}
