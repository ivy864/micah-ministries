<?php 

class Comment {
    private $authorID;
    private $requestID;
    private $content;
    private $time;
    /**
     * @param string $authorID
     * @param string $requestID
     * @param string $content
     * @param int $time
     */
    public function __construct($authorID, $requestID, $content, $time) {
        $this->authorID= $authorID;
        $this->requestID = $requestID;
        $this->content = $content;
        $this->time = $time;
    }

    public function getAuthorID() {
        return $this->authorID;
    }

    public function getRequestID() {
        return $this->requestID;
    }

    public function getContent() {
        return $this->content;
    }

    public function getTime() {
        return $this->time;
    }
}
