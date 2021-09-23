<?php

    function editorPermissions(){
        if(isLoggedIn() and ($_SESSION['user']->role == 'editor' or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    function manipulatePostPermission($post){
        if(isLoggedIn() and ($post->user_id == $_SESSION['user_id'] or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    function reviewerPermissions(){
        if(isLoggedIn() and ($_SESSION['user']->role == 'reviewer' or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }

    function adminPermissions(){
        if(isLoggedIn() and ($_SESSION['user']->role == 'admin' or $_SESSION['user']->role == 'superadmin')){
            return true;
        } else{
            return false;
        }
    }
