<?php

use App\Middleware\Flashmiddlewra;
$public = dirname(__DIR__);

define("DIR",$public);
define("TEASER",__DIR__.DIRECTORY_SEPARATOR."Teaser");

require_once $public. DIRECTORY_SEPARATOR.'/vendor/autoload.php';
session_start();
$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);




require_once $public.DIRECTORY_SEPARATOR."Src".DIRECTORY_SEPARATOR. 'container.php' ;

$app->add( new \App\Middleware\Flashmiddlewra( $container->view->getEnvironment() ) );
$app->add( new \App\Middleware\OldMidleware( $container->view->getEnvironment() ) );

$app->get("/ajout-film", \App\Controllers\PageController::class . ':home' )->setName("addFilm");
$app->post("/ajout-film", \App\Controllers\PageController::class . ':postCinema' )->setName("addFilm");
$app->get("/voire-films", \App\Controllers\ViewController::class . ':viewMovies' )->setName("viewFilm");
$app->get("/voire-film/{slug}", \App\Controllers\ViewController::class . ':viewMovie' )->setName("view.slug");
$app->get("/search", \App\Controllers\ViewController::class . ':searchMovies' )->setName("search");
$app->get("/modifier-Film/{slug}", \App\Controllers\ViewController::class . ':editMovie' )->setName("edit.slug");
$app->post("/modifier-Film/{slug}", \App\Controllers\ViewController::class . ':alterMovie' )->setName("edit.slug");
$app->get("/", \App\Controllers\CinemaControlleur::class . ':chooseCinema' )->setName("choose");
$app->get("/planning/{slugCinema}", \App\Controllers\CinemaControlleur::class . ':planning' )->setName("planning.cinema");
$app->post("/planning/{slugCinema}", \App\Controllers\CinemaControlleur::class . ':savePlanning' )->setName("planning.cinema");
$app->get("/agenda/{slugCinema}", \App\Controllers\CinemaControlleur::class . ':viewAgenda' )->setName("agenda.cinema");
$app->get("/cinema/{slugCinema}/{diffusion}", \App\Controllers\CinemaControlleur::class . ':viewOneAgenda' )->setName("agenda.slug.diffusion");
$app->post("/cinema/{slugCinema}/{diffusion}", \App\Controllers\CinemaControlleur::class . ':updatePlanning' )->setName("agenda.slug.diffusion");


$app->run();