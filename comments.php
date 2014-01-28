<?php
try{
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'localhost', 'chapter2'), 'root', 'vagrant', array(PDO::ATTR_EMULATE_PREPARES => false));
}catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die;
}

if(isset($_SERVER['REQUEST_METHOD'])){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
$stmt = $pdo->prepare("SELECT count(*) as cnt FROM Comments WHERE comment_id = :COMMENT_ID");
$stmt->bindValue(':COMMENT_ID',$_POST['key']);
$stmt->execute();
$row = $stmt->fetch();
        if($row['cnt'] == 0){
           $parent_id = null;
        }else{
           $parent_id = $_POST['key'];
        }
        $stmt = $pdo->prepare("insert into  Comments values(null,:PARENT_ID,:COMMENT)");
        $stmt->bindValue(':PARENT_ID',$parent_id);
        $stmt->bindValue(':COMMENT',$_POST['comment']);
        $stmt->execute();
    }
}
echo "<h2>隣接リスト</h2>";

$key = array_key_exists('key',$_GET) ?  $_GET['key'] : 1;

$stmt = $pdo->prepare("SELECT * FROM Comments WHERE comment_id = :COMMENT_ID");
$stmt->bindValue(':COMMENT_ID',$key);
$stmt->execute();
$row = $stmt->fetch();
if(isset($row['comment'])){
    echo "<ul>";
    echo "<li>" . $row['comment_id'] . ":" . $row['comment'] . "</li>";
    commentsFindByCommentId($key,$pdo);
    echo "</ul>";
}

function commentsFindByCommentId($key,$pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM Comments WHERE parent_id = :PARENT_ID");
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
    <input type="text" name="key" /><br>
    <textarea name="comment">comment</textarea><br>
    <input type="submit" />
</form>
