<?php

include_once('dbinfo.php');
include_once(dirname(__FILE__) . '/../domain/Comment.php');

function get_comments($requestID) {
    $con = connect();

//    $query =  . $requestID;

    $query = $con->prepare('SELECT * FROM db_maintenance_comments WHERE  request_id=? ORDER BY `db_maintenance_comments`.`time` ASC');
    $query->bind_param('s', $requestID);
    $query->execute();

    $result = $query->get_result();

    return($result->fetch_all(MYSQLI_ASSOC));
}

/**
 * @param Comment $comment
 */
function add_comment($comment) {
    $con = connect();
    $query = $con->prepare('INSERT INTO `db_maintenance_comments` (`author_id`, `request_id`, `content`, `time`) VALUES (?, ?, ?, ?)');
    $query->bind_param('sssi', $comment->getAuthorID(), $comment->getRequestID(), $comment->getContent(), $comment->getTime());
    
    $result = $query->execute();
    
    if (!$result) {
        error_log("Database error: " . $con->error);
        return false;
    }
    
    return true;
}

/**
 * @param Comment $comment
 */
function delete_comment($comment) {
    $con = connect();
    $query = $con->prepare('DELETE FROM db_maintenance_comments WHERE `author_id`=?  AND `request_id`=?  AND `time`=?');
    $query->bind_param('ssi', $comment->getAuthorID(), $comment->getRequestID(), $comment->getTime());

    $result = $query->execute();

    if (!$result) {
        error_log("Database error: " . $con->error);
        return false;
    }

    return true;
}
