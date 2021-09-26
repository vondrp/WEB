<?php

/**
 * Class Users - controller of users
 */
class Users extends Controller{

    const PASSWORD_MIN_LENGTH = 6;
    /**
     * Users constructor.
     */
    public function __construct(){
        $this->userModel = $this->model('User');
    }

    /**
     *
     */
    public function index(){
        if(!isLoggedIn()){
            header("Location: ".URLROOT . "/users/login");
        }

        $userPosts = $this->userModel->findUserPosts($_SESSION['user']);
        $users = $this->userModel->getAllUsers();
        $data = [
            'userPosts' => $userPosts,
            'users' => $users
        ];
        $this->view('users/index', $data);
    }
    /**
     * register user
     */
    public function register(){

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
                $data['confirmPasswordError'] = 'Please enter password.';
            }else {
                if($data['password'] != $data['confirmPassword']){
                    $data['confirmPasswordError'] = 'Passwords do not match, please try again';
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

        $data = [
            'title' => 'Login page',
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
                        //'Password or username is in incorrect.Please try again';
                   // $this->view('users/login', $data);
                }
            }
        }else{
            $data = [
                'usernameOrEmail' => '',
                'password' => '',
                'usernameOrEmailError'=> '',
                'passwordError' => ''
            ];
        }
        $this->view('users/login', $data);
    }

    /**
     * Controller for managing users
     */
    public function manageUsers(){
        if(!isLoggedIn()){
            header("Location: ".URLROOT . "/users/login");
        }
        if(strcmp($_SESSION['user']->role, 'superadmin') ==0 or strcmp($_SESSION['user']->role, 'admin') ==0){
            $users = $this->userModel->getAllUsers();
            $data = [
                'users' => $users
            ];
            $this->view('users/manageUsers', $data);
        }else{
            header("Location: ".URLROOT . "/users/index");
        }
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
        if($user_id == null){
            header('location: ' . URLROOT . '/users/index');
        }
        $user = $this->userModel->findUserById($user_id);
        if(!$user){
            header('location: ' . URLROOT . '/users/index');
        }
        $data = [
            'username' => '',
            'email' => '',
            'usernameError' => '',
            'emailError'=> '',
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
                'usernameError' => '',
                'emailError'=> '',
            ];
            $nameValidation = "/^[a-zA-Z0-9]*$/";
            //Validate username on letters/numbers
            if(empty($data['username'])){
                $data['usernameError'] = 'Please enter username';
            }elseif(!preg_match($nameValidation, $data['username'])){
                $data['usernameError'] = 'Name can only contain letters and numbers';
            }
            //Validate email
            if(strcmp($data['email'],$user->email) !=0){
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
            }

            //Make sure that errors are empty
            if( empty($data['usernameError']) && empty($data['emailError'])){
                //Update user username and email
                if($this->userModel->updateUserUsernameEmail($data)){
                    //Redirect to the user index page page
                    header('location: ' . URLROOT . '/users/index');
                }else{
                    die('Something went wrong.');
                }
            }
        }
        $this->view('users/index', $data);
    }

    /**
     * Controller method of changing password of logg in user
     */
    public function changePassword(){
        if(!isLoggedIn()){
            header("Location: ".URLROOT . "/users/login");
        }
        $user_id = $_SESSION['user']->id;
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
                'user' => $_SESSION['user'],
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
                $data['originalPasswordError'] = 'Prosím uveďte své původní heslo';
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
                    $data['confirmNewPasswordError'] = 'Hesla se neschodují, zkuste to prosím znovu.';
                }
            }

            //Make sure that errors are empty
            if( empty($data['confirmNewPasswordError']) && empty($data['originalPasswordError'])
            && empty($data['newPasswordError'])){

                //hashing password
                $data['newPassword'] = password_hash($data['newPassword'], PASSWORD_DEFAULT);
                if($this->userModel->changePassword($data)){
                    //Redirect to the user index page
                    header('location: ' . URLROOT . '/users/index');
                }else{
                    die('Something went wrong.');
                }
            }
        }
        $this->view('users/changePassword', $data);
    }

    /**
     * @param $user_id  id of the user, which role is changing
     */
    public function changeRole($user_id = null){
        if($user_id == null or !isLoggedIn()){
            header("Location: ".URLROOT . "/users/index");
        }
        $user = $this->userModel->findUserByID($user_id);
        if($user == false){
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

            }
        }else{
            $data['newRoleError'] = 'Pro změnu role uživatele nemáte dostatečná oprávnění.';
            $this->view('users/manageUsers', $data);
        }
    }

    /**
     * @param null $user_id
     */
    public function deleteUser($user_id = null){
        if($user_id == null){
            header("Location: ". URLROOT . "/users/index");
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