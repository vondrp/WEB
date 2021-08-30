<?php

/**
 * Class Database
 */
class Database{
    private $dbHost = DB_HOST;
    private $dbUser = DB_USER;
    private $dbPass = DB_PASS;
    private $dbName = DB_NAME;
    /**
     * @var statement  preparing a statement
     */
    private $statement;

    /**
     * @var $dbHandler  dbHandler whenever prepare a statement - use this statement
     */
    private $dbHandler;
    /**
     * @var error handler
     */
    private $error;

    /**
     * Database constructor.
     * run connection whenever datase file is called
     */
    public function __construct(){
        $conn = 'mysql:host=' . $this->dbHost . ';dbname=' .$this->dbName;
        $options = array(
            PDO::ATTR_PERSISTENT => true, /* attribute persistence, preventing driver crashing
                             and giving timeouts, when attempt to connect to database
                            + check if already connection with database */
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        );
        try{
            $this->dbHandler = new PDO($conn, $this->dbUser, $this->dbPass, $options);
        }catch(PDOException $e){
            $this->error = $e->getMessage();
            echo $this->error;
        }
        //$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    //Allows to write queries
    public function query($sql){
        $this->statement = $this->dbHandler->prepare($sql);
    }

    /**
     * Bind values
     * @param $parameter    parameter
     * @param $value        value
     * @param null $type    type of value
     */
    public function bind($parameter, $value, $type = null){
        switch (is_null($type)){
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
                break;
            default:
                $type = PDO::PARAM_STR;
        }
        $this->statement->bindValue($parameter,$value,$type);
    }

    /**
     * Execute statement (call PDO function execute)
     * @return mixed   execute prepared statement
     */
    public function execute(){
        return $this->statement->execute();
    }
    /**
     * Return an array
     * @return mixed    fetchAll object
     */
    public function resultSet(){
        $this->execute();
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Return a specific row as an object
     * @return mixed    fetch object
     */
    public function single(){
        $this->execute();
        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get the row count
     * @return mixed    the row count
     */
    public function rowCount(){
        $this->execute();
        return $this->statement->rowCount(); //use when query is upgraded
    }

}
