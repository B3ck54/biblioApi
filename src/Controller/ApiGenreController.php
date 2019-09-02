<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class ApiGenreController extends AbstractController
{
    /**
     * @Route("/api/genres", name="api_genres", methods={"GET"})
     */
    public function list(GenreRepository $repo, SerializerInterface $serializer) // besoin de mon repository pour aller chercher les infos en bdd
    {
        //je vais aller chercher mes différents genres et je vais les stocker dans $genres, je vais utiliser le repository et je demande de faire appel à finfAll
        $genres=$repo->FindAll();

        //après les avoir récupérer je les transforme en format JSON, je les stockes dans une variable $resultat.
        //Je fais appel au serializer et je vais lui demande de serialiser genre + le format dans lequel je veux serialiser
        $resultat= $serializer->serialize(
            $genres,
            'json',
            [
                'groups' => ['listGenreFull']
            ]
        );

        //je ne retourne pas une vue mais une new JsonResponse. 
        //Je veux retourner les résultats, avec le code status 200 (reponse http), 
        //tableau de contexte : vide
        //il faut préciser si le résultat est déjà en json, ici true
        return new JsonResponse($resultat,200,[],true);
    }

    /**
     * @Route("/api/genres/{id}", name="api_genres_show", methods={"GET"})
     */
    public function show(Genre $genre, SerializerInterface $serializer) // pas besoin de mon repository car déjà récupérer grâce à l'id
    {
        $resultat= $serializer->serialize(
            $genre,
            'json',
            [
                'groups' => ['listGenreFull']
            ]
        );

        return new JsonResponse($resultat,200,[],true);
    }

    /**
     * @Route("/api/genres", name="api_genres_create", methods={"POST"})
     */
    public function create(Request $request, ObjectManager $manager, SerializerInterface $serializer) // Besoin de la request car je vais récupérer les informations de ma requête
    {
        // je récupère les informations de ma request dans $date
        $data = $request -> getContent(); //request tu me donnes getContent, le contenu de la requête (les éléments postés)

        // $genre = new Genre (); // je crée mon genre, c'est là dedans qu'on désérialise les informations de la requête
        // $serializer->deserialize($data,Genre::class,'json',['object_to_populate'=>$genre]); // deserialisation de ce qu'il a dans data pour en former un objet de type genre (Genre::class) et je previens qu'on est en présence de JSON
        // //'object_to_populate = rempli l'objet avec les infos que tu auras désérialisé de $data

        // ou
        $genre=$serializer->deserialize($data, Genre::class, 'json');
        // il faut que j'enregistre $genre dans la base de données pour ce faire j'ai besoin de l'object manager et je vais persister l'objet $genre
        $manager -> persist ($genre);
        $manager->flush(); // et je le flush pour l'inscrire en base de données

        // ici je ne donne pas de résultat donc il est a null, dans les [] c'est ce qu'on va donner dans le header (context)
        // dans context tu vas me remplir le header "location", renvoi le lien avec ce nouvel élément avec son id, car par auto incrémentation directement créé dans la bdd
        // pour joindre ce nouveau genre qu'on vient de créer il faudra taper cet URL, on va aller chercher la méthode getId de l'objet qui vient d'être créé.
        
        return new JsonResponse(
            "Le genre a bien été créé", // null,
            201,
            ["location" => "api/genres/".$genre->getId()

        ],true); // ici je ne donne pas de résultat donc il est a null (on ne renvoit aucun corps, aucun body), dans les [] c'est ce qu'on va donner dans le header (context)
    }

     /**
     * @Route("/api/genres/{id}", name="api_genres_update", methods={"PUT"})
     */
    public function update(Genre $genre, Request $request,  ObjectManager $manager, SerializerInterface $serializer) //genre qu'il va récupérer automatique en bdd par rapport à l'id qu'il aure trouvé.
    // avec Genre $genre on récupère l'object existant et il va falloir qu'on lui dise
    {
        $data = $request -> getContent();

        $serializer->deserialize($data,Genre::class,'json',['object_to_populate'=>$genre]); //désérialize le data et met à jour l'objet genre

        $manager -> persist ($genre);
        $manager->flush(); // et je le flush pour l'inscrire en base de données

        return new JsonResponse('le genre a bien été modifié',200,[],true);
    }

     /**
     * @Route("/api/genres/{id}", name="api_genres_delete", methods={"DELETE"})
     */
    public function delte(Genre $genre,ObjectManager $manager) 
    {
        // Pas besoin de récuperer l'id il l'a déjà grâce à la route
        $manager ->remove ($genre);
        $manager->flush(); //active la suppression

        return new JsonResponse('effacé',200,[],false); //false car je n'envoie rien qui est en json

    }
}

// Construire des routes qui  vont correspondre à chacun de nos poinst d'entrée.
// Une route pour obtenir la liste des genres, la création d'un genre, bref une route pour chaque
// élément du CRUD