<?php

/**
 * Class Users is controller of users
 */
class Users extends Controller{
    /**
     * Users constructor.
     */
    public function __construct(){
        $this->userModel = $this->model('User');
    }

    /**
     * register user
     */
    public function register(){
        $passwordMinLength = 6;

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
                if($this->userModel->findUserByEmail($data['email'])){
                    $data['emailError'] = 'Email is already taken';
                }
            }
            //Validate password on length and numeric values
            if(empty($data['password'])){
                $data['passwordError'] = 'Please enter password.';
            }elseif ( strlen($data['password']) < $passwordMinLength ){
                $data['passwordError'] = 'Password must be at least '. $passwordMinLength. ' characters';
            }elseif( !preg_match($passwordValidation, $data['password'])){
                $data['passwordError'] = 'Password must have at least one numeric value';
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
                    $data['passwordError'] = 'Password 
                    or username is in incorrect.
                    Please try again';
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
     * Create session
     * @param $user    user which is going to be login
     */
    public function createUserSession($user){
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] =$user->role;
        header('location:' .URLROOT . '/pages/index');
    }

    /**
     * unset all session parameters and redirect to header
     */
    public function logout(){
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['role']);
        header('location: ' . URLROOT .'/users/login');
    }
}