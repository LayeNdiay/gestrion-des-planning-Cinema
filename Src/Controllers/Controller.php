<?php
namespace App\Controllers;
use PDO;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class  Controller
{
       protected  $container;
       protected $pdo ;
        public function __construct( $container )
        {
                $this->container=$container;
                $this->pdo =  new PDO("mysql:host=127.0.0.1;dbname=cinema;charset=utf8","root","");
        }
        public function render(ResponseInterface $response,$file,$params= [])
        {
           $this->container->view->render($response,$file,$params);
         
        }
        public function flash ($type)
        {
           if (!isset($_SESSION['flash'])) {
            $_SESSION['flash']= [];
           }
           $_SESSION['flash']=$type;
          return  $_SESSION['flash'] ;
        }
        public function redirect( ResponseInterface $response,$name,$status){
           
           return  $response->withStatus($status)->withHeader('location',$this->container->router->pathFor($name));
        }
         


        public static function slugify($text, string $divider = '-')
         {
               // replace non letter or digits by divider
               $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

               // transliterate
               $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

               // remove unwanted characters
               $text = preg_replace('~[^-\w]+~', '', $text);

               // trim
               $text = trim($text, $divider);

               // remove duplicate divider
               $text = preg_replace('~-+~', $divider, $text);

               // lowercase
               $text = strtolower($text);

               if (empty($text)) {
                  return 'n-a';
               }

               return $text;
         }
         

         public function idMovie($slug,ResponseInterface $response)
         {
            $queryMovie= $this->pdo->prepare("SELECT * FROM cine_movie where slug_movie =:slug");
            
            $queryMovie->execute([
               "slug"=>$slug
            ]);
             $result = $queryMovie->fetchAll(PDO::FETCH_ASSOC); 

            if (empty($result) || $result===NULL) {
                $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
                return false;
            }
            else {
              
               $resMovie = $result[0];
               return $resMovie["id_movie"];
            }
         
         }
         public function idCinema($slug,ResponseInterface $response)
         {
            $queryCinema= $this->pdo->prepare("SELECT * FROM cine_cinema where slug_cinema =:slug");
            
            $queryCinema->execute([
               "slug"=>$slug
            ]);
            $result = $queryCinema->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result ) || $result===NULL) {
             $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
             return false;
            }
            else{
             $resMovie = $result[0];
             return $resMovie["id_cinema"];
            }
 
           
         }
         public function idSalle($slug,ResponseInterface $response)
         {
            $querySalle= $this->pdo->query("SELECT * FROM cine_salle where slug_salle ='".$slug."'");
            if ($querySalle === false) {
                $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
            }
            else {
               $result = $querySalle->fetchAll(PDO::FETCH_ASSOC);
            $resMovie = $result[0];
            return $resMovie["id_salle"];
            }
            
         }
         public function idDiffusion($slug,ResponseInterface $response)
         {
            $querydiffusion= $this->pdo->query("SELECT * FROM cine_diffusion where slug_diffusion ='".$slug."'");
            if ($querydiffusion === false) {
               $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
               return false;
            }
            else {
               $result = $querydiffusion->fetchAll(PDO::FETCH_ASSOC);
            $resMovie = $result[0];
            return $resMovie["id_diffussion"];
            }
           
         }



        public function showMovies( RequestInterface $request,ResponseInterface $response, $query, $page)  
        {
            $query= $this->pdo->query($query);

            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            
            $categorie = [];
            for ($i=0; $i <count($result) ; $i++) { 
               $result[$i]["photo"]= base64_encode($result[$i]["photo"]);
               $query2 = $this->pdo->query("SELECT * FROM cine_categorie WHERE id_movie =  ". $result[$i]["id_movie"]  );
               $res = $query2->fetchAll(PDO::FETCH_ASSOC);
               $name = $result[$i]["titre"];
               $categorie[$name] = "";
               foreach ($res as $value) {
                  $categorie[$name] =  $categorie[$name]==="" ? $value["categorie_mame"] : $categorie[$name] . ",". $value["categorie_mame"];  
               }
            }
            if (empty($categorie)) {
               $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig"); 
            }
            else
            {
            $this->render($response,"Page". DIRECTORY_SEPARATOR. $page,["pdo"=>$result ,"categorie"=>$categorie]);

            }
        }  
        
   
   
   
   
   
   
   
   
}