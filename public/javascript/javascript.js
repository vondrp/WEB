function logIn(){
    let usernameOrEmail = document.getElementById('usernameOrEmail');
    let password = document.getElementById('password')
    let loginBtn = document.getElementById('loginButton');

    document.getElementById('loginForm').addEventListener('input', ()=> {
        if(usernameOrEmail.value.length > 0
            && password.value.length >0){
            loginBtn.removeAttribute('disabled');
        } else{
            loginBtn.setAttribute('disabled', 'disabled');
        }
    });
}

function registrationUp(){
    let username = document.getElementById('username');
    let email = document.getElementById('username');
    let password = document.getElementById('password')
    let password2 = document.getElementById('password2')
    let registBtn = document.getElementById('registerButton');

    document.getElementById('registrationForm').addEventListener('input', ()=> {
        if(username.value.length > 0 && email.value.length > 0
            && password.value.length >0 && password2.value.length > 0){
            registBtn.removeAttribute('disabled');
        } else{
            registBtn.setAttribute('disabled', 'disabled');
        }
    });
}

function openCloseEye(passwordId, openEyeId, closeEyeId){
    var x = document.getElementById(passwordId);
    var y = document.getElementById(openEyeId);
    var z = document.getElementById(closeEyeId);

    if(x.type === 'password'){
        x.type = "text";
        y.style.display = "block";
        z.style.display = "none";
    }else{
        x.type = "password";
        y.style.display = "none";
        z.style.display = "block";
    }
}
