<?php

//Load the model and the view

/**
 * Class Controller contains methods
 * to load the model and the view
 */
class Controller{

    protected $twig;

    private function __construct(){
        $this->twig = $this->loadTwig();
    }
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
        if(file_exists('../app/views/' . $view . '.html.twig')){
            //require_once '../app/views/' . $view . '.html.twig';
           $twig = $this->loadTwig();
           $twig->display($view.".html.twig", $data);
        }else{
            die("View does not exists");
        }
    }

    /**
     * Load twig
     * @return \Twig\Environment    initialized twig
     */
    public function loadTwig(){
        $loader = new \Twig\Loader\FilesystemLoader('../app/views');
        $twig = new Twig\Environment($loader);

        $md5Filter = new  \Twig\TwigFilter('md5', function ($string){
            return md5($string);
        });

        $twig->addFilter($md5Filter);
        return $twig;
    }
}
