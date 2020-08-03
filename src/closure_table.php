<?php
require_once('./lib/db.php');
require_once('./lib/html.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt = $pdo->prepare("select count(*) as cnt from Comments_253 where comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
    $stmt->execute();
    $row = $stmt->fetch();
    
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("insert into Comments_253 (bug_id,author,comment_date,comment) values (1,4,now(),:COMMENT)");
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();

    $lastInsert_id = $pdo->lastInsertId();
    $parent_id = $_POST['key'];

    if($row['cnt']===0){
        $parent_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("insert into TreePaths(ancestor,descendant) values(:ANCESTOR,:DESCENDANT)");
        $stmt->bindValue(':ANCESTOR',$lastInsert_id);
        $stmt->bindValue(':DESCENDANT',$lastInsert_id);
    }else{
        $stmt = $pdo->prepare("insert into TreePaths(ancestor,descendant) select t.ancestor,:DESCENDANT1 from TreePaths as t where t.descendant = :PARENT_ID union all select :ANCESTOR,:DESCENDANT2");
        $stmt->bindValue(':DESCENDANT1',$lastInsert_id);
        $stmt->bindValue(':PARENT_ID',$parent_id);
        $stmt->bindValue(':ANCESTOR',$lastInsert_id);
        $stmt->bindValue(':DESCENDANT2',$lastInsert_id);
    }
    $stmt->execute();
    $pdo->commit();
    header('location: closure_table.php');
    exit();
}

echo '<link rel="stylesheet" href="/css/base.css">';
echo "<h1>閉包テーブル(Closure Table)</h1>";

if (array_key_exists('key', $_GET)) {
    $stmt = $pdo->prepare("select descendant ,comment_id, comment, name, path from (select t1.descendant ,c.comment_id, c.comment, a.name, concat(group_concat(t1.ancestor separator'/'),'/') as path from TreePaths t1 inner join TreePaths t2 on t1.descendant = t2.descendant inner join Comments_253 c on c.comment_id = t1.descendant inner join Accounts a on a.account_id = c.author where t2.ancestor = :ANCESTOR group by t2.descendant) a order by path");
    $stmt->bindValue(':ANCESTOR', $_GET['key']);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    ul($rows);
} else {
    $parent_stmt = $pdo->prepare("select descendant,count(*) cnt from TreePaths group by descendant having count(*) =1;");
    $parent_stmt->execute();
    $parent_rows = $parent_stmt->fetchAll();
    foreach($parent_rows as $parent_row){
        $stmt = $pdo->prepare("select descendant ,comment_id, comment, name, path from (select t1.descendant ,c.comment_id, c.comment, a.name, concat(group_concat(t1.ancestor separator'/'),'/') as path from TreePaths t1 inner join TreePaths t2 on t1.descendant = t2.descendant inner join Comments_253 c on c.comment_id = t1.descendant inner join Accounts a on a.account_id = c.author where t2.ancestor = :ANCESTOR group by t2.descendant) a order by path");
        $stmt->bindValue(':ANCESTOR', $parent_row['descendant']);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        ul($rows);
    }
}

function ul($rows)
{
    echo "<ul>";
    foreach ($rows as $row) {
        $length = substr_count($row['path'], '/') - 1;
        for ($i = 0; $i < $length; $i++) {
            echo "<ul>";
        }
        echo unorderedList($row['comment_id'],$row['comment'],$row['name']);
        // echo "<li>" . $row['comment_id'] . ":" .  htmlspecialchars($row['comment'],ENT_QUOTES) . "(" . $row['name'] . ")" .  "</li>";
        for ($i = 0; $i < $length; $i++) {
            echo "</ul>";
        }
    }
    echo "</ul>";
}

echo newPost();
