<?php
/**
 * Class Post
 */
class Post{
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
     * @return mixed    return all posts in posts table
     */
    public function findAllPosts(){
        $this->db->query('SELECT * FROM posts ORDER BY created_at DESC ');

        $results = $this->db->resultSet();

        return $results;
    }

    /**
     * Add a new Post to the table
     * @param $data     array of data which are being added to posts table in database
     * @return bool     true - if action is successful, otherwise return false
     */
    public function addPost($data){
        $this->db->query('INSERT INTO posts(user_id, title, description, content) VALUES
        (:user_id, :title,:description ,:content)');

        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':content', $data['content']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
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
     * Update the post
     * @param $data     updated data of the post
     * @return bool     true - if update succeeded, otherwise return false
     */
    public function updatePost($data){
        $this->db->query('UPDATE posts SET title = :title, description = :description, content = :content WHERE id = :id');

        $this->db->bind(':id',$data['id']);
        $this->db->bind(':title',$data['title']);
        $this->db->bind(':description',$data['description']);
        $this->db->bind(':content',$data['content']);

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
    public function deletePost($id){
        $this->db->query('DELETE FROM posts WHERE id = :id');
        $this->db->bind(':id', $id);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Return all comments form table comments relatable to the selected post
     * @param $post_id      id of the post
     * @return mixed        post comments
     */
    public function findPostComments($post_id){
        $this->db->query('SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at DESC ');
        $this->db->bind(':post_id', $post_id);
        $results = $this->db->resultSet();
        return $results;
    }

    /**
     * Find comment in table comments with his id
     * @param $comment_id       id of the comment, which is being looked for
     * @return mixed    data of comment with right id
     */
    public function findCommentById($comment_id){
        $this->db->query('SELECT * FROM comments WHERE id = :id');
        $this->db->bind(':id', $comment_id);
        $row = $this->db->single();
        return $row;
    }

    /**
     * Add a new Comment to the comments table
     * @param $data     array of data which are being added to comments table in database
     * @return bool     true - if action is successful, otherwise return false
     */
    public function addComment($data){
        $this->db->query('INSERT INTO comments(post_id, author, content) VALUES
        (:post_id, :author, :content)');

        $this->db->bind(':post_id', $data['post_id']);
        $this->db->bind(':author', $data['author']);
        $this->db->bind(':content', $data['content']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Delete post in table posts with right id
     * @param $comment_id       id of the comment
     * @return bool     true - action success, otherwise return false
     */
    public function deleteComment($comment_id){
        $this->db->query('DELETE FROM comments WHERE id = :id');
        $this->db->bind(':id', $comment_id);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /*********
     *
     * REPLIES METHODS
     *
     *********/
    /**
     * Add a new Reply to the replies table
     * @param $data     array of data which are being added to replies table in database
     * @return bool     true - if action is successful, otherwise return false
     */
    public function addReply($data){
        $this->db->query('INSERT INTO replies(comment_id, user_id, content) VALUES
        (:comment_id, :user_id, :replyContent)');

        $this->db->bind(':comment_id', $data['comment_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':replyContent', $data['replyContent']);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Delete reply in table replies with right id
     * @param $reply_id       id of the comment
     * @return bool     true - action success, otherwise return false
     */
    public function deleteReply($reply_id){
        $this->db->query('DELETE FROM replies WHERE id = :id');
        $this->db->bind(':id', $reply_id);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Return all replies form table replies relatable to the selected comment
     * @param $comment_id      id of the comment
     * @return mixed        post comments
     */
    public function findCommentReplies($comment_id){
        $this->db->query('SELECT * FROM replies WHERE comment_id = :comment_id ORDER BY created_at ASC ');
        $this->db->bind(':comment_id', $comment_id);
        $results = $this->db->resultSet();
        return $results;
    }

    /**
     * Assing to all comments in an array their replies
     * @param $comments     array of comments
     */
    public function findAllCommentsReplies($comments){
        foreach ($comments as $comment) {
            $comment->replies = $this->findCommentReplies($comment->id);
            $this->findAuthors($comment->replies);
        }
    }

    public function findReply($reply_id){
        $this->db->query('SELECT * FROM replies WHERE id = :id');
        $this->db->bind(':id', $reply_id);
        return $this->db->single();
    }
    /**
     *  REVIEWS METHODS
     */

    /**
     * Return all reviews form table reviews relatable to the selected post
     * @param $post_id      id of the post
     * @return mixed        post comments
     */
    public function findPostReviews($post_id){
        $this->db->query('SELECT * FROM reviews WHERE post_id = :post_id ORDER BY created_at DESC ');
        $this->db->bind(':post_id', $post_id);
        $results = $this->db->resultSet();
        return $results;
    }

    /*********
     * FIND USERS METHODS
     */

    /**
     * Find user by his id
     * @param $user_id    id of the user we are looking for
     */
    public function findUserById($user_id){
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $user_id);
        return $this->db->single();
    }

    public function findAuthors($records){
        foreach ($records as $record) {
            $record->author = $this->findUserById($record->user_id);
        }
    }
}
