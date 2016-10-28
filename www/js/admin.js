window.addEventListener("load", function(){
    /**
     * simple token based request response authentication
     */
    var loginForm = document.querySelector(".main-login form");
    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            var form = event.target;
            var hashed = md5(md5(form.passOrig.value), "mirin.cz" + form.loginToken.value);

            form.passOrig.value = Math.random().toString(36).substr(10);
            form.passHashed.value = hashed;
        });
    }
});

function gotoDelete(articleId, posted, name, deleteUrl) {
    var yesNo = confirm("Opravdu chcete smazat komentář '" + articleId + "' od '"
        + name + "' ze dne '" + posted + "'?");
    if (yesNo == true) {
        location.href = deleteUrl;
    }
}
