<?php
require_once(__DIR__ . '/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // 階層の深さを取得する
    $stmt = $pdo->prepare('select count(c2.comment_id) as depth from Comments_252 as c1 inner join Comments_252 as c2 on c1.nsleft between c2 .nsleft and c2.nsright where c1.comment_id = :COMMENT_ID group by c1.comment_id');
    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
    $stmt->execute();
    $depth = $stmt->fetch();

    // 対象の親コメントの最大nsrightを求める
    $stmt = $pdo->prepare("select nsright from Comments_252 where comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT_ID',$_POST['key']);
    $stmt->execute();
    $max_nsright = $stmt->fetch();

    // 挿入対象のnsleft,nsrightを求める
    $stmt = $pdo->prepare("
    select nsleft,nsleft +  (1000000 / pow(10,:DEPTH1)) -1 as nsright from (select truncate(max(nsleft),-1 * (7 - :DEPTH2)) + (10000000 / pow(10,:DEPTH3)) + 1 as nsleft from Comments_252 where nsleft < :MAX_NSLEFT) a");
    $stmt->bindValue(':DEPTH1',$depth['depth']);
    $stmt->bindValue(':DEPTH2',$depth['depth']);
    $stmt->bindValue(':DEPTH3',$depth['depth']);
    $stmt->bindValue(':MAX_NSLEFT',$max_nsright['nsright']);
    $stmt->execute();
    $row = $stmt->fetch();

    /*
    $stmt = $pdo->prepare("update Comments_252 set nsleft = case when nsleft > :NSRIGHT1 then nsleft + 2 else nsleft end , nsright = nsright + 2 where nsright >= :NSRIGHT2");
    $stmt->bindValue(':NSRIGHT1',$row['nsright']);
    $stmt->bindValue(':NSRIGHT2',$row['nsright']);
    $stmt->execute();

    $stmt = $pdo->prepare("insert into Comments_252(nsleft,nsright,bug_id,author,comment_date,comment) values(:NSRIGHT +  1,:NSLEFT + 2,1,4,now(),:COMMENT)");
    $stmt->bindValue(':NSRIGHT',$row['nsright'] + 1);
    $stmt->bindValue(':NSLEFT',$row['nsright'] + 2);
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();
    */

    $stmt = $pdo->prepare("insert into Comments_252(nsleft,nsright,bug_id,author,comment_date,comment) values(:NSLEFT,:NSRIGHT,1,4,now(),:COMMENT)");
    $stmt->bindValue(':NSLEFT',$row['nsleft']);
    $stmt->bindValue(':NSRIGHT',$row['nsright'] + 100);
    $stmt->bindValue(':COMMENT',$_POST['comment']);
    $stmt->execute();
    header('location: nested_sets.php');
    exit();
}

echo '<link rel="stylesheet" href="/css/base.css">';
echo "<h2>入れ子集合(Nested sets)</h2>";

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
    <div>対象のコメントID<br>
    <input type="text" name="key" /><br></div>
    <div>コメント<br>
    <textarea name="comment">comment</textarea><br></div>
    <br><input type="submit" class="button"/>
</form>