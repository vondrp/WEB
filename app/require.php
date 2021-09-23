<?php
    require_once 'config/config.php';
    require_once 'helpers/session_helper.php';
    require_once 'helpers/permissions_helper.php';
    //Require libraries from folder libraries
    //require_once 'libraries/twigTS/vendor/autoload.php';
    require_once 'libraries/twigExtension/vendor/autoload.php';
    require_once 'libraries/Core.class.php';
    require_once 'libraries/Controller.class.php';
    require_once 'libraries/Database.class.php';

    //Instantiate core class
    $init = new Core();