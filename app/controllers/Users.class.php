<?php

/**
 * Class Users - controller of users
 */
class Users extends Controller{
    /** length of the password */
    const PASSWORD_MIN_LENGTH = 6;
    /**
     * Users constructor.
     */
    public function __construct(){
        $this->userModel = $this->model('User');
    }

    /**
     * index page is used for user profile
     * @param null $user_id     id of the user, if not provided, user in session is showed
     */
    public function index($user_id = null){
        /*if(!isLoggedIn()){
            header("Location: ".URLROOT . "/users/login");
        }*/

        if($user_id == null){
            if(!isLoggedIn()){
                header("Location: ".URLROOT . "/users/login");
            }
            $user = $this->userModel->findUserByID($_SESSION['user']->id);
            $userPosts = $this->userModel->findUserPosts($_SESSION['user']->id);
            $userReviews = $this->userModel->findUserReviews($_SESSION['user']->id);
        }else{
            $user = $this->userModel->findUserByID($user_id);
            if(!$user){
                if(!isLoggedIn()){
                    header("Location: ".URLROOT . "/users/login");
                }
                $user = $this->userModel->findUserByID($_SESSION['user']->id);
                $userPosts = $this->userModel->findUserPosts($_SESSION['user']->id);
                $userReviews = $this->userModel->findUserReviews($_SESSION['user']->id);
            }else{
                $userPosts = $this->userModel->findUserPosts($user_id);
                $userReviews = $this->userModel->findUserReviews($user_id);
            }
        }
        $data = [
            'user' => $user,
            'userPosts' => $userPosts,
            'userReviews' => $userReviews,
        ];
        $this->view('users/index', $data);
    }


    /**
     * registration of the user
     */
    public function register(){
        if(!empty($_SESSION['user'])){
            header("Location: ".URLROOT . "/users/index");
        }
        $data = [
            'username' => '',
            'email' => '',
            'password' => '',
            'confirmPassword' => '',
            'usernameError' => '',
            'emailError'=> '',
            'passwordError' => '',
            'confirmPasswordError' => ''
        ];
        //REQUEST METHOD
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //Santize post data
            //severs problems with unwanted characters - PDO mechanic
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            //trim remove white space
            $data = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirmPassword' => trim($_POST['confirmPassword']),
                'usernameError' => '',
                'emailError'=> '',
                'passwordError' => '',
                'confirmPasswordError' => ''
            ];
            $nameValidation = "/^[a-zA-Z0-9]*$/";
            $passwordValidation = "/^(.{0,7}|[^a-z]*|[^\d]*)$/i";
            //Validate username on letters/numbers
            if(empty($data['username'])){
                $data['usernameError'] = 'Please enter username';
            }elseif(!preg_match($nameValidation, $data['username'])){
                $data['usernameError'] = 'Name can only contain letters and numbers';
            }
            //Validate email
            if(empty($data['email'])){
                $data['emailError'] = 'Please enter email address.';
            }elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $data['emailError'] = 'Please enter the correct format';
            }else{
                //check if email exists
                if($this->userModel->userEmailAlreadyRegistered($data['email'])){
                    $data['emailError'] = 'Email is already taken';
                }
            }
            //Validate password on length and numeric values
            if(empty($data['password'])){
                $data['passwordError'] = 'Prosím uveďte heslo.';
            }elseif ( strlen($data['password']) < Users::PASSWORD_MIN_LENGTH ){
                $data['passwordError'] = 'Heslo musí být nejméně '. Users::PASSWORD_MIN_LENGTH. ' znaků dlouhé';
            }elseif( !preg_match($passwordValidation, $data['password'])){
                $data['passwordError'] = 'V hesle musí být alespoň jedno číslo';
            }

            //Validate confirm password
            if(empty($data['confirmPassword'])){
                $data['confirmPasswordError'] = 'Prosím napište potvrzovací heslo';
            }else {
                if($data['password'] != $data['confirmPassword']){
                    $data['confirmPasswordError'] = 'Hesla se neshodují, zkuste to prosím znovu';
                }
            }

            //Make sure that errors are empty
            if( empty($data['usernameError']) && empty($data['emailError'])
                && empty($data['passwordError'])  && empty($data['confirmPasswordError'])){
                //hashing password
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                //Register user from model function
                if($this->userModel->register($data)){
                    //Redirect to the login page
                    header('location: ' . URLROOT . '/users/login');
                }else{
                    die('Something went wrong.');
                }
            }
        }
        $this->view('users/register', $data);
    }

    /**
     * login user
     */
    public function login(){
        if(!empty($_SESSION['user'])){
            header("Location: ".URLROOT . "/users/index");
        }
        $data = [
            'usernameOrEmail' => '',
            'password' => '',
            'usernameOrEmailError' => '',
            'passwordError' => ''
        ];
        //Check for post
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //Sanitize post data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'usernameOrEmail' => trim($_POST['usernameOrEmail']),
                'password' => trim($_POST['password']),
                'usernameOrEmailError' => '',
                'passwordError' => ''
            ];
            //Validate usernameOrEmail
            if(empty($data['usernameOrEmail'])){
                $data['usernameOrEmailError'] = 'Please enter a username or email';
            }

            //Validate password
            if(empty($data['password'])){
                $data['passwordError'] = 'Please enter a password';
            }
            //Check if all errors are empty
            if(empty($data['usernameOrEmailError'])
                && empty($data['passwordError'])){
                $loggedInUser = $this->userModel->login($data['usernameOrEmail'], $data['password']);
                if($loggedInUser){
                    $this->createUserSession($loggedInUser);
                }else{
                    $data['passwordError'] = 'Uživatelské jméno nebo heslo bylo zadáno špatně. Zkuste to prosím znovu.';
                    $this->view('users/login', $data);
                }
            }
        }else{
            $this->view('users/login', $data);
        }
    }

    /**
     * Controller for managing users
     */
    public function manageUsers(){
        if(!isLoggedIn()){
            header("Location: ".URLROOT . "/users/login");
        }
        $users = $this->userModel->getAllUsers();
        $data = [
            'users' => $users
        ];
        $this->view('users/manageUsers', $data);
        /*
        if(strcmp($_SESSION['user']->role, 'superadmin') == 0 or strcmp($_SESSION['user']->role, 'admin') ==0){
            $users = $this->userModel->getAllUsers();
            $data = [
                'users' => $users
            ];
            $this->view('users/manageUsers', $data);
        }else{
            header("Location: ".URLROOT . "/users/index");
        }*/
    }

    /**
     * Create session
     * @param $user    user which is going to be login
     */
    private function createUserSession($user){
        $_SESSION['user'] = $user;
        header('location:' .URLROOT . '/pages/index');
    }

    /**
     * Changes data of the selected user
     * @param null $user_id id of the selected user
     */
    public function updateUserUsernameEmail($user_id = null){
        if(!isLoggedIn()){
            header('location: ' . URLROOT . '/users/login');
        }
        if($user_id == null){
            header('location: ' . URLROOT . '/users/index');
        }
        $user = $this->userModel->findUserById($user_id);

        if (!$user or !manipulateUserProfilePermissions($user)){
            header('location: ' . URLROOT . '/users/index');
        }

        $userPosts = $this->userModel->findUserPosts($user_id);
        $userReviews = $this->userModel->findUserReviews($user_id);

        $data = [
            'user' => $user,
            'userPosts' => $userPosts,
            'userReviews' => $userReviews,
            'username' => '',
            'email' => '',
            'usernameError' => '',
            'emailError'=> '',
            'message' => ''
        ];
        //REQUEST METHOD
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //Santize post data
            //severs problems with unwanted characters - PDO mechanic
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            //trim remove white space
            $data = [
                'id' => $user->id,
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'user' => $user,
                'userPosts' => $userPosts,
                'userReviews' => $userReviews,
                'usernameError' => '',
                'emailError'=> '',
                'message' => ''
            ];
            $nameValidation = "/^[a-zA-Z0-9]*$/";
            //Validate username on letters/numbers
            if(empty($data['username'])){
                $data['usernameError'] = 'Prosím uveďte uživatelské jméno';
            }elseif(!preg_match($nameValidation, $data['username'])){
                $data['usernameError'] = 'Jméno může obsahovat pouze číslice a písmena bez diakritiky.';
            }
            //Validate email
            if(strcmp($data['email'],$user->email) !=0){
                if(empty($data['email'])){
                    $data['emailError'] = 'Prosím uveďte emailovou adresu.';

                }elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                    $data['emailError'] = 'Prosím vložte správný formát emailu.';
                }else{
                    //check if email exists
                    if($this->userModel->userEmailAlreadyRegistered($data['email'])){
                        $data['emailError'] = 'Email je již zabraný.';
                    }
                }
            }

            //Make sure that errors are empty
            if( empty($data['usernameError']) && empty($data['emailError'])){
                //Update user username and email
                if($this->userModel->updateUserUsernameEmail($data)){
                    //Redirect to the user index page page
                    $data['message'] = "Změna se provedla úspěšně";
                    header('location: ' . URLROOT . '/users/index/'.$user_id);
                }else{
                    die('Something went wrong.');
                }
            }else{
                $this->view('users/index', $data);
            }
        }else{
            $this->view('users/index', $data);
        }
    }

    /**
     * Controller method of changing password of logg in user
     * @param $user_id
     */
    public function changePassword($user_id = null){
        if(!isLoggedIn()){
            header("Location: ".URLROOT . "/users/login");
        }
        if($user_id == null){
            header("Location: ".URLROOT . "/users/index");
        }
        $user = $this->userModel->findUserByID($user_id);
        if(!$user or !manipulateUserProfilePermissions($user)){
            header("Location: ".URLROOT . "/users/index");
        }
        //$user_id = $_SESSION['user']->id;
        $data = [
            'originalPassword' => '',
            'newPassword' => '',
            'confirmNewPassword' => '',
            'originalPasswordError' => '',
            'newPasswordError' => '',
            'confirmNewPasswordError' => ''
        ];
        //Check for post
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            //Sanitize post data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'id' => $user_id,
                'user' => $user,
                'originalPassword' => trim($_POST['originalPassword']),
                'newPassword' => trim($_POST['newPassword']),
                'confirmNewPassword' => trim($_POST['confirmNewPassword']),
                'originalPasswordError' => '',
                'newPasswordError' => '',
                'confirmNewPasswordError' => ''
            ];
            $passwordValidation = "/^(.{0,7}|[^a-z]*|[^\d]*)$/i";
            $hashedPassword = !empty($data['user']) ? $data['user']->password : '';
            if(empty($data['originalPassword'])){
                $data['originalPasswordError'] = 'Prosím uveďte své původní heslo';
            }else if(!password_verify($data['originalPassword'], $hashedPassword)){
                $data['originalPasswordError'] = 'Vaše původní heslo je zadané špatně.';
            }

            //Validate password on length and numeric values
            if(empty($data['newPassword'])){
                $data['newPasswordError'] = 'Prosím uveďte nové heslo.';
            }elseif ( strlen($data['newPassword']) < Users::PASSWORD_MIN_LENGTH ){
                $data['newPasswordError'] = 'Heslo musí být nejméně '. Users::PASSWORD_MIN_LENGTH. ' znaků dlouhé';
            }elseif( !preg_match($passwordValidation, $data['newPassword'])){
                $data['newPasswordError'] = 'V hesle musí být alespoň jedna číslice.';
            }

            //Validate confirm password
            if(empty($data['confirmNewPassword'])){
                $data['confirmNewPasswordError'] = 'Prosím vložte potvrzovací heslo.';
            }else {
                if($data['newPassword'] != $data['confirmNewPassword']){
                    $data['confirmNewPasswordError'] = 'Hesla se neshodují, zkuste to prosím znovu.';
                }
            }

            //Make sure that errors are empty
            if( empty($data['confirmNewPasswordError']) && empty($data['originalPasswordError'])
            && empty($data['newPasswordError'])){
                //hashing password
                $data['newPassword'] = password_hash($data['newPassword'], PASSWORD_DEFAULT);
                if($this->userModel->changePassword($data)){
                    header('location: ' . URLROOT . '/users/manageUsers');
                }else{
                    die('Something went wrong.');
                }
            }else{
                $this->view('users/changePassword', $data);
            }
        }else{
            $this->view('users/changePassword', $data);
        }
    }

    /**
     * Controller to view resetPassword
     * - makes available services to change user
     * password - this controller specifically check
     * email provided by user and it it is found
     * in database - add to table for passwords reset
     * new record and send user email with information
     * how to change mail
     */
    public function resetPassword(){
        if(isLoggedIn()){
            header("Location: ".URLROOT . "/users/index");
        }
        $data =[
          'email' => '',
          'emailError' => ''
        ];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $data = [
                'email' => $email,
                'emailError' => ''
            ];

            if(empty($data['email'])){
                $data['emailError'] = "Nebyl zadán email";
            }else{
                $user = $this->userModel->findUserByEmail($email);
                if(!$user){
                    $data['emailError'] = "Pro zadaný email neexistuje uživatelský účet.";
                }
            }

            if(empty($data['emailError'])){
                // tutorial - https://www.youtube.com/watch?v=wUkKCMEYj9M
                $selector = bin2hex(random_bytes(8));
                $token = random_bytes(32);
                //link which is going to be send to user
                $url = URLROOT . '/users/createNewPassword?selector='.$selector.'&validator='.bin2hex($token);
                $expires = date("U") + 1800; //one hour from now
                $data['token'] = $token;
                $data['selector'] = $selector;
                $data['expires'] = $expires;
                if($this->userModel->passwordResetAppeal($data)){

                    $to = $email;
                    $subject = 'Restartování heslo pro webovou konferenci';
                    $message ='<p>Dostali jsme žádost o změnu hesla. Odkaz pro resetování hesla je níže. Pokud jste žádost neprovedli, tento email ignorujte.</p>';
                    $message .='<br>Tady odkaz pro resetování hesla:</br>';
                    $message .='<a href="'.$url.'">'.$url.'</a></p>';
                    $headers = "From: : vondrp@students.zcu.cz\r\n";
                    $headers .= "Reply-To: vondrp@students.zcu.cz\r\n";
                    $headers .= "Content-type: text/html\r\n";

                    mail($to, $subject, $message, $headers);
                    $data['success'] = 'Zkontrolujte si e-mail';
                    $this->view('users/resetPassword', $data);
                }else{
                    die('Something went wrong.');
                }
            }else{
                $this->view('users/resetPassword', $data);
            }
        }else{
            $this->view('users/resetPassword', $data);
        }

    }

    /**
     * From a link with selector and validator
     * which user got to his email by resetPassword
     * user can on this site change his password
     */
    public function createNewPassword(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //Sanitize post data                                             
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $currentDate = date("U");
            $data = [
                'selector' => $_POST['selector'],
                'validator' => $_POST['validator'],
                'password' => trim($_POST['password']),
                'passwordRepeat' => trim($_POST['password-repeat']),
                'passwordError' => '',
                'passwordRepeatError' => '',
                'currentDate' => $currentDate
            ];
            $passwordValidation = "/^(.{0,7}|[^a-z]*|[^\d]*)$/i";
            //Validate password on length and numeric values
            if(empty($data['password'])){
                $data['passwordError'] = 'Prosím uveďte heslo.';
            }elseif ( strlen($data['password']) < Users::PASSWORD_MIN_LENGTH ){
                $data['passwordError'] = 'Heslo musí být nejméně '. Users::PASSWORD_MIN_LENGTH. ' znaků dlouhé';
            }elseif( !preg_match($passwordValidation, $data['password'])){
                $data['passwordError'] = 'V hesle musí být alespoň jedno číslo';
            }

            //Validate confirm password
            if(empty($data['passwordRepeat'])){
                $data['passwordRepeatError'] = 'Prosím uveďte potvrzovací heslo.';
            }else {
                if($data['password'] != $data['passwordRepeat']){
                    $data['passwordRepeatError'] = 'Hesla se neshodují, zkuste to prosím znovu.';
                }
            }
            if(empty($data['passwordError']) && empty($data['passwordRepeatError'])){
                $result = $this->userModel->createNewPassword($data);
                if(strcmp($result, "OK")==0){
                    header("Location: ".URLROOT . "/users/login");
                }else{
                    die('Something went wrong. Something: '.$result.' '.$data['token']);
                }
            }else{
                $this->view('users/createNewPassword', $data);
            }
        }else{
            $data = [
                'selector' => '',
                'validator' => '',
                'message' => ''
            ];
            $selector = $_GET["selector"];
            $validator= $_GET["validator"];

            if(empty($selector) or empty($validator)){
                $data['message'] = 'Váš požadavek nelze ověřit';
            }else if(ctype_xdigit($selector) === false && ctype_xdigit($validator) === false){
                //check is hexadecimal  token are infact hexadecimal tokens
                $data['message'] = 'Nastala chyba při ověřování selectoru a tokenu z url adresy.';
            }else{
                $data['validator'] = $validator;
                $data['selector'] = $selector;
            }
            $this->view('users/createNewPassword', $data);
        }
    }

    /**
     * Change user role
     * @param $user_id  id of the user, which role is changing
     */
    public function changeRole($user_id = null){
        if($user_id == null or !isLoggedIn()){
            header("Location: ".URLROOT . "/users/index");
        }
        $user = $this->userModel->findUserByID($user_id);
        if(!$user or !manipulateUserProfilePermissions($user)){
            header("Location: ".URLROOT . "/users/index");
        }
        $data = [
            'user_id' => $user_id,
            'newRole' => '',
            'newRoleError'=> ''
        ];
        if(strcmp($_SESSION['user']->role, 'admin') ==0 or strcmp($_SESSION['user']->role, 'superadmin') == 0){

            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $_POST = filter_input_array(INPUT_POST);
                $data = [
                    'user_id' => $user_id,
                    'newRole' => trim($_POST['role']),
                    'newRoleError' => '',
                ];

                if(empty($data['newRole'])){
                    $data['newRoleError'] = 'Nebyla zvolena žádná role';
                }

                if(strcmp($data['newRole'], 'superadmin') == 0 and strcmp($_SESSION['user']->role, 'admin')==0){
                    $data['newRoleError'] = 'Nebyla zvolena žádná role';
                }

                if(strcmp($data['newRole'], $user->role) == 0){
                    $data['newRoleError'] = 'Nezměněná role';
                }

                if(empty($data['newRoleError'])){
                    if($this->userModel->changeUserRole($data)){
                        header("Location:". URLROOT ."/users/manageUsers");
                    }else{
                        die("Something went wrong, please try again!");
                    }
                }else{
                    $this->view('users/manageUsers', $data);
                }

            }else{
                $this->view('users/manageUsers', $data);
            }
        }else{
            $data['newRoleError'] = 'Pro změnu role uživatele nemáte dostatečná oprávnění.';
            $this->view('users/manageUsers', $data);
        }
    }

    /**
     * Delete user from database
     * @param null $user_id
     */
    public function deleteUser($user_id = null){
        if(!isLoggedIn()){
            header("Location: ". URLROOT . "/users/login");
        }
        if($user_id == null){
            header("Location: ". URLROOT . "/users/index");
        }
        $user = $user = $this->userModel->findUserByID($user_id);
        if (!$user or !manipulateUserProfilePermissions($user)){
            header('location: ' . URLROOT . '/users/index');
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->userModel->deleteUser($user_id)){
                header("Location: ". URLROOT ."/users/index");
            }else{
                die('Something went wrong');
            }
        }
    }
    /**
     * unset all session parameters and redirect to header
     */
    public function logout(){
        unset($_SESSION['user']);
        header('location: ' . URLROOT .'/users/login');
    }
}