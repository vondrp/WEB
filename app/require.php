<?php
    require_once 'config/config.php';

    //Require libraries from folder libraries
    require 'libraries/twigTS/vendor/autoload.php';
    require_once 'libraries/Core.class.php';
    require_once 'libraries/Controller.class.php';
    require_once 'libraries/Database.class.php';

    //Instantiate core class
    $init = new Core();