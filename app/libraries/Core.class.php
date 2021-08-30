<?php

/**
 * Class Core is core class of the web
 * it takes care of works with URL addresses
 * and calling of Controllers
 */
class Core{
    /**
     * @var string if there no other controllers this page
     * will be automatically loaded
     */
    protected $currentController = 'Pages';
    /**
     * @var string inside of controller will
     * load the $currentMethod method
     */
    protected $currentMethod = 'index';
    /**
     * @var array default view parameters array
     */
    protected $params = [];

    /**
     * Core constructor.
     */
    public function __construct(){
        $url = $this->getUrl();
        if($url !=NULL){
            /* ucwords - concatenate function - capitalize first letter */
            if(file_exists('../app/controllers/' .ucwords($url[0]) . 'php')){
                /* Will set a new controller */
                $this->currentController = ucwords($url[0]);
                unset($url[0]);
            }
        }
        /*Require the controller */
        require_once  '../app/controllers/' . $this->currentController . '.class.php';
        $this->currentController = new $this->currentController;
        //Check for second part of URL
        if(isset($url[1])) {
            if(method_exists($this->currentController, $url[1])){
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }
        //Get parameters
        $this->params = $url ? array_values($url) : [];
        //Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod],$this->params);
    }

    /**
     * method will fetch the URL
     * @return mixed    url address
     */
    public function getUrl(){
        if(isset($_GET['url'])){
            $url = rtrim($_GET['url'], '/');
            // Allows to filer variable as string/number
            $url = filter_var($url, FILTER_SANITIZE_URL);
            //Breaking it into an array
            $url = explode('/', $url);
            return $url;
        }
    }
}
