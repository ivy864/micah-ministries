<?php

include_once('dbinfo.php');
include_once('../domain/Comment.php');

function get_comments($requestID) {
    $con = connect();

//    $query =  . $requestID;

    $query = $con->prepare('SELECT * FROM db_maintenance_comments WHERE  request_id=:requestID');
    $query->bindValue(':requestID', $requestID, PDO::PARAM_STR);
    $result = mysqli_query($con, $query);

    var_dump($result);
}

/**
 * @param Comment $comment
 */
function add_comment($comment) {
    $con = connect();
    $query = $con->prepare('INSERT INTO `db_maintenance_comments` (`author_id`, `request_id`, `content`, `time`) VALUES (?, ?, ?, ?)');
    $query->bind_param('sssi', $comment->getAuthorID(), $comment->getRequestID(), $comment->getContent(), $comment->getTime());
    
    $query->execute();
}
