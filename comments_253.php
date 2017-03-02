<?php

try{
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'localhost', 'chapter2'), 'root', 'vagrant', array(PDO::ATTR_EMULATE_PREPARES => false));
}catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die;
}

if(isset($_SERVER['REQUEST_METHOD'])){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $stmt = $pdo->prepare("insert into Comments_253 (bug_id,author,comment_date,comment) values (1,4,now(),:COMMENT)");
        $stmt->bindValue(':COMMENT',$_POST['comment']);
        $stmt->execute();
        $stmt = $pdo->prepare("insert into TreePaths(ancestor,descendant) select t.ancestor,:DESCENDANT1 from TreePaths as t where t.descendant = :PARENT_ID union all select :ANCESTOR,:DESCENDANT2");
        $stmt->bindValue(':DESCENDANT1',$pdo->lastInsertId());
        $stmt->bindValue(':PARENT_ID',$_POST['key']);
        $stmt->bindValue(':ANCESTOR',$pdo->lastInsertId());
        $stmt->bindValue(':DESCENDANT2',$pdo->lastInsertId());
        $stmt->execute();
        header('location: comments_253.php');
        exit();
    }
}

echo "<h2>閉包テーブル</h2>";

$key = array_key_exists('key',$_GET) ?  $_GET['key'] : 1;

$stmt = $pdo->prepare("select t1.descendant ,c.comment_id, c.comment, a.name, concat(group_concat(t1.ancestor separator'/'),'/') as path from TreePaths t1 inner join TreePaths t2 on t1.descendant = t2.descendant inner join Comments_253 c on c.comment_id = t1.descendant inner join Accounts a on a.account_id = c.author where t2.ancestor = :ANCESTOR group by t2.descendant order by path");
$stmt->bindValue(':ANCESTOR',$key);
$stmt->execute();
$rows = $stmt->fetchAll();
echo "<ul>";
foreach($rows as $row){
    $length = substr_count($row['path'],'/') - 1;
    for($i = 0;$i < $length;$i++){
        echo "<ul>";
    }
    echo "<li>" . $row['comment_id'] . ":" .  $row['comment'] . "(" . $row['name'] .")" .  "</li>";
    for($i = 0;$i < $length;$i++){
        echo "</ul>";
    }
} 
echo "</ul>";

?>

<form method="POST" action="">
    <input type="text" name="key" /><br>
    <textarea name="comment">comment</textarea><br>
    <input type="submit" />
</form>
