<?php
echo "aaa";

require_once __DIR__ . "/../app/bootstrap.php";

var_dump($app);

$app->get('/',function() use($app) {
echo "bbb";

//    return $app['twig']->render('index.twig');
});
