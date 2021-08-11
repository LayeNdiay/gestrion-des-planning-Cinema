<?php
namespace App\Middleware;
use \slim\Http\Request;
use \slim\Http\Response;
class OldMidleware
{
    private  $twig;

    public function __construct(\twig\Environment $twig)
    {
        $this->twig = $twig;
    }
    public function __invoke( Request $request, Response $response,$next)
    {
        $this->twig->addGlobal( 'old',isset($_SESSION["old"]) ? $_SESSION["old"] : []);
        $response = $next($request, $response);
        if (isset($_SESSION['old'])) {
            unset($_SESSION['old']);
        }
        if ($response->getStatusCode() == 302) {
            var_dump($response->getStatusCode() );
            $_SESSION["old"] = $request->getParams();
       }
        return $response;
    }
    
}