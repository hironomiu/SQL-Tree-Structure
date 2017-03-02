<?php
echo "<h2>入れ子集合</h2>";
try{
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'localhost', 'chapter2'), 'root', 'vagrant', array(PDO::ATTR_EMULATE_PREPARES => false));
}catch (PDOException $e) {
    die( 'Connection failed: ' . $e->getMessage());
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt = $pdo->prepare("select nsright - 1 as nsright from Comments_252 where comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
    $stmt->execute();
    $row = $stmt->fetch();
    $stmt = $pdo->prepare("update Comments_252 set nsleft = case when nsleft > :NSRIGHT1 then nsleft + 2 else nsleft end , nsright = nsright + 2 where nsright >= :NSRIGHT2");
    $stmt->bindValue(':NSRIGHT1',$row['nsright']);
    $stmt->bindValue(':NSRIGHT2',$row['nsright']);
    $stmt->execute();
    $stmt = $pdo->prepare("insert into Comments_252(nsleft,nsright,bug_id,author,comment_date,comment) values(:NSRIGHT +  1,:NSLEFT + 2,1,4,now(),:COMMENT)");
    $stmt->bindValue(':NSRIGHT',$row['nsright'] + 1);
    $stmt->bindValue(':NSLEFT',$row['nsright'] + 2);
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();
    header('location: comments_252.php');
    exit();
}

$key = array_key_exists('key',$_GET) ?  $_GET['key'] : 1;

$stmt = $pdo->prepare("select c2.*,a.name from Comments_252 as c1 inner join Comments_252 as c2 on c2.nsleft between c1.nsleft and c1.nsright inner join Accounts a on c2.author = a.account_id where c1.comment_id = :COMMENT_ID order by c2.nsleft");
$stmt->bindValue(':COMMENT_ID',$key);
$stmt->execute();
$rows = $stmt->fetchAll();

$length = 0;
echo "<ul>";
foreach($rows as $key => $row){
    if($key === 0){
        null;
    }else{
        if($rows[$key - 1 ]['nsleft'] < $row['nsleft'] && $rows[$key - 1]['nsright'] > $row['nsleft']){
            $length++;
        }else{
            for($i=2;$i<$key + 1;$i++){
                if($rows[$key - $i ]['nsleft'] < $row['nsleft'] && $rows[$key - $i ]['nsright'] > $row['nsleft']){
                    $length = $lengths[$key - $i] + 1;
                    break;
                }
            }
        } 
    }
    for($i = 0;$i < $length;$i++){
        echo "<ul>";
    }
    echo "<li>" . $row['comment_id'] . ":" .  $row['comment'] . "(" . $row['name'] .")" .  "</li>";
    for($i = 0;$i < $length;$i++){
        echo "</ul>";
    }
    $lengths[$key] = $length;
}

echo "</ul>";
?>

<form method="POST" action="">
    <input type="text" name="key" /><br>
    <textarea name="comment">comment</textarea><br>
    <input type="submit" />
</form>
