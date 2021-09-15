<?php

class Comments extends Controller{
    /**
     * Comments constructor
     * arranged connection with the model
     */
    public function __construct(){
        $this->commentModel = $this->model('Comment');
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
        $data = [
            'content' => '',
            'contentError' => ''
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'post_id' => $post_id,
                'author' => $_SESSION['username'],
                'content' => trim($_POST['content']),
                'contentError' => ''
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
            }else{
                $this->view('posts/show/'.$post_id, $data);
            }

        }
        $this->view('posts/show/'.$post_id, $data);
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
