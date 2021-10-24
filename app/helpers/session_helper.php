<?php
    session_start();

    /**
     * Find out if user is logged in
     * @return bool     true - user logged in, otherwise false
     */
    function isLoggedIn(){
        if(isset($_SESSION['user'])){
            return true;
        }else{
            return false;
        }
    }
?>