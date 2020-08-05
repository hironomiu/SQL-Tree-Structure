<?php
require_once('./lib/db.php');
require_once('./lib/html.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt = $pdo->prepare("select count(*) as cnt from Comments_251 where comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
    $sqlstatus = $stmt->execute();
    $row = $stmt->fetch();
    $stmt = $pdo->prepare("insert into  Comments_251(bug_id,author,comment_date,comment) values(1,4,now(),:COMMENT)");
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();

    if($row['cnt'] === 0){
        $stmt = $pdo->prepare("update  Comments_251 set path = :PATH where comment_id = :LAST_INSERT_ID");
        $stmt->bindValue(':PATH',$pdo->lastInsertId()."/");
        $stmt->bindValue(':LAST_INSERT_ID',$pdo->lastInsertId());
    }else{
        $stmt = $pdo->prepare("update  Comments_251 set path = (select a.path from (select concat(concat(path,:LAST_INSERT_ID1),'/') as path from Comments_251 where comment_id = :COMMENT_ID) as a)  where comment_id = :LAST_INSERT_ID2");
        $stmt->bindValue(':LAST_INSERT_ID1',$pdo->lastInsertId());
        $stmt->bindValue(':COMMENT_ID',$_POST['key']);
        $stmt->bindValue(':LAST_INSERT_ID2',$pdo->lastInsertId());
    }
    $stmt->execute();

    header('location: path_enumeration.php');
    exit();
}

echo '<link rel="stylesheet" href="/css/base.css">';
echo "<h1>経路列挙(Path Enumeration)</h1>";

if (array_key_exists('key', $_GET)) {
    $parent_stmt = $pdo->prepare("SELECT c.comment_id , c.path , c.bug_id , c.author , c.comment_date , c.comment , a.name FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE comment_id = :COMMENT_ID");
    $parent_stmt->bindValue(':COMMENT_ID', $_GET['key']);
} else {
    $parent_stmt = $pdo->prepare("SELECT c.comment_id , c.path , c.bug_id , c.author , c.comment_date , c.comment , a.name FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE (LENGTH(path) - LENGTH(REPLACE(path, '/', '')))   / LENGTH('/') = 1");
}
$sqlstatus = $parent_stmt->execute();
$parent_rows = $parent_stmt->fetchAll();

foreach ($parent_rows as $row) {
    if (isset($row['comment'])) {
        echo "<ul>";
        echo unorderedList($row['comment_id'],$row['comment'],$row['name']);
        // echo "<li>" . $row['comment_id'] . ":" .  htmlspecialchars($row['comment'],ENT_QUOTES) . "(" . $row['name'] . ")" .  "</li>";
        $rows = commentsFindByCommentId($row['path'], $pdo, $row['comment_id']);
        echo "</ul>";
    }
}

function commentsFindByCommentId($key,$pdo,$comment_id) { 
    $stmt = $pdo->prepare("SELECT c.comment_id,c.path,c.comment,a.name FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE c.path like concat(:PATH,'%') and c.comment_id != :COMMENT_ID order by c.path");
    $stmt->bindValue(':PATH',$key);
    $stmt->bindValue(':COMMENT_ID',$comment_id);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
        $length = substr_count($row['path'],'/') - 1;
        for($i = 0;$i < $length;$i++){
            echo "<ul>";
        }
        echo unorderedList($row['comment_id'],$row['comment'],$row['name']);
        // echo "<li>" . $row['comment_id'] . ":" . htmlspecialchars($row['comment'],ENT_QUOTES) . "(" . $row['name'] .")" .  "</li>";
        for($i = 0;$i < $length;$i++){
            echo "</ul>";
        }
    } 
}

echo newPost();
