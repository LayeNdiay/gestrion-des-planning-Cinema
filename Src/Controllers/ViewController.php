<?php
namespace App\Controllers;

use PDO;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Respect\Validation\Rules\Slug;
use Respect\Validation\Validator;


class ViewController extends Controller
    {
          public function viewMovies( RequestInterface $request,ResponseInterface $response)  
          {
             $this->showMovies($request,$response,"SELECT * FROM cine_movie ORDER by date_sortie DESC","liste.twig");
          }
          public function viewMovie( RequestInterface $request,ResponseInterface $response)  
          {

                if ($request->getAttribute("slug") ==  null ) {
                    return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
                }
                else
                {
                    return  $this->showMovies($request,$response,"SELECT * FROM cine_movie WHERE slug_movie ='" . $request->getAttribute("slug")."'" ,"viewOneMovie.twig");
                }
           }
          public function searchMovies( RequestInterface $request,ResponseInterface $response)  
           {
              if ($request->getParam("nomFilm") === "")
              {
                 return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");

              }
              else
              {
               return  $this->showMovies($request,$response,"SELECT * FROM cine_movie WHERE 	titre like  '".$request->getParam("nomFilm"). "%' ORDER by date_sortie DESC","liste.twig");
              }
            }

            public function editMovie( RequestInterface $request,ResponseInterface $response)  
            {
               if ($request->getAttribute("slug") ==  null ) {
                  return $this->render($response,"Page". DIRECTORY_SEPARATOR. "noresult.twig");
               }
                  else
                  {
                      return  $this->showMovies($request,$response,"SELECT * FROM cine_movie WHERE slug_movie ='" . $request->getAttribute("slug")."'" ,"editMovie.twig");
                  }
             }
             public function alterMovie(RequestInterface $request,ResponseInterface $response)
             {
               $slug = $request->getAttribute("slug");
            $errors = [];
            Validator::notEmpty()->validate($request->getParam('titreFilm')) || $errors["titreFilm"] = "Vous devez donner le titre du film" ;
            Validator::notEmpty()->validate($request->getParam('dateSortie')) || $errors["dateSortie"] = "vous devez donner la date de sortie" ;
            Validator::notEmpty()->validate($request->getParam('durre')) || $errors["durre"] = "vous devez donner la durée du film" ;
            Validator::notEmpty()->validate($request->getParam('categorie')) || $errors["categorie"] = "Veillez donner la ou les catégorie(s) du film" ;
            Validator::notEmpty()->validate($request->getParam('description')) || $errors["description"] = "vous devez donner la description du film" ;
           
            if (!empty($errors )) {
           return  $response->withStatus(302)->withHeader('location',$this->container->router->pathFor("edit.slug",["slug"=>$slug]));
           }
           
           else {
               $titre = $request->getParam('titreFilm');
               $categories=$request->getParam('categorie');
               $date = $request->getParam('dateSortie');
               $durre = $request->getParam('durre');
               $description = $request->getParam('description');
               $lienTeaser = $request->getParam('lienTeaser') ==null ? "Teaser indisponible" : $request->getParam('lienTeaser') ;
               $lienYoutube = $request->getParam('lienYoutuber')==null ?  "Aucun lien vers youtube" : $request->getParam('lienYoutuber');
               $created = date("Y-m-d H:i:s",time());
               

                $query = $this->pdo->prepare("UPDATE cine_movie SET   date_sortie=:dateSortie,titre=:titre,durre=:durre,description=:description,lien_youtube=:lienYoutube,lien_teaser=:lienTeaser,updated_At=:create WHERE slug_movie = :slug");
                $query->execute(
                    [
                        "slug"=>$slug,
                        "dateSortie"=>$date,
                        "titre"=>$titre,
                        "durre"=>$durre,
                        "description"=>$description,
                        "lienYoutube"=>$lienYoutube,
                         "lienTeaser"=>$lienTeaser,
                         "create"=>$created
                    ]);
                    
                $id = $this->idMovie($slug,$response); 
               $delete= $this->pdo->prepare("DELETE FROM cine_categorie WHERE id_movie=:id");
               $delete->execute(["id"=>$id]);
                $query2 = $this->pdo->prepare("INSERT INTO cine_categorie(id_movie,categorie_mame,created_At) VALUES(:id,:nomCategorie,:create)");
                
                $categories= explode(",",$categories);
                foreach( $categories as $categorie)
                {
                   
                       
                        $query2->execute([
                            "id"=> $id,
                            'nomCategorie'=>$categorie,
                             "create"=>$created
                        ]);
                }
               return  $response->withStatus(200)->withHeader('location',$this->container->router->pathFor("view.slug",["slug"=>$slug]));

              
             }


    }
   

   }