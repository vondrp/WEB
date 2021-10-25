/**
 * remove attribute disabled from submit button
 * in form for changing password, when all inputs all filled
 */
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

/**
 * remove attribute disabled from submit button
 * in form for registration, when all inputs all filled
 */
function registrationUp(){
    let username = document.getElementById('username');
    let email = document.getElementById('username');
    let password = document.getElementById('password')
    let password2 = document.getElementById('password2')
    let registerBtn = document.getElementById('registerButton');

    document.getElementById('registrationForm').addEventListener('input', ()=> {
        if(username.value.length > 0 && email.value.length > 0
            && password.value.length >0 && password2.value.length > 0){
            registerBtn.removeAttribute('disabled');
        } else{
            registerBtn.setAttribute('disabled', 'disabled');
        }
    });
}

/**
 * remove attribute disabled from submit button
 * in form for changing password, when all inputs all filled
 */
function changePassword(){
    let originalPassword = document.getElementById('originalPassword');
    let newPassword = document.getElementById('newPassword');
    let confirmNewPassword = document.getElementById('confirmNewPassword');
    let changePasswordBtn = document.getElementById('changePasswordButton');

    document.getElementById('changePasswordForm').addEventListener('input', ()=>{
        if(originalPassword.value.length > 0 && newPassword.value.length>0
        && confirmNewPassword.value.length >0){
            changePasswordBtn.removeAttribute('disabled');
        }else{
            changePasswordBtn.setAttribute('disabled', 'disabled');
        }
    });
}

/**
 * Change type of password input
 * to password or text
 * @param passwordId        id of an password input
 * @param openEyeId         icon of an open eye
 * @param closeEyeId        icon of an close eye
 */
function openCloseEye(passwordId, openEyeId, closeEyeId){
    let x = document.getElementById(passwordId);
    let y = document.getElementById(openEyeId);
    let z = document.getElementById(closeEyeId);

    if(x.type === 'password'){
        x.type = "text";
        y.style.display = "block";
        y.style.paddingTop = '0.2em';
        z.style.display = "none";
    }else{
        x.type = "password";
        y.style.display = "none";
        z.style.display = "block";
        z.style.paddingTop = '0.2em';
    }
}

/**
 * default confirmation
 */
function confirmAction() {
    let confirmAction = confirm("Určitě chcete tuto akci podniknout?");
    if (confirmAction) {
        alert("Akce úspěšně provedena");
    } else {
        alert("Akce zrušena");
    }
}

/**
 *
 * @param instanceType  string of the type of instance, which is going to be delated
 */
function confirmAction(instanceType, actionType = null) {
    let question = "Určitě chcete tuto akci podniknout?";

    if(actionType === "delete"){
        question = "Určitě chcete "+instanceType+ " smazat?";
    }
    let confirmAction = confirm(question);
    if (confirmAction) {
        return true;
    } else {
        alert("Akce zrušena");
        return false;
    }
}
