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
     * show overview of published posts
     */
    public function index(){
        $posts = $this->postModel->findAllPublishedPosts();
        $data = [
          'posts' => $posts,
        ];
        $this->view('posts/index', $data);
    }

    /**
     * Controller of view with unpublished posts
     */
    public function unpublished(){
        if(!isLoggedIn() and strcmp($_SESSION['user']->role, 'normal')){
            header("Location: ".URLROOT . "/posts");
        }

        $posts = $this->postModel->findAllUnpublishedPosts();
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
        if(!editorPermissions()){
            header("Location: ".URLROOT . "/posts");
        }
        $data = [
            'title' => '',
            'content' => '',
            'fileName' => '',
            'description' =>'',
            'titleError' => '',
            'fileError' => '',
            'contentError' => ''
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //FILTER_SANITIZE_STRING - original second parameter - destroys html tags
            $_POST = filter_input_array(INPUT_POST);

            $file = $_FILES['fileToUpload'];
            $fileName = $_FILES['fileToUpload']['name'];
            $fileTmpName = $_FILES['fileToUpload']['tmp_name'];
            $fileError = $_FILES['fileToUpload']['error'];
            $fileSize = $_FILES['fileToUpload']['size'];

            $data = [
                'user_id' => $_SESSION['user']->id,
                'title' => trim($_POST['title']),
                'fileName' => $fileName,
                'content' => trim($_POST['content']),
                'description' => trim($_POST['description']),
                'titleError' => '',
                'fileError' =>'',
                'contentError' => ''
            ];


            if(empty($data['title'])){
                $data['titleError'] = 'The title of a post cannot be empty';
            }

            if(empty($data['content'])){
                $data['contentError'] = 'The content of a post cannot be empty';
            }

            if(!empty($data['fileName'])) {
                $fileExt = explode('.', $fileName);
                $fileActualExt = mb_strtolower(end($fileExt));
                $allowed = array('pdf');
                if (in_array($fileActualExt, $allowed)) {
                    if ($fileError === 0) {
                        if ($fileSize < 5000000) { //soubor je mensi nez 500mb + 0
                            $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                            $fileDestination = 'uploads/' . $fileNameNew;
                            $data['fileName'] = $fileNameNew;
                        } else {
                            $data['fileError'] = 'Soubor je příliš velký.';
                        }
                    } else {
                        $data['fileError'] = 'Při nahrávání souboru došlo k chybě.';
                    }
                } else {
                    $data['fileError'] = 'Špatný typ souboru!';
                }
            }else{
                $data['fileError'] = 'Nebyl vybrán žádný soubor';
            }
            if(empty($data['titleError'])
                && empty($data['contentError'])
                && empty($data['descriptionError'])
                && empty($data['fileError'])){
                    if($this->postModel->addPost($data)){
                        move_uploaded_file($fileTmpName,$fileDestination);
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
     * @param null $post_id   updated post id
     */
    public function update($post_id = null){
        // for case when no parameter is given
        if(empty($post_id)){
            header("Location: ". URLROOT . "/posts");
        }
        $post = $this->postModel->findPostById($post_id);
        if(!$post){ /* $post no find failure */
            header("Location: ". URLROOT . "/posts");
        }
        if(!manipulatePostPermission($post) ){
            header("Location: ". URLROOT . "/posts/show/".$post_id);
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
            $_POST = filter_input_array(INPUT_POST);

            $data = [
                'id' => $post_id,
                'post' => $post,
                'user_id' => $_SESSION['user']->id,
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'description' => trim($_POST['description']),
                'titleError' => '',
                'contentError' => ''
            ];


            if(empty($data['title'])){
                $data['titleError'] = 'Je třeba uvést nadpis.';
            }

            if(empty($data['content'])){
                $data['contentError'] = 'Článek nemůže být úplně prázdný.';
            }

            if(($data['title'] == $post->title)
            && ($data['content'] == $post->content)
            && ($data['description'] == $post->description)){

                $data['contentError'] = 'Nic nebylo změněno.';
                $data['titleError'] = 'Změntě aspoň například nadpis!';
                $data['descriptionError'] = 'Stačí změnit popis.';
            }

            if(empty($data['titleError'])
                && empty($data['contentError'])
                && empty($data['descriptionError'])){
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
     * Change status of post publish to opposite one to current one
     * @param null $post_id     id of the post
     */
    public function changePostPublishedStatus($post_id = null){
        if($post_id == null){
            header("Location: ".URLROOT."/posts");
        }
        $post = $this->postModel->findPostById($post_id);
        if(!$post){ /* $post no find failure */
            header("Location: ". URLROOT . "/posts");
        }

        if(!adminPermissions()){
            header("Location: ".URLROOT."/posts/show/".$post_id);
        }

        $data = [
            'post' => $post
        ];
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if($post->published == 1) {
                $published = 0;
            }else{
                $published = 1;
            }
            $data = [
                'post' => $post,
                'post_id' => $post_id,
                'published' => $published
            ];
            if($this->postModel->publishPost($data)){
                if($published = 1){
                    header("Location:". URLROOT ."/posts");
                }else{
                    header("Location:". URLROOT ."/posts/unpublished");
                }
            }else{
                die("Something went wrong, please try again!");
            }
        }
        $this->view('posts/show', $data);
    }

    /**
     * Change PDF file enclosed to the article
     * @param null $post_id     id of the post
     */
    public function changePostFile($post_id = null){
        if($post_id == null){
            header("Location: ".URLROOT."/posts");
        }
        $post = $this->postModel->findPostById($post_id);
        if(!$post){ /* $post no find failure */
            header("Location: ". URLROOT . "/posts");
        }
        if(!manipulatePostPermission($post)){
            header("Location: ".URLROOT."/posts/show/".$post_id);
        }

        $data = [
            'post' => $post,
            'title' => '',
            'content' => '',
            'description' => '',
            'fileToUpload' => '',
            'titleError' =>'',
            'contentError' => '',
            'fileError' => ''
        ];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            //$_POST = filter_input_array(INPUT_POST);

            $file = $_FILES['fileToUpload'];
            $fileName = $_FILES['fileToUpload']['name'];
            $fileTmpName = $_FILES['fileToUpload']['tmp_name'];
            $fileError = $_FILES['fileToUpload']['error'];
            $fileSize = $_FILES['fileToUpload']['size'];

            $data = [
                'post' => $post,
                'id' => $post_id,
                'fileToUpload' => $file,
                'fileName' => $fileName,
                'fileError' => ''
            ];

            if(empty($data['fileToUpload'])){
                $data['fileError'] = 'Nebyl vybrán žádný soubor.';
            }
            $fileExt = explode('.', $fileName);
            $fileActualExt = mb_strtolower(end($fileExt));
            $allowed = array('pdf');
            if(in_array($fileActualExt, $allowed)){
                if($fileError === 0){
                    if($fileSize < 5000000){
                        $fileNameNew = uniqid('', true).".".$fileActualExt;
                        $fileDestination = 'uploads/'.$fileNameNew;
                        $data['fileName'] = $fileNameNew;
                    }else{
                        $data['fileError'] = 'Soubor je příliš velký.';
                    }
                } else{
                    $data['fileError'] = 'Při nahrávání souboru došlo k chybě.';
                }
            }else{
                $data['fileError'] = 'Špatný typ souboru!';
            }

            if(empty($data['fileError'])){
                if($this->postModel->changePostPdf($data)){
                    move_uploaded_file($fileTmpName,$fileDestination);
                    header("Location:". URLROOT ."/posts");
                }else{
                    die("Nastala chyba, zkuste to prosím znovu.");
                }
            }
        }else{
            $this->view('posts/show', $data);
        }
    }

    /**
     * Controller method of post deleting
     * @param null $post_id   id of the deleted post
     */
    public function delete($post_id = null){
        if($post_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $post = $this->postModel->findPostById($post_id);
        if(!$post){ /* $post no find failure */
            header("Location: ". URLROOT . "/posts");
        }
        if(!manipulatePostPermission($post)){
            header("Location: ". URLROOT . "/posts");
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $fileName = $post->file;
            if($this->postModel->deletePost($post_id)){
                unlink( "uploads/".$fileName );
                header("Location: ". URLROOT ."/posts");
            }else{
                die('Something went wrong');
            }
        }
    }

    /**
     * Controller of showing one specific post
     * @param null $post_id  id of the showed post
     */
    public function show($post_id = null){
        if($post_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $post =  $this->postModel->findPostById($post_id);

        if(!$post){
            header("Location: ". URLROOT . "/posts");
        }
        if( ($post->published == 0) and (!isLoggedIn() or strcmp($_SESSION['user']->role, 'normal') ==0) ){
            header("Location: ". URLROOT . "/posts");
        }

        $comments = $this->postModel->findPostComments($post_id);
        $this->postModel->findAllCommentsReplies($comments);
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
     * @param null $post_id  id of the post to which is comment related
     */
    public function createComment($post_id = null){
        if($post_id == null){
            header("Location: ". URLROOT . "/posts");
        }

        $post =  $this->postModel->findPostById($post_id);
        if(!$post){ /* $post no find failure */
            header("Location: ". URLROOT . "/posts");
        }

        if(!isLoggedIn()){
            header("Location: ".URLROOT ."/posts/show/".$post_id);
        }

        $comments = $this->postModel->findPostComments($post_id);
        $reviews = $this->postModel->findPostReviews($post_id);

        $error = '';

        $data = [
            'post_id' => $post_id,
            'author' => '',
            'commentContent' => '',
            'commentContentError' => '',

            'post' => $post,
            'comments'=> $comments,
            'reviews' => $reviews,
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST);
            $data = [
                'post_id' => $post_id,
                'author' => $_SESSION['user']->username,
                'commentContent' => trim($_POST['commentContent']),
                'commentContentError' => '',

                'post' => $post,
                'comments'=> $comments,
                'reviews' => $reviews,
            ];

            if(empty($data['commentContent'])){
                $data['commentContentError'] = 'The content of a comment cannot be empty';
                $error .= '<p class="invalidFeedBack">Empty content</p>';
            }

            if(empty($data['commentContentError']) ){
                if($this->postModel->addComment($data)){
                   // header("Location:". URLROOT ."/posts/show/".$post_id);
                    //header("Location:". URLROOT ."/posts/show/".$post_id);
                    //$comments = $this->postModel->findPostComments($post_id);
                    $error .= '<label>Comment added</label>';
                    //$data['comments'] = $comments;
                    //$data['commentContent'] = '';
                }else{
                    die("Something went wrong, please try again!");
                }
            }
            $newComments = $this->postModel->findPostComments($post_id);

            $reloadComments = '{{ commentsMacros.showComments(comments) }}';

            //$twig = $this->loadTwig();
            //$rel = $twig->render('includes/comments.inc.twig', $dataComm);
           // $rel = $twig->render($reloadComments, $dataComm);

            /*
            $data2 = array(
                'error' => $error,
                'reloadComments' => $rel
            );*/

            $dataComm = array(
                'comments' => $newComments
            );
            ob_start();
            $this->view('posts/comm', $dataComm);
            $rel = ob_get_clean();
            $data2 = array(
                'error' => $error,
                'reloadComments' => $rel
            );

            echo json_encode($data2);
        }
        //$this->view('posts/show', $data);
    }

    /**
     * Controller method of post deleting
     * @param null $comment_id   id of the deleted comment
     */
    public function deleteComment($comment_id = null){
        if($comment_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $comment = $this->postModel->findCommentById($comment_id);
        if(!$comment){
            header("Location: ". URLROOT . "/posts");
        }

        if(!isLoggedIn()){
            header("Location: ". URLROOT ."/posts/show/".$comment->post_id);
        }elseif($comment->author != $_SESSION['username']){
            header("Location: ". URLROOT . "/posts");
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->postModel->deleteComment($comment_id)){
                header("Location: ". URLROOT ."/posts/show/".$comment->post_id);
            }else{
                die('Something went wrong');
            }
        }
    }

    /*********
     *  REPLIES METHODS
     */
    /**
     * Create reply
     * @param null $comment_id id of the comment to which reply belongs
     */
    public function createReply($comment_id = null){
        if($comment_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $comment = $this->postModel->findCommentById($comment_id);
        if(!$comment){
            header("Location: ". URLROOT . "/posts");
        }

        $post_id = $comment->post_id;

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

            'comment_id' =>$comment_id,
            'replyContent' =>'',
            'replyContentError' => '',

            'commentContent' => '',
            'commentContentError' => ''
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST);
            $data = [
                'comment_id' => $comment_id,
                'user_id' => $_SESSION['user']->id,
                'replyContent' => trim($_POST['replyContent']),

                'replyContentError' => '',

                'post' => $post,
                'comments'=> $comments,
                'reviews' => $reviews
            ];

            if(empty($data['replyContent'])){
                $data['replyContentError'] = 'The content of a comment cannot be empty';
            }

            if(empty($data['replyContentError']) ){
                if($this->postModel->addReply($data)){
                    header("Location:". URLROOT ."/posts/show/".$post_id);
                }else{
                    die("Something went wrong, please try again!");
                }
            }
        }
        $this->view('posts/show', $data);
    }

    /**
     * Controller method of reply deleting
     * @param null $reply_id   id of the deleted reply
     * @param null $post_id    id of the post to which is reply related
     */
    public function deleteReply($reply_id = null, $post_id = null){
        if($post_id == null or $reply_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $post = $this->postModel->findPostById($post_id);
        $reply = $this->postModel->findReply($reply_id);
        if(!$reply or !$post){
            header("Location: ". URLROOT . "/posts");
        }
        if(!isLoggedIn()){
             header("Location: ". URLROOT ."/posts/show/".$post_id);
        }elseif($reply->user_id != $_SESSION['user']->id){
             header("Location: ". URLROOT . "/posts");
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->postModel->deleteReply($reply_id)){
               header("Location: ". URLROOT ."/posts/show/".$post_id);
            }else{
                die('Something went wrong');
            }
        }
    }

}
