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
            'description' =>'',
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
                'description' => trim($_POST['description']),
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
                && empty($data['contentError'])
                && empty($data['descriptionError'])){
                if($this->postModel->addPost($data)){
                    header("Location:". URLROOT ."/posts");
                }else{
                    die("Something went wrong, please try again!");
                }
            }else{
                $this->view('posts/create', $data);
            }

        }else{
            $this->view('posts/create', $data);
        }
    }

    /**
     * Controller of the post update
     * checks updated data
     * @param $id   updated post id
     */
    public function update($id){
        $post = $this->postModel->findPostById($id);
        if(!isLoggedIn() or ($post->user_id != $_SESSION['user_id']) ){
            header("Location: ". URLROOT . "/posts");
        }
        $data = [
            'post' => $post,
            'title' => '',
            'content' => '',
            'description' => '',
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
                'description' => trim($_POST['description']),
                'titleError' => '',
                'contentError' => ''
            ];

            if(empty($data['title'])){
                $data['titleError'] = 'The title of a post cannot be empty';
            }

            if(empty($data['content'])){
                $data['contentError'] = 'The content of a post cannot be empty';
            }

            if(($data['title'] == $this->postModel->findPostById($id)->title)
            && ($data['content'] == $this->postModel->findPostById($id)->content) ){

                $data['contentError'] = 'Nothing has been changed';
                $data['titleError'] = 'At least change the title!';
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

        }else{
            $this->view('posts/update', $data);
        }
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

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->postModel->deletePost($id)){
                header("Location: ". URLROOT ."/posts");
            }else{
                die('Something went wrong');
            }
        }
    }

    /**
     * Controller of showing one specific post
     * @param $post_id  id of the showed post
     */
    public function show($post_id){
        $post =  $this->postModel->findPostById($post_id);
        if(!$post){
            header("Location: ". URLROOT . "/posts");
        }
        $comments = $this->postModel->findPostComments($post_id);
        $reviews = $this->postModel->findPostReviews($post_id);


        $data = [
            'post' => $post,
            'comments'=> $comments,
            'reviews' => $reviews
        ];
        $this->view('posts/show', $data);
    }

    //*******************
    //
    //  COMMENTS METHODS
    //
    //*******************

    /**
     * Create comment
     * @param $post_id  id of the post to which is comment related
     */
    public function createComment($post_id){
        if(!isLoggedIn()){
            header("Location: ".URLROOT ."/posts/show/".$post_id);
        }
        $post =  $this->postModel->findPostById($post_id);
        $comments = $this->postModel->findPostComments($post_id);
        $reviews = $this->postModel->findPostReviews($post_id);
        $data = [
            'post' => $post,
            'comments'=> $comments,
            'reviews' => $reviews,
            'content' => '',
            'contentError' => ''
         ];
        /*
        $data = [
            'content' => '',
            'contentError' => ''
        ];*/

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'post_id' => $post_id,
                'author' => $_SESSION['username'],
                'content' => trim($_POST['content']),
                'contentError' => '',

                'post' => $post,
                'comments'=> $comments,
                'reviews' => $reviews
            ];

            if(empty($data['content'])){
                $data['contentError'] = 'The content of a comment cannot be empty';
            }

            if(empty($data['contentError']) ){
                if($this->postModel->addComment($data)){
                    header("Location:". URLROOT ."/posts/show/".$post_id);
                }else{
                    die("Something went wrong, please try again!");
                }
            }
        }
        $this->view('posts/show', $data);
    }

    /**
     * Controller method of post deleting
     * @param $id   id of the deleted comment
     */
    public function deleteComment($id){
        $comment = $this->postModel->findCommentById($id);
        if(!isLoggedIn()){
            header("Location: ". URLROOT ."/posts/show/".$comment->post_id);
        }elseif($comment->author != $_SESSION['username']){
            header("Location: ". URLROOT . "/posts");
        }
        $data = [
            'comment' => $comment,
            'content' => '',
            'contentError' => ''
        ];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->postModel->deleteComment($id)){
                header("Location: ". URLROOT ."/posts/show/".$comment->post_id);
            }else{
                die('Something went wrong');
            }
        }
    }
}
