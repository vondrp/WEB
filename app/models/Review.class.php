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
     * @param $id       id of the post, which is being looked for
     * @return mixed    data of post with right id
     */
    public function findReviewById($id){
        $this->db->query('SELECT * FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Find out if user has already reviewed article
     * @param $post_id  id of the post
     * @param $user_id  id of the user
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
}
