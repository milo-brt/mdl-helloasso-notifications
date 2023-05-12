<?php

    // composer autoloader
    require 'vendor/autoload.php';

    // project loader
    require 'Controllers/NotificationController.php';
    require 'Models/FileStoreModel.php';

    // router
    $router = new AltoRouter();
    $controller = new Controllers\NotificationController();
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    
    $dotenv->load();
    
    $router->map('GET', '/', 'get');
    $router->map('POST', '/', 'post');

    $match = $router->match();
    
    if(is_array($match)) {
        list($view, $data) = $controller->{$match['target']}($match['params']);
        if($view != null)
            require __DIR__ . "/Views/$view.phtml";    
    }

?>