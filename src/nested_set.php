<?php
require_once('./lib/db.php');
require_once('./lib/html.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 階層の深さを取得する
    $stmt = $pdo->prepare('select count(c2.comment_id) as depth from Comments_252 as c1 inner join Comments_252 as c2 on c1.nsleft between c2 .nsleft and c2.nsright where c1.comment_id = :COMMENT_ID group by c1.comment_id');
    $stmt->bindValue(':COMMENT_ID', $_POST['key']);
    $stmt->execute();
    $depth = $stmt->fetch();
    if (empty($depth)) {
        // 対象の親コメントの最大nsrightを求める
        $stmt = $pdo->prepare("select nsleft,nsleft + 10000000 as nsright from (select max(nsright) + 1 as nsleft from Comments_252) a");
        $stmt->execute();
        $row = $stmt->fetch();
    } else {
        // 対象の親コメントの最大nsrightを求める
        $stmt = $pdo->prepare("select nsright from Comments_252 where comment_id = :COMMENT_ID");
        $stmt->bindValue(':COMMENT_ID', $_POST['key']);
        $stmt->execute();
        $max_nsright = $stmt->fetch();

        // 挿入対象のnsleft,nsrightを求める
        $stmt = $pdo->prepare("select nsleft,nsleft +  (10000000 / pow(10,:DEPTH1)) -1 as nsright from (select truncate(max(nsleft),-1 * (7 - :DEPTH2)) + (10000000 / pow(10,:DEPTH3)) + 1 as nsleft from Comments_252 where nsleft < :MAX_NSLEFT) a");
        $stmt->bindValue(':DEPTH1', $depth['depth']);
        $stmt->bindValue(':DEPTH2', $depth['depth']);
        $stmt->bindValue(':DEPTH3', $depth['depth']);
        $stmt->bindValue(':MAX_NSLEFT', $max_nsright['nsright']);
        $stmt->execute();
        $row = $stmt->fetch();
    }
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
    $stmt->bindValue(':NSLEFT', $row['nsleft']);
    $stmt->bindValue(':NSRIGHT', $row['nsright']);
    $stmt->bindValue(':COMMENT', $_POST['comment']);
    $stmt->execute();
    header('location: nested_set.php');
    exit();
}

echo '<link rel="stylesheet" href="/css/base.css">';
echo "<h1>入れ子集合(Nested Set)</h1>";

if (array_key_exists('key', $_GET)) {
    $stmt = $pdo->prepare("select c2.*,a.name from Comments_252 as c1 inner join Comments_252 as c2 on c2.nsleft between c1.nsleft and c1.nsright inner join Accounts a on c2.author = a.account_id where c1.comment_id = :COMMENT_ID order by c2.nsleft");
    $stmt->bindValue(':COMMENT_ID', $_GET['key']);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    ul($rows);
} else {
    $stmt = $pdo->prepare('select c1.comment_id,count(c2.comment_id) as depth from Comments_252 as c1 inner join Comments_252 as c2 on c1.nsleft between c2 .nsleft and c2.nsright group by c1.comment_id having depth = 1');
    $stmt->execute();
    $comment_ids = $stmt->fetchAll();
    foreach ($comment_ids as $comment_id) {
        $stmt = $pdo->prepare("select c2.*,a.name from Comments_252 as c1 inner join Comments_252 as c2 on c2.nsleft between c1.nsleft and c1.nsright inner join Accounts a on c2.author = a.account_id where c1.comment_id = :COMMENT_ID order by c2.nsleft");
        $stmt->bindValue(':COMMENT_ID', $comment_id['comment_id']);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        ul($rows);
    }
}

function ul($rows)
{
    // 深さの初期化
    $length = 0;
    echo "<ul>";

    // 各rowの深さを持つ配列の初期化
    $lengths = [];
    foreach ($rows as $key => $row) {
        // `$key === 0`は深さ1段目が自明なので処理不要
        if ($key !== 0) {
            // 親レコードのnsleft&nsrightに自身のレコードのnsleftが範囲内なら深さをプラスする
            if ($rows[$key - 1]['nsleft'] < $row['nsleft'] && $rows[$key - 1]['nsright'] > $row['nsleft']) {
                $length++;
            } else {
                for ($i = 2; $i < $key + 1; $i++) {
                    // 自身の範囲内の親を見つけたらその親の深さにプラスした値を持ち抜ける
                    if ($rows[$key - $i]['nsleft'] < $row['nsleft'] && $rows[$key - $i]['nsright'] > $row['nsleft']) {
                        $length = $lengths[$key - $i] + 1;
                        break;
                    }
                }
            }
        }
        for ($i = 0; $i < $length; $i++) {
            echo "<ul>";
        }
        echo unorderedList($row['comment_id'], $row['comment'], $row['name']);
        // echo "<li>" . $row['comment_id'] . ":" .  htmlspecialchars($row['comment'],ENT_QUOTES) . "(" . $row['name'] . ")" .  "</li>";
        for ($i = 0; $i < $length; $i++) {
            echo "</ul>";
        }
        // 深さを格納
        $lengths[$key] = $length;
    }
    echo "</ul>";
}
echo newPost();
echo toTop();
