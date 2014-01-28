<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Application;

$app = new Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__ . '/../views',
    'twig.class_path' => __DIR__ . '/../vendor/twig/lib',
));

$app['db'] = function() use($app){
    try{
        $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', 'localhost', 'chapter2'), 'root', 'vagrant', array(PDO::ATTR_EMULATE_PREPARES => false));
    }catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die;
    }
    return $pdo;
};
