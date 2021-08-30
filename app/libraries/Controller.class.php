<?php

//Load the model and the view

/**
 * Class Controller contains methods
 * to load the model and the view
 */
class Controller{

    /**
     * Load the model
     * @param $model   name of the model
     * @return mixed    Instantiate model
     */
    public function model($model){
        require_once  '../app/models/' . $model. '.class.php';
        //Instantiate model
        return new $model();
    }

    /**
     * Load the view {check for the file}
     * @param $view        view file we want to load
     * @param array $data   dynamic data which are going to be passed into the view
     */
    public function view($view, $data = []){
        if(file_exists('../app/views/' . $view . '.php')){
            require_once '../app/views/' . $view . '.php';
        }else{
            die("View does not exists");
        }
    }
}
