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
    public function getUsers(){
        $this->db->query("SELECT * FROM users");
        $result = $this->db->resultSet();

        return $result;
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
     * @param $password     password
     * @return false if login action failed
     */
    public function login($usernameOrEmail, $password){
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
     * Find user by email, Email is passsed in by the Controller
     * @param $email    email address we are looking for
     */
    public function findUserByEmail($email){
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
}
