<?php
namespace App\Controllers;

use PDO;
use Slim\Http\UploadedFile;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
class PageController extends Controller
    {   

        public function home(RequestInterface $request,ResponseInterface $response)
        {
           $this->render($response,"Page". DIRECTORY_SEPARATOR. "home.twig");
        }

        public function postCinema(RequestInterface $request,ResponseInterface $response)
        {
            $errors = [];
            Validator::notEmpty()->validate($request->getParam('titreFilm')) || $errors["titreFilm"] = "Vous devez donner le titre du film" ;
            Validator::notEmpty()->validate($request->getParam('dateSortie')) || $errors["dateSortie"] = "vous devez donner la date de sortie" ;
            Validator::notEmpty()->validate($request->getParam('durre')) || $errors["durre"] = "vous devez donner la durée du film" ;
            Validator::notEmpty()->validate($request->getParam('categorie')) || $errors["categorie"] = "Veillez donner la ou les catégorie(s) du film" ;
            Validator::notEmpty()->validate($request->getParam('description')) || $errors["description"] = "vous devez donner la description du film" ;
           
            if (!empty($errors )) {
               $errors["error"]='Le formulaire est mal rempli';
                $this->flash($errors);
                return $this->redirect($response,'addFilm',302);
           }
           
           else {
               $titre = $request->getParam('titreFilm');
               $fichiers=$request->getuploadedFiles() ;
               $date = $request->getParam('dateSortie');
               $durre = $request->getParam('durre');
               $categories=$request->getParam('categorie');
               $description = $request->getParam('description');
               
               $lienYoutube = $request->getParam('lienYoutuber')==null ?  "Aucun lien vers youtube" : $request->getParam('lienYoutuber');
               $slug = $this->slugify($request->getParam('titreFilm'));
               $created = date("Y-m-d H:i:s",time());
               $lienTeaser= null;
              if( $fichiers['lienTeaser'] !=null)
              {
                $nameFichier =$fichiers['lienTeaser']->getClientFilename();
               
                $directoryFichier = TEASER .DIRECTORY_SEPARATOR . $nameFichier;
                $lienTeaser= 'Teaser/'.$nameFichier;
                copy($_FILES["lienTeaser"]["tmp_name"],$directoryFichier);
              }
               $img =file_get_contents($fichiers['photo']->file);
                $query = $this->pdo->prepare("INSERT INTO cine_movie(slug_movie,date_sortie,photo,titre,durre,description,lien_youtube,lien_teaser,created_At) VALUES(:slug,:dateSortie,:photo,:titre,:durre,:description,:lienYoutube,:lienTeaser,:create)");
                $query->execute(
                    [
                        "slug"=>$slug,
                        "dateSortie"=>$date,
                        "photo"=>$img,
                        "titre"=>$titre,
                        "durre"=>$durre,
                        "description"=>$description,
                        "lienYoutube"=>$lienYoutube,
                         "lienTeaser"=>$lienTeaser,
                         "create"=>$created,
                    ]);
                $idFilm=(int) $this->pdo->lastInsertId();
                $categories= explode(",",$categories);
                $query2 = $this->pdo->prepare("INSERT INTO cine_categorie(id_movie,categorie_mame,created_At) VALUES(:id,:nomCategorie,:create)");
                foreach( $categories as $categorie)
                {
                        $query2->execute([
                            "id"=> $idFilm,
                            'nomCategorie'=>$categorie,
                             "create"=>$created
                        ]);
                }
            $this->flash(["success"=>"le formulaire est bien rempli"]);
            return $this->redirect($response,'addFilm',303);
           }
        }

        
    }
