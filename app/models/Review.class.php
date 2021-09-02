<?php

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
        $this->db->query('INSERT INTO reviews(post_id, reviewer, topicRelevance, langQuality, originality, recommendation, notes) VALUES
        (:post_id, :reviewer,:topicRelevance ,:langQuality, :originality, :recommendation, :notes)');

        $this->db->bind(':post_id', $data['post_id']);
        $this->db->bind(':reviewer', $data['reviewer']);
        $this->db->bind(':topicRelevance', $data['topicRelevance']);
        $this->db->bind(':langQuality', $data['langQuality']);
        $this->db->bind(':originality', $data['originality']);
        $this->db->bind(':recommendation', $data['recommendation']);
        $this->db->bind(':notes', $data['notes']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    public function updateReview($data){
        $this->db->query('UPDATE reviews SET topicRelevace = :topicRelevance, langQuality = :langQuality,
 originality = :originality, recommendation = :recommendation, notes = :notes WHERE id = :id');

        $this->db->bind(':id', $data['id']);
        //$this->db->bind(':reviewer', $data['reviewer']);
        $this->db->bind(':topicRelevance', $data['topicRelevance']);
        $this->db->bind(':langQuality', $data['langQuality']);
        $this->db->bind(':originality', $data['originality']);
        $this->db->bind(':recommendation', $data['recommendation']);
        $this->db->bind(':notes', $data['notes']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Delete post in table posts with right id
     * @param $id       id of the post
     * @return bool     true - action success, otherwise return false
     */
    public function deleteReview($id){
        $this->db->query('DELETE FROM posts WHERE id = :id');
        $this->db->bind(':id', $id);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }
}
