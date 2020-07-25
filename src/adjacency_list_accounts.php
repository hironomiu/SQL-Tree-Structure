<?php
require_once('./db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_id = null;
    if (!empty($_POST['key'])) {
        $stmt = $pdo->prepare("SELECT count(*) as cnt FROM Comments_22 WHERE comment_id = :COMMENT_ID");
        $stmt->bindValue(':COMMENT_ID', $_POST['key']);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row['cnt'] != "0") {
            $parent_id = $_POST['key'];
        }
    }
    $stmt = $pdo->prepare("insert into  Comments_22(comment_id,parent_id,bug_id,author,comment_date,comment) values(null,:PARENT_ID,1,4,now(),:COMMENT)");
    $ret = $stmt->bindValue(':PARENT_ID', $parent_id);
    $stmt->bindValue(':COMMENT', $_POST['comment']);
    $ret = $stmt->execute();
    header('location: adjacency_list_accounts.php');
    exit();
}

echo '<link rel="stylesheet" href="/css/base.css">';
echo "<h2>隣接リスト(adjacency list、AccountsとJOIN)</h2>";

if (array_key_exists('key', $_GET)) {
    $parent_stmt = $pdo->prepare("SELECT comment_id FROM Comments_22 WHERE comment_id = :COMMENT_ID");
    $parent_stmt->bindValue(':COMMENT_ID', $_GET['key']);
} else {
    $parent_stmt = $pdo->prepare("SELECT comment_id FROM Comments_22 WHERE parent_id is null");
}
$parent_stmt->execute();
$parent_rows = $parent_stmt->fetchAll();

foreach ($parent_rows as $key) {
    $stmt = $pdo->prepare("SELECT c.comment_id, c.comment, a.name FROM Comments_22 c inner join Accounts a on c.author = a.account_id WHERE c.comment_id = :COMMENT_ID");
    $stmt->bindValue(':COMMENT_ID', $key['comment_id']);
    $stmt->execute();
    $row = $stmt->fetch();
    if (isset($row['comment'])) {
        echo "<ul>" . "<li>" . $row['comment_id'] . ":" . $row['comment'] . "(" . $row['name'] . ")" .  "</li>";
        commentsFindByCommentId($key['comment_id'], $pdo);
        echo "</ul>";
    }
}
function commentsFindByCommentId($key, $pdo)
{
    $stmt = $pdo->prepare("SELECT c.comment_id, c.comment, a.name FROM Comments_22 c inner join Accounts a on c.author = a.account_id WHERE c.parent_id = :PARENT_ID");
    $stmt->bindValue(':PARENT_ID', $key);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        echo "<ul>";
        echo "<li>" . $row['comment_id'] . ":" . $row['comment'] . "(" . $row['name'] . ")" .  "</li>";
        commentsFindByCommentId($row['comment_id'], $pdo);
        echo "</ul>";
    }
}

?>

<form method="POST" action="">
    <input type="text" name="key" /><br>
    <textarea name="comment">comment</textarea><br>
    <input type="submit" />
</form>