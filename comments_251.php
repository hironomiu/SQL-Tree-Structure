<?php
echo "<h2>経路列挙</h2>";
try{
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'localhost', 'chapter2'), 'root', 'vagrant', array(PDO::ATTR_EMULATE_PREPARES => false));
}catch (PDOException $e) {
    die( 'Connection failed: ' . $e->getMessage());
}

if(isset($_SERVER['REQUEST_METHOD'])){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $stmt = $pdo->prepare("insert into  Comments_251(bug_id,author,comment_date,comment) values(1,4,now(),:COMMENT)");
        $stmt->bindValue(':COMMENT',$_POST['comment']);
        $stmt->execute();
        $stmt = $pdo->prepare("update  Comments_251 set path = (select a.path from (select concat(concat(path,:LAST_INSERT_ID1),'/') as path from Comments_251 where comment_id = :COMMENT_ID) as a)  where comment_id = :LAST_INSERT_ID2");
        $stmt->bindValue(':LAST_INSERT_ID1',$pdo->lastInsertId());
        $stmt->bindValue(':COMMENT_ID',$_POST['key']);
        $stmt->bindValue(':LAST_INSERT_ID2',$pdo->lastInsertId());
        $stmt->execute();
        header('location: comments_251.php');
        exit();
    }
}

$key = array_key_exists('key',$_GET) ?  $_GET['key'] : 1;

$stmt = $pdo->prepare("SELECT * FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE c.comment_id = :COMMENT_ID");
$stmt->bindValue(':COMMENT_ID',$key);
$stmt->execute();
$row = $stmt->fetch();

if(isset($row['comment'])){
    echo "<ul>";
    echo "<li>" . $row['comment_id'] . ":".  $row['comment'] . "(" . $row['name'] .")" .  "</li>";
    $rows = commentsFindByCommentId($row['path'],$pdo,$key);
    echo "</ul>";
}


function commentsFindByCommentId($key,$pdo,$comment_id) { 
    $stmt = $pdo->prepare("SELECT * FROM Comments_251 c inner join Accounts a on c.author = a.account_id WHERE c.path like concat(:PATH,'%') and c.comment_id != :COMMENT_ID order by c.path");
    $stmt->bindValue(':PATH',$key);
    $stmt->bindValue(':COMMENT_ID',$comment_id);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
        $length = substr_count($row['path'],'/') - 1;
        for($i = 0;$i < $length;$i++){
            echo "<ul>";
        }
        echo "<li>" . $row['comment_id'] . ":" . $row['comment'] . "(" . $row['name'] .")" .  "</li>";
        for($i = 0;$i < $length;$i++){
            echo "</ul>";
        }
    } 
}

?>

<form method="POST" action="">
    <input type="text" name="key" /><br>
    <textarea name="comment">comment</textarea><br>
    <input type="submit" />
</form>
