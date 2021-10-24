<?php

/**
 * Class Review is model part of the reviews
 * - works directly width database
 * it´s methods are called from controller
 */
class Review{

    /**
     * @var Database    local database connection
     */
    private $db;

    /**
     * Post constructor.
     * initialize database connection for the this class
     */
    public function __construct(){
        $this->db = new Database();
    }

    /**
     * Add a new Review to the table
     * @param $data     array of data which are being added to review table in database
     * @return bool     true - if action is successful, otherwise return false
     */
    public function addReview($data){
        $this->db->query('INSERT INTO reviews(post_id, user_id, topicRelevance, langQuality, originality, recommendation, notes) VALUES
        (:post_id, :user_id, :topicRelevance ,:langQuality, :originality, :recommendation, :notes)');

        $this->db->bind(':post_id', $data['post_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':recommendation', $data['recommendation'], PDO::PARAM_INT);
        $this->db->bind(':topicRelevance', $data['topicRelevance'], PDO::PARAM_INT);
        $this->db->bind(':langQuality', $data['langQuality'], PDO::PARAM_INT);
        $this->db->bind(':originality', $data['originality'], PDO::PARAM_INT);
        $this->db->bind(':notes', $data['notes']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Update the review
     * @param $data     updated data of the review
     * @return bool     true - if update succeeded, otherwise return false
     */
    public function updateReview($data){
        $this->db->query('UPDATE reviews SET topicRelevance = :topicRelevance, langQuality = :langQuality,
 originality = :originality, recommendation = :recommendation, notes = :notes WHERE id = :id');

        $this->db->bind(':recommendation', $data['recommendation']);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':topicRelevance', $data['topicRelevance']);
        $this->db->bind(':langQuality', $data['langQuality']);
        $this->db->bind(':originality', $data['originality']);
        $this->db->bind(':notes', $data['notes']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Delete post in table reviews with right id
     * @param $id       id of the review
     * @return bool     true - action success, otherwise return false
     */
    public function deleteReview($id){
        $this->db->query('DELETE FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Find review in table reviews with his id
     * @param int $id       id of the of the review
     * @return mixed    data of post with right id
     */
    public function findReviewById($id){
        $this->db->query('SELECT * FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Find out if user has already reviewed article
     * @param int $post_id  id of the post
     * @param int $user_id  id of the user
     * @return false|mixed  false - user has NOT reviewed selected post, otherwise return record of the review
     */
    public function hasAlreadyReviewedArticle($post_id, $user_id){
        $this->db->query('SELECT * FROM reviews WHERE user_id = :user_id AND post_id = :post_id');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':post_id', $post_id);

        $row = $this->db->single();
        if(!empty($row)){
            return $row;
        }else{
            return false;
        }
    }

    /**
     * @return mixed    all users with role reviewer
     */
    public function findAllReviewers(){
        $this->db->query('SELECT * FROM users WHERE role = "reviewer" ');
        return $this->db->resultSet();
    }

    /**
     * Assign up to three reviews for the post
     * if one of the reviewer already create review for the article
     * new one wont be created
     * @param $data     id of the post, id´s of the reviewers
     * @return bool     true - everything went ok, otherwise return false
     *
     */
    public function threeReviewersForPost($data){
        $row1 = $this->hasAlreadyReviewedArticle($data['post_id'], $data['reviewerID_1']);
        if(empty($row1)){
            $this->db->query('INSERT INTO reviews(post_id, user_id ) VALUES
            (:post_id, :user_id)');

            $this->db->bind(':post_id', $data['post_id']);
            $this->db->bind(':user_id', $data['reviewerID_1']);

            if(!$this->db->execute()){
                return false;
            }
        }
        $row2 = $this->hasAlreadyReviewedArticle($data['post_id'], $data['reviewerID_2']);
        if(empty($row2)) {
                $this->db->query('INSERT INTO reviews(post_id, user_id ) VALUES (:post_id, :user_id)');

                $this->db->bind(':post_id', $data['post_id']);
                $this->db->bind(':user_id', $data['reviewerID_2']);

                if (!$this->db->execute()) {
                    return false;
                }
        }
        $row3 = $this->hasAlreadyReviewedArticle($data['post_id'], $data['reviewerID_3']);
        if(empty($row3)) {
            $this->db->query('INSERT INTO reviews(post_id, user_id ) VALUES (:post_id, :user_id)');

            $this->db->bind(':post_id', $data['post_id']);
            $this->db->bind(':user_id', $data['reviewerID_3']);

            if (!$this->db->execute()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return mixed    return all posts with least than three reviews
     */
    public function findAllPostsWithoutAtLeastThreeReviews(){
        $this->db->query('SELECT * FROM posts ORDER BY created_at DESC ');
        $posts = $this->db->resultSet();

        $results = [];
        foreach($posts as $post) {
            $this->db->query('SELECT * FROM reviews WHERE post_id = :post_id ORDER BY created_at DESC ');
            $this->db->bind(':post_id', $post->id);
            $foundReviews = $this->db->rowCount();
            if ($foundReviews < 3) {
                if($foundReviews == 0) $post->howManyReviewsToAdd = 3;
                else if ($foundReviews == 1) $post->howManyReviewsToAdd = 2;
                else if ($foundReviews == 2) $post->howManyReviewsToAdd = 1;
                array_push($results, $post);
            }
        }
        return $results;
    }

    /**
     * Find all reviews, which have assigned reviewer but were not done yet
     * @return mixed    all reviews with 0 values -> not done yet by assigned reviewer
     */
    public function findAllUnfinishedReviews(){
        $this->db->query('SELECT * FROM reviews WHERE topicRelevance = 0 AND langQuality =0 AND originality =0
        AND recommendation =0 ORDER BY created_at DESC ');

        $results = $this->db->resultSet();

        foreach ($results as $record) {
            $record->post = $this->findPostById($record->post_id);
        }

        foreach($results as $record){
            $record->author = $this->findUserById($record->user_id);
        }
        return $results;
    }


    /**
     * Find post in table posts with his id
     * @param $id       id of the post, which is being looked for
     * @return mixed    data of post with right id
     */
    public function findPostById($id){
        $this->db->query('SELECT * FROM posts WHERE id = :id');
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row;
    }

    /**
     * Find user by his id
     * @param int $user_id    id of the user we are looking for
     * @return mixed          return record of the user
     */
    public function findUserById($user_id){
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $user_id);
        return $this->db->single();
    }
}
