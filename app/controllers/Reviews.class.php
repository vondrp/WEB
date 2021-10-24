<?php

/**
 * Class Reviews is controller of the reviews
 */
class Reviews extends Controller {

    /**
     * Reviews constructor
     * arranged connection with the model
     */
    public function __construct(){
        $this->reviewModel = $this->model('Review');
        //$this->postModel = $this->model('Post');
    }

    /**
     * Base page of the reviews controller
     * actually not exists - redirect to home index page
     */
    public function index(){
        $data = [
            'title' => 'Home page',
        ];
        $this->view('pages/index', $data);
    }

    /**
     * Controller of the create review view
     * checks data of the new review provided by the user
     * @param null $post_id  id of the post to which review belong
     */
    public function create($post_id = null){
        if($post_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        //href="{{ constant('URLROOT') }}/posts/show/{{ post.id }}
        $post = $this->reviewModel->findPostById($post_id);
        if(!$post){
            header("Location: ". URLROOT . "/posts");
        }
        if(!reviewerPermissions()){
            header("Location: ".URLROOT . "/posts/show/".$post_id);
        }
        $data = [
            'post' => $post,
            'topicRelevance' => '',
            'langQuality' => '',
            'originality' =>'',
            'recommendation' => '',
            'notes' => '',
            'topicRelevanceError' => '',
            'langQualityError' => '',
            'originalityError' =>'',
            'recommendationError' => '',
            'notesError' => ''
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST);

            $data = [
                'post_id' => $post_id,
                'user_id' => $_SESSION['user']->id,
                'topicRelevance' => trim($_POST['topicRelevance']),
                'langQuality' => trim($_POST['langQuality']),
                'originality' => trim($_POST['originality']),
                'recommendation' =>  trim($_POST['recommendation']),
                'notes' => trim($_POST['notes']),
                'topicRelevanceError' => '',
                'langQualityError' => '',
                'originalityError' =>'',
                'recommendationError' => '',
                'notesError' => ''
            ];

            if(empty($data['topicRelevance'])){
                $data['topicRelevanceError'] = 'Je třeba uvést relevantnost tématu.';
            }

            if(empty($data['langQuality'])){
                $data['langQualityError'] = 'Je třeba uvést kvalitu jazykových prostředků.';
            }

            if(empty($data['originality'])){
                $data['originalityError'] = 'Je třeba uvést míru originality.';
            }

            if(empty($data['recommendation'])){
                $data['recommendationError'] = 'Je třeba uvést doporučení o publikování.';
            }

            if(empty($data['topicRelevanceError'])
                && empty($data['langQualityError'])
                && empty($data['originalityError'])
                && empty($data['recommendationError'])){

                $alreadyReviewed = $this->reviewModel->hasAlreadyReviewedArticle($post_id, $data['user_id']);
                if(!$alreadyReviewed){
                    if($this->reviewModel->addReview($data)){
                        header("Location:". URLROOT ."/posts/show/".$post_id);
                    }else{
                        die("Something went wrong, please try again!");
                    }
                }else{
                    if($this->reviewModel->deleteReview($alreadyReviewed->id)){
                        if($this->reviewModel->addReview($data)){
                            header("Location:". URLROOT ."/posts/show/".$post_id);
                        }else{
                            die("Something went wrong, please try again!");
                        }
                    }else{
                        die('Something went wrong');
                    }
                }
            }else{
                $this->view('reviews/create', $data);
            }

        }else{
            $this->view('reviews/create', $data);
        }

    }

    /**
     * Controller of the create view
     * checks data of the new post provided by the user
     * before sending them to model
     */

    /**
     * Controller of the update review view
     * checks upgraded data of the review provided by the user
     * before sending them to model
     * @param null $review_id    id of the upgraded review
     */
    public function update($review_id = null){
        if($review_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $review = $this->reviewModel->findReviewById($review_id);
        if(!$review){
            header("Location: ". URLROOT . "/posts");
        }
        //strcmp($review->reviewer,$_SESSION['username'] ) !=0)
        if(!isLoggedIn() or ( $review->user_id != $_SESSION['user']->id))
        {
            header("Location: ".URLROOT . "/posts/show/".$review->post_id);
        }
        $data = [
            'review' => $review,
            'topicRelevance' => '',
            'langQuality' => '',
            'originality' =>'',
            'recommendation' => '',
            'notes' => '',
            'topicRelevanceError' => '',
            'langQualityError' => '',
            'originalityError' =>'',
            'recommendationError' => '',
            'notesError' => ''
        ];

        //Check is form submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST);

            $data = [
                'id' => $review_id,
                'topicRelevance' => trim($_POST['topicRelevance']),
                'langQuality' => trim($_POST['langQuality']),
                'originality' => trim($_POST['originality']),
                'recommendation' =>  trim($_POST['recommendation']),
                'notes' => trim($_POST['notes']),
                'topicRelevanceError' => '',
                'langQualityError' => '',
                'originalityError' =>'',
                'recommendationError' => '',
                'notesError' => ''
            ];

            if(empty($data['topicRelevance'])){
                $data['topicRelevanceError'] = 'Je třeba uvést relevantnost tématu.';
            }

            if(empty($data['langQuality'])){
                $data['langQualityError'] = 'Je třeba uvést kvalitu jazykových prostředků.';
            }

            if(empty($data['originality'])){
                $data['originalityError'] = 'Je třeba uvést míru originality.';
            }

            if(empty($data['recommendation'])){
                $data['recommendationError'] = 'Je třeba uvést doporučení o publikování.';
            }

            if(empty($data['topicRelevanceError'])
                && empty($data['langQualityError'])
                && empty($data['originalityError'])
                && empty($data['recommendationError'])){
                if($this->reviewModel->updateReview($data)){
                    header("Location:". URLROOT ."/posts/show/".$review->post_id);
                }else{
                    die("Something went wrong, please try again!");
                }
            }else{
                $this->view('reviews/update', $data);
            }

        }else{
            $this->view('reviews/update', $data);
        }

    }

    /**
     * Controller method of review deleting
     * @param null $review_id   id of the deleted review
     */
    public function delete($review_id = null){
        if($review_id == null){
            header("Location: ". URLROOT . "/posts");
        }
        $review = $this->reviewModel->findReviewById($review_id);
        $post_id = $review->post_id;
        if(!isLoggedIn()){
            header("Location: ". URLROOT . "/posts");
            //strcmp($review->reviewer_id,$_SESSION['username']) !=0
        }elseif( $review->user_id != $_SESSION['user']->id){
            header("Location: ". URLROOT . "/posts");
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->reviewModel->deleteReview($review_id)){
                header("Location: ". URLROOT ."/posts/show/".$post_id);
            }else{
                die('Something went wrong');
            }
        }
    }

    /**
     * Controller of the view, where admins can assign
     * reviewers to the post
     */
    public function assignReviews(){
        if(!adminPermissions()){
            header("Location: ". URLROOT ."/pages/index");
        }

        $posts = $this->reviewModel->findAllPostsWithoutAtLeastThreeReviews();
        $undoneReviews = $this->reviewModel->findAllUnfinishedReviews();

        $reviewerOptions = $this->reviewModel->findAllReviewers();
        $data = [
            'posts' => $posts,
            'reviewers' => $reviewerOptions,
            'undoneReviews' => $undoneReviews
        ];
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST);
            $data = [
                'posts' => $posts,
                'reviewers' => $reviewerOptions,
                'undoneReviews' => $undoneReviews,
                'post_id' => $_POST['post_id'],
                'reviewerID_1' => ($_POST['reviewerID_1']),
                'reviewerID_2' => ($_POST['reviewerID_2']),
                'reviewerID_3' => ($_POST['reviewerID_3']),
                'reviewersError' => ''
            ];

            if( ($data['reviewerID_1']==0)
                || ($data['reviewerID_2']==0)
                || ($data['reviewerID_3']==0)
            ){
                $data['reviewersError'] = 'Je třeba vybrat všechny 3 recenzenty';
            }else if(
                ($data['reviewerID_1'] == $data['reviewerID_2'] )
                || ( $data['reviewerID_1'] == $data['reviewerID_3'] )
                || ( $data['reviewerID_2'] == $data['reviewerID_3'] )
            ){
                $data['reviewersError'] = 'Vybraní recenzenti musí být různí';
            }

            if(empty($data['reviewersError'] ) ){
                if($this->reviewModel->threeReviewersForPost($data)){
                    header("Location:". URLROOT ."/reviews/assignReviews");
                }else{
                    die("Something went wrong, please try again!");
                }
            }else{
                $this->view('reviews/assignReviews', $data);
            }
        }else{
            $this->view('reviews/assignReviews', $data);
        }
    }
}
