<?php
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
        $twig = $this->loadTwig();
        $twig->addGlobal('session', $_SESSION);

        if(file_exists('../app/views/' . $view . '.html.twig')){
            //require_once '../app/views/' . $view . '.html.twig';
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
