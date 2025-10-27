<?php

include_once('dbinfo.php');

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
    
    $query->execute();
}
