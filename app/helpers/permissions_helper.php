<?php
    /**
     * @return bool true - if user in session has all rights of the editor
     */
    function editorPermissions(){
        if(isLoggedIn() and ($_SESSION['user']->role == 'editor' or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    /**
     * @return bool true - if user has right to manipulate post
     */
    function manipulatePostPermission($post){
        if(isLoggedIn() and ($post->user_id == $_SESSION['user']->id or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    /**
     * @return bool true - if user in session has all rights of the reviewer
     */
    function reviewerPermissions(){
        if(isLoggedIn() and ($_SESSION['user']->role == 'reviewer' or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    /**
     * @return bool true - if user in session has all rights of an admin
     */
    function adminPermissions(){
        if(isLoggedIn() and ($_SESSION['user']->role == 'admin' or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    /**
     * Find out is user in session have permission to manipulate selected user
     * @param $user     $user which profile is going to be manipulated with
     * @return bool     true - have permission, false - not permission
     */
    function manipulateUserProfilePermissions($user){
        if(isLoggedIn() and ( ($_SESSION['user']->role == 'superadmin')
                or ($_SESSION['user']->role == 'admin' and $user->role !== 'superadmin')
                or $_SESSION['user']->id == $user->id) ){
            return true;
        } else{
            return false;
        }
    }
?>