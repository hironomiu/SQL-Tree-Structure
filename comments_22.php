<?php
echo "<h2>隣接リスト</h2>";
try{
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'localhost', 'chapter2'), 'root', 'vagrant', array(PDO::ATTR_EMULATE_PREPARES => false));
}catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
if(isset($_SERVER['REQUEST_METHOD'])){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $parent_id = null;
        if(!empty($_POST['key'])){
	    $stmt = $pdo->prepare("SELECT count(*) as cnt FROM Comments_22 WHERE comment_id = :COMMENT_ID");
	    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
	    $stmt->execute();
	    $row = $stmt->fetch();
	    if($row['cnt'] != "0"){
                $parent_id = $_POST['key'];
	    }
	}
        $stmt = $pdo->prepare("insert into  Comments_22(comment_id,parent_id,bug_id,author,comment_date,comment) values(null,:PARENT_ID,1,4,now(),:COMMENT)");
        $ret = $stmt->bindValue(':PARENT_ID',$parent_id);
        $stmt->bindValue(':COMMENT',$_POST['comment']);
        $ret = $stmt->execute();
        header('location: comments_22.php');
        exit();
    }
}

$key = array_key_exists('key',$_GET) ?  $_GET['key'] : 1;

$stmt = $pdo->prepare("SELECT * FROM Comments_22 c inner join Accounts a on c.author = a.account_id WHERE c.comment_id = :COMMENT_ID");
$stmt->bindValue(':COMMENT_ID',$key);
$stmt->execute();
$row = $stmt->fetch();
if(isset($row['comment'])){
    echo "<ul>"."<li>" . $row['comment_id'] . ":" . $row['comment'] . "(" . $row['name'] .")" .  "</li>";
    commentsFindByCommentId($key,$pdo);
    echo "</ul>";
}

function commentsFindByCommentId($key,$pdo) {
    $stmt = $pdo->prepare("SELECT * FROM Comments_22 c inner join Accounts a on c.author = a.account_id WHERE c.parent_id = :PARENT_ID");
    $stmt->bindValue(':PARENT_ID',$key);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
        echo "<ul>";
        echo "<li>" . $row['comment_id'] . ":" . $row['comment'] . "(" . $row['name'] .")" .  "</li>";
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
