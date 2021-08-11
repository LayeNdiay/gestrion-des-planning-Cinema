<?php
namespace App\Middleware;
use \slim\Http\Request;
use \slim\Http\Response;
class Flashmiddlewra
{
    private  $twig;

    public function __construct(\twig\Environment $twig)
    {
        $this->twig = $twig;
    }
    public function __invoke( Request $request, Response $response,$next)
    {
        $this->twig->addGlobal( 'flash',isset($_SESSION["flash"]) ? $_SESSION["flash"] : []);
        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }
        return $next($request, $response);
    }
    
}
