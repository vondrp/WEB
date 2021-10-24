<?php

/**
 * Class Pages is controller of pages view
 */
class Pages extends Controller {

    public function __construct(){
        // Really nothing
    }

    public function index(){
        $data = [
            'title' => 'Home page',
        ];
        $this->view('pages/index', $data);
    }
}