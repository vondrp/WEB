<?php

/**
 * User model
 */
class User{

    /**
     * @var Database database connection
     */
    private $db;

    /**
     * User constructor.
     * connect to the database
     */
    public function __construct(){
        $this->db = new Database();
    }

    /**
     * Return all users from database
     * @return mixed    all from table users
     */
    public function getAllUsers(){
        $this->db->query("SELECT * FROM users ORDER BY role ASC ");
        return $this->db->resultSet();
    }

    /**
     * Register user by adding him to the database table users
     * @param $data an array with username, email, password
     * @return true - if everything went rigth, false - if problem occure
     */
    public function register($data){
        $this->db->query('INSERT INTO users (username, email, password) VALUES
        (:username,:email, :password)');
        //Bind values
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);

        //Execute function
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Login method
     * @param $usernameOrEmail     username or email
     * @param $password     password    of the user
     * @return false if login action failed
     */
    public function login($usernameOrEmail, $password){
        //LEFT JOIN roles
        $this->db->query('SELECT * FROM users WHERE username = :usernameOrEmail OR email= :usernameOrEmail');
        //Bind value
        $this->db->bind(':usernameOrEmail', $usernameOrEmail);

        $row = $this->db->single();
        //$hashedPassword = $row->password;
        $hashedPassword = !empty($row) ? $row->password : '';

        if(password_verify($password, $hashedPassword)){
            return $row;
        }else{
            return false;
        }
    }

    /**
     * Find if email is already registered
     * @param $email    email address we are looking for
     */
    public function userEmailAlreadyRegistered($email){
        //Prepare statement
        $this->db->query('SELECT * FROM users WHERE email = :email');
        //Email param will be binded with the email variable
        $this->db->bind(':email', $email);
        //Check if email is already registered
        if($this->db->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Find user by his/her id
     * @param $user_id  id of the user
     * @return false|mixed  return false if user was no find, otherwise return user record
     */
    public function findUserByID($user_id){
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $user_id);
        $row = $this->db->single();
        if(!empty($row)){
            return $row;
        }else{
            return false;
        }
    }

    /**
     * Find user by his email
     * @param $email        email of the user we are looking for
     * @return false|mixed  return user data, when user nor found return false
     */
    public function findUserByEmail($email){
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        if(!empty($row)){
            return $row;
        }else{
            return false;
        }
    }

    /**
     * Find all posts of the user
     * @param $user_id     user which posts we are looking for
     * @return mixed    all posts, which user created
     */
    public function findUserPosts($user_id){
       // $user_id = $user->id;
        $this->db->query('SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC');

        $this->db->bind(':user_id', $user_id);

        return $this->db->resultSet();
    }

    /**
     * Find all reviews of the user
     * @param $user_id     user which reviews we are looking for
     * @return mixed    all reviews, which user created
     */
    public function findUserReviews($user_id){
        //$user_id = $user->id;
        $this->db->query('SELECT * FROM reviews WHERE user_id = :user_id 
    AND recommendation != 0 ORDER BY created_at DESC');
        $this->db->bind(':user_id', $user_id);

        $results = $this->db->resultSet();

        foreach ($results as $record) {
            $record->post = $this->findPostById($record->post_id);
        }
        return $results;
    }

    public function findUserUndoneReviews($user_id){
        //$user_id = $user->id;
        $this->db->query('SELECT * FROM reviews WHERE user_id = :user_id AND recommendation = 0
        AND topicRelevance = 0 AND langQuality = 0 AND originality = 0 ORDER BY created_at DESC');
        $this->db->bind(':user_id', $user_id);

        $results = $this->db->resultSet();

        foreach ($results as $record) {
            $record->post = $this->findPostById($record->post_id);
        }
        return $results;
    }

    /**
     * Find post in table posts with his id
     * @param $id       id of the post, which is being looked for
     * @return mixed    data of post with right id
     */
    private function findPostById($id){
        $this->db->query('SELECT * FROM posts WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Update/change user data
     * @param $data     updated data of the user
     * @return bool     true - if update succeeded, otherwise return false
     */
    public function updateUserUsernameEmail($data){
        $this->db->query('UPDATE users SET username = :username, email = :email WHERE id = :id');

        $this->db->bind(':id',$data['id']);
        $this->db->bind(':username',$data['username']);
        $this->db->bind(':email',$data['email']);

        if($this->db->execute()){
            if($_SESSION['user']->id == $data['id']){
                $user = $this->findUserByID($data['id']);
                $_SESSION['user'] = $user;
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * Change user password
     * @param $data     update data
     * @return bool     true - if update succeeded, otherwise return false
     */
    public function changePassword($data){
            $this->db->query('UPDATE users SET password = :password WHERE id = :id');

            $this->db->bind(':id', $data['id']);
            $this->db->bind(':password', $data['newPassword']);
            if ($this->db->execute()) {
                return true;
            } else {
                return false;
            }
    }

    /**
     * processes database part of the password reset request
     * @param $data     email, token, selector, expires
     * @return bool     true - successful, otherwise false
     */
    public function passwordResetAppeal($data){
        $this->db->query('DELETE FROM pwdReset WHERE pwdResetEmail = :email');
        $this->db->bind(':email', $data['email']);
        $this->db->execute();

            $this->db->query('INSERT INTO pwdReset (pwdResetEmail, pwdResetSelector, pwdResetToken, pwdResetExpires) 
                VALUES (:email, :selector, :token, :expires)');

            $hashedToken = password_hash($data['token'], PASSWORD_DEFAULT);

            //Bindvalues
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':selector', $data['selector']);
            $this->db->bind(':token', $hashedToken);
            $this->db->bind(':expires', $data['expires']);
            //Execute function
            if($this->db->execute()){
                return true;
            }else{
                return false;
            }
    }

    /**
     * Change forgotten password
     * @param $data     selector, currentDate, validator, password
     * @return string   infromation if OK or some specific error
     */
    public function createNewPassword($data){
        $this->db->query("SELECT * FROM pwdReset WHERE pwdResetSelector = :pwdResetSelector AND pwdResetExpires >= :pwdResetExpires");

        $this->db->bind(':pwdResetSelector', $data['selector']);
        $this->db->bind(':pwdResetExpires', $data['currentDate']);

        $row= $this->db->single();
        if(!empty($row)){
            $tokenBin = hex2bin($data['validator']);
            $tokenCheck = password_verify($tokenBin, $row->pwdResetToken);
            if(!$tokenCheck){
                //return false;
                return "Token validace ".$tokenCheck;
            }else /*if($tokenCheck === true)*/{
                $tokenEmail = $row->pwdResetEmail;
                $this->db->query("SELECT * FROM users WHERE email = :pwdResetEmail");

                $this->db->bind(':pwdResetEmail', $tokenEmail);
                $user= $this->db->single();
                if(!empty($user)){
                    $newPwdHash = password_hash($data['password'], PASSWORD_DEFAULT);

                    $this->db->query("UPDATE users SET password = :pwdResetPassword WHERE email = :pwdResetEmail");

                    $this->db->bind(':pwdResetPassword', $newPwdHash);
                    $this->db->bind(':pwdResetEmail', $tokenEmail);

                    if($this->db->execute()){
                        $this->db->query('DELETE FROM pwdReset WHERE pwdResetEmail = :email');
                        $this->db->bind(':email', $tokenEmail);
                        if($this->db->execute()){
                            return "OK";
                        }else{
                            return "Vymazani z pwdReset";//false;
                        }
                    }else{
                        return "Zmena hesla v users."; //false;
                    }
                }else{
                    //return false;
                    return "Nenalezen v users";
                }
            }
        }else{
            //return false;
            return "Nenalezen v pdwReset";
        }
    }

    /**
     * Delete user
     * @param $user_id  user id
     * @return bool     true - action success, otherwise return false
     */
    public function deleteUser($user_id){
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $user_id);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Update user role
     * @param $data
     * @return bool    true - if update succeeded, otherwise return false
     */
    public function changeUserRole($data){
        $this->db->query('UPDATE users SET role = :newRole WHERE id = :id');
        $this->db->bind(':newRole', $data['newRole']);
        $this->db->bind('id', $data['user_id']);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }
}
