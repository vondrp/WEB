<?php

/**
 * Class Posts is controller of the posts
 */
class Posts extends Controller{

    /**
     * Posts constructor
     * arranged connection with the model
     */
    public function __construct(){
        $this->postModel = $this->model('Post');
    }

    /**
     * Controller of the index view
     */
    public function index(){
        $posts = $this->postModel->findAllPosts();
        $data = [
          'posts' => $posts,
        ];
        $this->view('posts/index', $data);
    }

    /**
     * Controller of the create view
     * checks data of the new post provided by the user
     * before sending them to model
     */
    public function create(){
        if(!isLoggedIn()){
            header("Location: ".URLROOT . "/posts");
        }
        $data = [
            'title' => '',
            'content' => '',
            'titleError' => '',
            'contentError' => ''
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'user_id' => $_SESSION['user_id'],
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'titleError' => '',
                'contentError' => ''
            ];

            if(empty($data['title'])){
                $data['titleError'] = 'The title of a post cannot be empty';
            }

            if(empty($data['content'])){
                $data['contentError'] = 'The content of a post cannot be empty';
            }

            if(empty($data['titleError'])
                && empty($data['contentError'])){
                if($this->postModel->addPost($data)){
                    header("Location:". URLROOT ."/posts");
                }else{
                    die("Something went wrong, please try again!");
                }
            }else{
                $this->view('posts/create', $data);
            }

        }
        $this->view('posts/create', $data);
    }

    /**
     * Controller of the post update
     * checks updated data
     * @param $id   updated post id
     */
    public function update($id){
        $post = $this->postModel->findPostById($id);
        if(!isLoggedIn()){
            header("Location: ". URLROOT . "/posts");
        }elseif($post->user_id != $_SESSION['user_id']){
            header("Location: ". URLROOT . "/posts");
        }
        $data = [
            'post' => $post,
            'title' => '',
            'content' => '',
            'titleError' =>'',
            'contentError' => ''
        ];
        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'post' => $post,
                'user_id' => $_SESSION['user_id'],
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'titleError' => '',
                'contentError' => ''
            ];

            if(empty($data['title'])){
                $data['titleError'] = 'The title of a post cannot be empty';
            }

            if(empty($data['content'])){
                $data['contentError'] = 'The content of a post cannot be empty';
            }

            if($data['title'] == $this->postModel->findPostById($id)->title){
                $data['titleError'] == 'At least change the title!';
            }

            if($data['content'] == $this->postModel->findPostById($id)->content){
                $data['contentError'] == 'At least change the body!';
            }

            if(empty($data['titleError'])
                && empty($data['contentError'])){
                if($this->postModel->updatePost($data)){
                    header("Location:". URLROOT ."/posts");
                }else{
                    die("Something went wrong, please try again!");
                }
            }else{
                $this->view('posts/update', $data);
            }

        }
        $this->view('posts/update', $data);

    }

    /**
     * Controller method of post deleting
     * @param $id   id of the deleted post
     */
    public function delete($id){
        $post = $this->postModel->findPostById($id);
        if(!isLoggedIn()){
            header("Location: ". URLROOT . "/posts");
        }elseif($post->user_id != $_SESSION['user_id']){
            header("Location: ". URLROOT . "/posts");
        }
        $data = [
            'post' => $post,
            'title' => '',
            'content' => '',
            'titleError' =>'',
            'contentError' => ''
        ];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->postModel->deletePost($id)){
                header("Location: ". URLROOT ."/posts");
            }else{
                die('Something went wrong');
            }
        }
    }

}
