<?php

/**
 * Class Pages is controller of pages view
 */
class Pages extends Controller {

    public function __construct(){
        $this->userModel = $this->model('User');
    }

    public function index(){
        $users = $this->userModel->getUsers();
        $data = [
            'title' => 'Home page',
            'users' => $users
        ];

        $this->view('pages/index', $data);
    }

    public function about(){
        echo "About";
    }
}