<?php
// Get container
$container = $app->getContainer();
use Slim\Views\Twig;
use \Slim\Views\TwigExtension;
// Register component on container
$container['view'] = function ($container) {
    $view = new Twig( DIR .DIRECTORY_SEPARATOR ."Src".DIRECTORY_SEPARATOR."Views", [
        'cache' => false,
        'debug'=>true
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($container['router'], $basePath));
    return $view;

};