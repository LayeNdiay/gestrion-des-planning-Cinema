<?php
namespace App\Controllers;
use DateTimeZone;
use DateTime;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
USE PDO;
use Slim\Http\Response;

class Api extends Controller
{ 
    public function getCurentPlanning(RequestInterface $request,ResponseInterface $response)
    {
        $request_method = $_SERVER["REQUEST_METHOD"];
        if ($request_method ==="GET") 
        {
            $firtDayOfWeek = new DateTime('Tuesday this week ');
            $lastDayOfWeek = new DateTime('Sunday this week ');
            $last = $lastDayOfWeek->format('Y-m-d');
            $fisrt = $firtDayOfWeek->format('Y-m-d');

            $query = $this->pdo->prepare("SELECT *  FROM cine_diffusion LEFT JOIN cine_movie as cin ON cin.id_movie=cine_diffusion.id_movie LEFT JOIN cine_categorie as cat ON cat.id_movie=cine_diffusion.id_movie WHERE cine_diffusion.Jour>=:first AND cine_diffusion.Jour<=:last");

            $query->execute([
                "first"=>$fisrt,
                "last"=>$last
            ]);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
          
            for ($i=0; $i <count($result) ; $i++) { 
                $result[$i]["photo"]= 'base64_encode($result[$i]["photo"])';
             }
            header('Content-Type: application/json');
            $a = json_encode($result,JSON_UNESCAPED_SLASHES);
            echo $a;
           exit();
        }
        else
        {
            header("HTTP/1.0 405 Method Not Allowed");
        }
    }
    public function getCurentPlanningOfCinema(RequestInterface $request,ResponseInterface $response)
    {
       
        $request_method = $_SERVER["REQUEST_METHOD"];
        $idCinema = $this->idCinema($request->getAttribute("cinema"),$response);
        if ($request_method ==="GET" || $idCinema !=false) 
        {
         
            $firtDayOfWeek = new DateTime('Tuesday this week ');
            $lastDayOfWeek = new DateTime('Sunday this week ');
            $last = $lastDayOfWeek->format('Y-m-d');
            $fisrt = $firtDayOfWeek->format('Y-m-d');

            $query = $this->pdo->prepare("SELECT *  FROM cine_diffusion LEFT JOIN cine_movie as cin ON cin.id_movie=cine_diffusion.id_movie LEFT JOIN cine_categorie as cat ON cat.id_movie=cine_diffusion.id_movie LEFT JOIN cine_cinema  ON cine_cinema.id_cinema=cine_diffusion.id_cinema LEFT JOIN cine_salle as salle ON salle.id_salle= cine_diffusion.id_salle WHERE cine_diffusion.Jour>=:first AND cine_diffusion.Jour<=:last AND cine_cinema.id_cinema = :id");

            $query->execute([
                "first"=>$fisrt,
                "last"=>$last,
                "id"=>$idCinema
            ]);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
           
            for ($i=0; $i <count($result) ; $i++) { 
                $result[$i]["photo"]= base64_encode($result[$i]["photo"]);
                $result[$i]["logo"]= base64_encode($result[$i]["photo"]);
             }
            
            header('Content-Type: application/json');
            $a = json_encode($result,JSON_UNESCAPED_SLASHES);
            echo $a;
           exit();
        }
        else
        {
            header("HTTP/1.0 405 Method Not Allowed");
        }   

    }
}