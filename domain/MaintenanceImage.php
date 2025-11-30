<?php

class MaintenanceImage {
    private $id;
    private $request_id;
    private $file_name;
    private $file_type;
    private $file_blob;
    private $uploaded_at;

    function __construct($request_id, $file_name, $file_type, $file_blob, $id = null, $uploaded_at = null) {
        $this->request_id = $request_id;
        $this->file_name = $file_name;
        $this->file_type = $file_type;
        $this->file_blob = $file_blob;
        $this->id = $id;
        $this->uploaded_at = $uploaded_at;
    }

    function getID() { return $this->id; }
    function getRequestID() { return $this->request_id; }
    function getFileName() { return $this->file_name; }
    function getFileType() { return $this->file_type; }
    function getFileBlob() { return $this->file_blob; }
    function getUploadedAt() { return $this->uploaded_at; }
}

?>
