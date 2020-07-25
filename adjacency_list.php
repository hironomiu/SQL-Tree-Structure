<?php
define('__ROOT__', dirname(dirname(__FILE__)));
//require_once(__ROOT__.'/config/db_config.php');
require_once('./db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt = $pdo->prepare("SELECT count(*) as cnt FROM Comments WHERE comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
    $state = $stmt->execute();
    $row = $stmt->fetch();
    if($row['cnt'] === 0){
       $stmt = $pdo->prepare("insert into  Comments values(null,null,:COMMENT)");
    }else{
       $stmt = $pdo->prepare("insert into  Comments values(null,:PARENT_ID,:COMMENT)");
       $stmt->bindValue(':PARENT_ID',$_POST['key']);
    }
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();
    header('location: adjacency_list.php');
    exit();
}

echo "<h2>隣接リスト(adjacency list)</h2>";

if(array_key_exists('key',$_GET)){
   $parent_stmt = $pdo->prepare("SELECT comment_id FROM Comments WHERE comment_id = :COMMENT_ID");
   $parent_stmt->bindValue(':COMMENT_ID',$_GET['key']);
} else {
   $parent_stmt = $pdo->prepare("SELECT comment_id FROM Comments WHERE parent_id is null");
}
$parent_stmt->execute();
$parent_rows = $parent_stmt->fetchAll();

foreach ($parent_rows as $key){
   $stmt = $pdo->prepare("SELECT comment_id, parent_id, comment FROM Comments WHERE comment_id = :COMMENT_ID");
   $stmt->bindValue(':COMMENT_ID',$key['comment_id']);
   $stmt->execute();
   $row = $stmt->fetch();
   if(isset($row['comment'])){
       echo "<ul>";
       echo "<li>" . $row['comment_id'] . ":" . $row['comment'] . "</li>";
       commentsFindByCommentId($key['comment_id'],$pdo);
       echo "</ul>";
   }

}

function commentsFindByCommentId($key,$pdo)
{
    $stmt = $pdo->prepare("SELECT comment_id, parent_id, comment FROM Comments WHERE parent_id = :PARENT_ID");
    $stmt->bindValue(':PARENT_ID',$key);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
        echo "<ul>";
        echo "<li>" . $row['comment_id'].":" .$row['comment'] . "</li>";
        commentsFindByCommentId($row['comment_id'],$pdo);
        echo "</ul>";
    } 
}

?>

<form method="POST" action="">
    <div>対象のコメントID：<input type="text" name="key" /><br></div>
    <div>コメント：<textarea name="comment">comment</textarea><br></div>
    <input type="submit" />
</form>
