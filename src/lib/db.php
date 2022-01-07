<?php
require_once('./config/db_config.php');
try {
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $db_host, $db_name), $db_user, $db_pass, array(PDO::ATTR_EMULATE_PREPARES => false));
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
