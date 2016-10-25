window.addEventListener("load", function(){
    /**
     * simple token based request response authentication
     */
    document.querySelector(".main-login form").addEventListener("submit", function(event) {
        var form = event.target;
        var hashed = md5(md5(form.passOrig.value), "mirin.cz" + form.loginToken.value);

        form.passOrig.value = Math.random().toString(36).substr(10);
        form.passHashed.value = hashed;
    });
});
