<?php
class Reviews extends Controller {

    /**
     * Reviews constructor
     * arranged connection with the model
     */
    public function __construct(){
        $this->reviewModel = $this->model('Review');
        $this->postModel = $this->model('Post');
    }


    /**
     * Controller of the create review view
     * checks data of the new review provided by the user
     * @param $post_id  id of the post to which review belong
     */
    public function create($post_id){
        //href="{{ constant('URLROOT') }}/posts/show/{{ post.id }}
        $post = $this->postModel->findPostById($post_id);
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
                'reviewer_id' => $_SESSION['user']->id,
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
                if($this->reviewModel->addReview($data)){
                    header("Location:". URLROOT ."/posts/show/".$post_id);
                }else{
                    die("Something went wrong, please try again!");
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
     * @param $review_id    id of the upgraded review
     */
    public function update($review_id){
        $review = $this->reviewModel->findReviewById($review_id);
        //strcmp($review->reviewer,$_SESSION['username'] ) !=0)
        if(!isLoggedIn() or ( $review->reviewer_id != $_SESSION['user']->id))
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
     * Controller method of post deleting
     * @param $id   id of the deleted post
     */
    public function delete($id){
        $review = $this->reviewModel->findReviewById($id);
        $post_id = $review->post_id;
        if(!isLoggedIn()){
            header("Location: ". URLROOT . "/posts");
            //strcmp($review->reviewer_id,$_SESSION['username']) !=0
        }elseif( $review->reviewer_id != $_SESSION['user']->id){
            header("Location: ". URLROOT . "/posts");
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($this->reviewModel->deleteReview($id)){
                header("Location: ". URLROOT ."/posts/show/".$post_id);
            }else{
                die('Something went wrong');
            }
        }
    }
}
