<?php
require_once(__DIR__ . '/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("insert into Comments_253 (bug_id,author,comment_date,comment) values (1,4,now(),:COMMENT)");
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();

    $stmt = $pdo->prepare("select count(*) as cnt from Comments_253 where comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT',$_POST['key']);
    $stmt->execute();
    $row = $stmt->fetch();

    $lastInsert_id = $pdo->lastInsertId();
    $parent_id = $_POST['key'];

    if($row['cnt']===0){
        $parent_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("insert into TreePaths(ancestor,descendant) select t.ancestor,:DESCENDANT1 from TreePaths as t where t.descendant = :PARENT_ID union all select :ANCESTOR,:DESCENDANT2");
    $stmt->bindValue(':DESCENDANT1',$lastInsert_id);
    $stmt->bindValue(':PARENT_ID',$parent_id);
    $stmt->bindValue(':ANCESTOR',$lastInsert_id);
    $stmt->bindValue(':DESCENDANT2',$lastInsert_id);
    $stmt->execute();
    $pdo->commit();
    header('location: closure_table.php');
    exit();
}

echo '<link rel="stylesheet" href="/css/base.css">';
echo "<h2>閉包テーブル(Closure Table)</h2>";

if (array_key_exists('key', $_GET)) {
    $stmt = $pdo->prepare("select descendant ,comment_id, comment, name, path from (select t1.descendant ,c.comment_id, c.comment, a.name, concat(group_concat(t1.ancestor separator'/'),'/') as path from TreePaths t1 inner join TreePaths t2 on t1.descendant = t2.descendant inner join Comments_253 c on c.comment_id = t1.descendant inner join Accounts a on a.account_id = c.author where t2.ancestor = :ANCESTOR group by t2.descendant) a order by path");
    $stmt->bindValue(':ANCESTOR', $_GET['key']);
} else {
    $stmt = $pdo->prepare("select descendant ,comment_id, comment, name, path from (select t1.descendant ,c.comment_id, c.comment, a.name, concat(group_concat(t1.ancestor separator'/'),'/') as path from TreePaths t1 inner join TreePaths t2 on t1.descendant = t2.descendant inner join Comments_253 c on c.comment_id = t1.descendant inner join Accounts a on a.account_id = c.author group by t2.descendant) a order by path");
}

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
