<?php
function unorderedList($comment_id, $comment, $name)
{
    return "<li>" .
        "<div onclick=\"obj=document.getElementById('open" . $comment_id . "').style; obj.display=(obj.display=='none')?'block':'none';\">
        <a style=\"cursor:pointer;\">" . $comment_id . ":" . htmlspecialchars($comment, ENT_QUOTES) . "(" . $name . ")▼折畳み</a>
        </div>
        <div id=\"open" . $comment_id . "\" style=\"display:none;clear:both;\">
        <form method=\"POST\" action=\"\">
        <input type=\"hidden\" name=\"key\" value=\"" . $comment_id . "\"/><br>
        <textarea name=\"comment\"></textarea><br>
        <br><input type=\"submit\" />
        </form>
        </div>" . "</li>";
}

function newPost()
{
    return "<form method=\"POST\" action=\"\">
    <div>新規コメント<br>
    <textarea name=\"comment\"></textarea><br></div>
    <br><input type=\"submit\" />
    </form>";
}

function toTop()
{
    return "<div><a href=\"/\">Top</></div>";
}
