<?php
namespace App\Controllers;

use DateTime;
use \App\DateTimeFrench;
use DateTimeZone;
use PDO;
use Slim\Http\UploadedFile;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Respect\Validation\Rules\Base64;
use Respect\Validation\Rules\Date;
use Respect\Validation\Rules\Length;
use Respect\Validation\Validator;
class CinemaControlleur extends Controller
  {
        public function getCinema($id)
        {
          $queryCinema= $this->pdo->query("SELECT * FROM cine_cinema where id_cinema =".$id);
             $result = $queryCinema->fetchAll(PDO::FETCH_ASSOC);
             $resCinema = $result[0];
             $resCinema["logo"] = base64_encode($resCinema["logo"]);
             return $resCinema;
        }
        public function verfieId(ResponseInterface $response ,RequestInterface $request)
        {
          $slug = $request->getAttribute("slugCinema");
          $isIdValid = false;

          $query= $this->pdo->query("SELECT * FROM cine_cinema");
          $test = $query->fetchAll(PDO::FETCH_ASSOC);
          
          for ($i=0; $i <count($test) ; $i++) { 
              if($slug === $test[$i]["slug_cinema"])
              {
                $isIdValid = true ;
              }
            }
            return $isIdValid;
        }

      

        public function chooseCinema( RequestInterface $request,ResponseInterface $response)  
          {

            $query= $this->pdo->query("SELECT * FROM cine_cinema");
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            for ($i=0; $i <count($result) ; $i++) 
            { 
                $result[$i]["logo"]= base64_encode($result[$i]["logo"]);
            }
            $this->render($response,"Page". DIRECTORY_SEPARATOR. "chooseCinema.twig",["cinemas"=>$result]);
          }   



          public function planning( RequestInterface $request,ResponseInterface $response)  
          {
            
            $id = $this->idCinema($request->getAttribute("slugCinema"),$response);  
           if($this->verfieId($response,$request)=== false)
             {
                return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
             }
             else 
                  {
                      

                      // On recupére les film
                      $queryFilm = $this->pdo->query("SELECT * FROM cine_movie ORDER by date_sortie DESC limit 10");
                      $resultFilm = $queryFilm->fetchAll(PDO::FETCH_ASSOC);

                      // On recupérére les Salle
                      $querySalle = $this->pdo->query("SELECT * FROM cine_salle WHERE id_cinema=".$id);
                      $resultSalle = $querySalle->fetchAll(PDO::FETCH_ASSOC);

                      $this->render($response,"Page". DIRECTORY_SEPARATOR. "planning.twig",["cinema"=>$this->getCinema($id) ,"films"=>$resultFilm,"salles"=>$resultSalle]);
                }
          }
        public function savePlanning(RequestInterface $request,ResponseInterface $response)
        {
          $id = $this->idCinema($request->getAttribute("slugCinema"),$response);

              $create =date("Y-m-d H:i:s",time());
              $slugFilm = $request->getParam("nomFilm");
              $slugSalle = $request->getParam("nomSalle");
              $langue = $request->getParam("langue");
              $qualite = $request->getParam("qualite");
              $jours = $request->getParam("Jour");
              $public = $request->getParam("public");
              $debut = $request->getParam("debut");
              $status = $request->getParam("status");
              $requette = $this->pdo->prepare("INSERT INTO cine_diffusion(id_salle,id_cinema,id_movie,langue,Qualite,Jour,Debut,public,Status,created_At,slug_diffusion) VALUES(:id_salle,:id_cinema,:id_movie,:langue,:Qualite,:Jour,:Debut,:public,:status,:created_At ,:slug_diffusion)");
             for ($i=0; $i <count($slugFilm) ; $i++) 
              { 
                  $requette->execute([
                    "id_salle"=>$this->idSalle($slugSalle[$i],$response),
                    "id_cinema"=>$id,
                    "id_movie"=>$this->idMovie($slugFilm[$i],$response),
                    "langue"=>$langue[$i],
                    "Qualite"=>$qualite[$i],
                    "Jour"=>$jours[$i],
                    "Debut"=>$debut[$i],
                    "public"=>$public[$i],
                    "status"=>$status[$i],
                    "created_At"=>$create,
                    "slug_diffusion"=>$slugFilm[$i].'-'.$create
                ]);
             }
              $films = [];
              $dates = [];
              $DTZ = new DateTimeZone('Europe/Paris');
              $salles=[];
              $requetteFilm = $this->pdo->prepare("SELECT * FROM cine_movie WHERE id_movie=:id");
              $requetteSalle = $this->pdo->prepare("SELECT * FROM cine_salle WHERE id_salle=:idSalle");
              for ($i=0; $i <count($slugFilm) ; $i++) 
             { 
                  $requetteFilm->execute([
                    "id"=>$this->idMovie($slugFilm[$i],$response)
                ]);
                $resultFilm= $requetteFilm->fetchAll(PDO::FETCH_ASSOC);
                $films[$i]=$resultFilm[0];
                $films[$i]["photo"] =base64_encode($films[$i]["photo"]);
                $date = new DateTimeFrench($jours[$i], $DTZ);
                $dates[$i] = $date->format('l j F');
    
                $requetteSalle->execute([
                  "idSalle"=>$this->idSalle($slugSalle[$i],$response)
                ]);
                $resultatSalle = $requetteSalle->fetchAll(PDO::FETCH_ASSOC);
                $salles[$i]= $resultatSalle[0];
             }
            
             return $this->render($response,"Page". DIRECTORY_SEPARATOR. "viewOnePlanning.twig",["films"=>$films,"date"=>$dates,"qualite"=>$qualite,"salle"=>$salles,"cinema"=>$this->getCinema($id)]);
          

          }   
        


        public function viewAgenda(RequestInterface $request,ResponseInterface $response)
        {
          $id = $this->idCinema($request->getAttribute("slugCinema"),$response);
            
          if($this->verfieId($response,$request)=== false)
            {
               return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
            }
          else 
          {
            $firtDayOfWeek = new DateTime('Tuesday this week ');
            $lastDayOfWeek = new DateTime('Sunday this week ');
            $last = $lastDayOfWeek->format('Y-m-d');
            $firt = $firtDayOfWeek->format('Y-m-d');
            
            $requestDifffusion = $this->pdo->prepare("SELECT * FROM cine_diffusion WHERE Jour >= :first AND Jour <= :dernier AND id_cinema = :idCinema " );
            $requestDifffusion->execute([
              "first"=>$firt,
              "dernier"=>$last,
              "idCinema"=>$id
            ]);

            $diffusion =$requestDifffusion->fetchAll(PDO::FETCH_ASSOC);

            $requetteCategorie = $this->pdo->prepare("SELECT * FROM cine_categorie WHERE id_movie = :idCategorie");
            $requetteFilm = $this->pdo->prepare("SELECT * FROM cine_movie WHERE id_movie = :idFilm ");
            $requetteSalle = $this->pdo->prepare("SELECT * FROM cine_salle WHERE id_salle = :idSalle AND id_cinema = :idCinema");

            $films =[];
            $categorie =[];
            $arrayIdMovie = [];
            $diff=[];
            $salles =[];
            $public = [];

            for ($i=0; $i <count($diffusion) ; $i++) 
            { 

              $requetteFilm->execute([
                  "idFilm"=>$diffusion[$i]["id_movie"]
                ]);

              $resultFilm= $requetteFilm->fetchAll(PDO::FETCH_ASSOC);
              $name = $resultFilm[0]["titre"];

              if ( array_search($diffusion[$i]["id_movie"],$arrayIdMovie)===false ) 
              {
                $films[$name]=$resultFilm[0];
                $films[$name]["photo"] =base64_encode($films[$name]["photo"]);
                $public[] = $diffusion[$i]["public"];

                $arrayIdMovie[] = $diffusion[$i]["id_movie"];
              }
              
              $requetteSalle->execute([
                "idCinema"=>$id,
                "idSalle"=>$diffusion[$i]["id_salle"]
              ]);

              $resultatSalle = $requetteSalle->fetchAll(PDO::FETCH_ASSOC);
             

              $requetteCategorie->execute([
                "idCategorie"=>$diffusion[$i]["id_movie"]
              ]);
              $resultCategorie=$requetteCategorie->fetchAll(PDO::FETCH_ASSOC);
             


               $categorie[$name] = "";
               foreach ($resultCategorie as $value) {
                  $categorie[$name] =  $categorie[$name]==="" ? $value["categorie_mame"] : $categorie[$name] . ",". $value["categorie_mame"];  
               }



               $day=new DateTime($diffusion[$i]["Jour"]);
               if ($day->format("l") === "Tuesday") {
                $diff["Mardi"][$name] =   $diffusion[$i];
                $salles["Mardi"][$name]=$resultatSalle[0]["nom_salle"];
               }
               if ($day->format("l") === "Wednesday") {
                $diff["Mercredi"][$name] =   $diffusion[$i];
                $salles["Mercredi"][$name]=$resultatSalle[0]["nom_salle"];

               }
               if ($day->format("l") === "Thursday") {
                $diff["Jeudi"][$name] =   $diffusion[$i];
                $salles["Jeudi"][$name]=$resultatSalle[0]["nom_salle"];

               }
               if ($day->format("l") === "Friday") {
                $diff["Vendredi"][$name] =   $diffusion[$i];
                $salles["Vendredi"][$name]=$resultatSalle[0]["nom_salle"];

               }
               if ($day->format("l") === "Saturday") {
                $diff["Samedi"][$name] =   $diffusion[$i];
                $salles["Samedi"][$name]=$resultatSalle[0]["nom_salle"];

               }
               if ($day->format("l") === "Sunday") {
                $diff["Dimanche"][$name] =   $diffusion[$i];
                $salles["Dimanche"][$name]=$resultatSalle[0]["nom_salle"];

               }

            }
            
            return $this->render($response,"Page". DIRECTORY_SEPARATOR. "viewAgenda.twig",["id"=>$id,"cinema"=>$this->getCinema($id) ,"diff"=>$diff,"films"=>$films,"categorie"=>$categorie,"salle"=>$salles,"publics"=>$public]);
                
          }



        } 




        public function viewOneAgenda(RequestInterface $request,ResponseInterface $response,$arg)
        {
          $id =  $this->idCinema($request->getAttribute("slugCinema"),$response);
          if($this->verfieId($response,$request)=== false)
          {
            return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
          }
          else 
          {
            
            $idDiffusion = $this->idDiffusion($request->getAttribute("diffusion"),$response);
            $requette = $this->pdo->prepare("SELECT * FROM cine_diffusion WHERE id_diffussion = :id_diffusion");
            $requette->execute([
              "id_diffusion"=>$idDiffusion
            ]);
            $resultdiffusion = $requette->fetchAll(PDO::FETCH_ASSOC);
            if (empty($resultdiffusion)) {
              return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");

            }
            $diffusion = $resultdiffusion[0];
            

            $queryFilm = $this->pdo->query("SELECT * FROM cine_movie WHERE id_movie = " .$diffusion["id_movie"]);
            $resultFilm = $queryFilm->fetchAll(PDO::FETCH_ASSOC);

            // On recupérére les Salle
            $querySalle = $this->pdo->query("SELECT * FROM cine_salle WHERE id_cinema=".$id);
            $resultSalle = $querySalle->fetchAll(PDO::FETCH_ASSOC);

            $this->render($response,"Page". DIRECTORY_SEPARATOR. "planning.twig",["cinema"=>$this->getCinema($id) ,"films"=>$resultFilm,"salles"=>$resultSalle,"diff"=>$diffusion]);

          }
        }

        public function updatePlanning(RequestInterface $request,ResponseInterface $response)
        {
          $id =  $this->idCinema($request->getAttribute("slugCinema"),$response);
          if($this->verfieId($response,$request)=== false)
          {
            return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
          }
          else 
          {
            $idDiffusion = $this->idDiffusion($request->getAttribute("diffusion"),$response);

            $update =date("Y-m-d H:i:s",time());
            $slugFilm = $request->getParam("nomFilm");
            $slugSalle = $request->getParam("nomSalle");
            $langue = $request->getParam("langue");
            $qualite = $request->getParam("qualite");
            $jours = $request->getParam("Jour");
            $public = $request->getParam("public");
            $debut = $request->getParam("debut");
            $status = $request->getParam("status");
            //die(var_dump($qualite));
            $requette = $this->pdo->prepare("INSERT INTO cine_diffusion(id_salle,id_cinema,id_movie,langue,Qualite,Jour,Debut,public,Status,created_At,slug_diffusion) VALUES(:id_salle,:id_cinema,:id_movie,:langue,:Qualite,:Jour,:Debut,:public,:status,:created_At ,:slug_diffusion)");

            $requette = $this->pdo->prepare("UPDATE cine_diffusion SET id_salle=:id_salle,langue=:langue,Qualite=:Qualite,Jour=:Jour,Debut=:Debut,public=:public,Status= :status,updated_At=:updated_At WHERE id_diffussion =:id");
           
                $requette->execute([
                  "id_salle"=>$this->idSalle($slugSalle[0],$response),
                  "langue"=>$langue[0],
                  "Qualite"=>$qualite[0],
                  "Jour"=>$jours[0],
                  "Debut"=>$debut[0],
                  "public"=>$public[0],
                  "status"=>$status[0],
                  "updated_At"=>$update,
                  "id"=>$idDiffusion
              ]);
            $films = [];
            $dates = [];
            $DTZ = new DateTimeZone('Europe/Paris');
            $salles=[];
            $requetteFilm = $this->pdo->prepare("SELECT * FROM cine_movie WHERE id_movie=:id");
            $requetteSalle = $this->pdo->prepare("SELECT * FROM cine_salle WHERE id_salle=:idSalle");
            
                $requetteFilm->execute([
                  "id"=>$this->idMovie($slugFilm[0],$response)
              ]);
              $resultFilm= $requetteFilm->fetchAll(PDO::FETCH_ASSOC);
              $films[0]=$resultFilm[0];
              $films[0]["photo"] =base64_encode($films[0]["photo"]);
              $date = new DateTimeFrench($jours[0], $DTZ);
              $dates[0] = $date->format('l j F');
  
              $requetteSalle->execute([
                "idSalle"=>$this->idSalle($slugSalle[0],$response)
              ]);
              $resultatSalle = $requetteSalle->fetchAll(PDO::FETCH_ASSOC);
              $salles[0]= $resultatSalle[0];
          
           return $this->render($response,"Page". DIRECTORY_SEPARATOR. "viewOnePlanning.twig",["films"=>$films,"date"=>$dates,"qualite"=>$qualite,"salle"=>$salles,"cinema"=>$this->getCinema($id)]);
            
          }
        }
  }