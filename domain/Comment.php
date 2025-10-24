<?php 

class Comment {
    private $name;
    private $requestID;
    private $content;
    private $time;

    public function __construct($name, $requestID, $content, $time) {
        $this->name = $name;
        $this->requestID = $requestID;
        $this->content = $content;
        $this->time = $time;
    }

    public function getName() {
        return $this->name;
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
