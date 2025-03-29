<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/author", name="author")
 * @OA\Tag(name="Authors")
 */
class AuthorController extends AbstractSimpleApiController
{
    public const entityClass = Author::class;
    public const entityCreateTypeClass = AuthorType::class;
    public const entityUpdateTypeClass = AuthorType::class;

    /**
     * @Route("", methods={"POST"}, name="_create")
     * @OA\RequestBody(
     *   required=true,
     *   @OA\JsonContent(ref=@Model(type=self::entityCreateTypeClass))
     * ),
     * @OA\Response(response=200, description="Successful operation"),
     */
    public function create(Request $request): Response
    {

        $form = $this->createForm(static::entityCreateTypeClass);


        $form->submit($request->request->all());


        if ($form->isValid()) {
            // Enregistrement en base de données
            $entity = $this->persistAndFlush($form->getData());


            return static::renderEntityResponse($entity, static::serializationGroups, [], Response::HTTP_CREATED);
        }


        return $this->throwUnprocessableEntity($form);
    }

    /**
     * @Route("/{id}", methods={"GET"}, requirements={"id"="\d+"}, name="_get")
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=404, description="Entity not found"),
     */
    public function read(Request $request): Response
    {
        // Récupération de l'entité Author par son ID
        $entity = $this->getEntityOfRequest($request);

        // Retourne l'entité au format JSON
        return static::renderEntityResponse($entity, static::serializationGroups, [], Response::HTTP_OK, []);
    }

    /**
     * @Route("", methods={"GET"}, name="_getAll")
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=500, description="Server error"),
     */
    public function getAll(): Response
    {
        // Récupération de tous les auteurs
        $entities = $this->getRepository(self::entityClass)->findAll();

        // Si aucune entité n'est trouvée, retourne une réponse avec une liste vide
        if (empty($entities)) {
            return new Response('No authors found', Response::HTTP_NOT_FOUND);
        }

        // Retourne les entités sérialisées en JSON
        return static::renderEntityResponse($entities, static::serializationGroups, [], Response::HTTP_OK, []);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, requirements={"id"="\d+"}, name="_update")
     * @OA\RequestBody(required=true, @OA\JsonContent(ref=@Model(type=self::entityUpdateTypeClass))),
     * @OA\Response(response=200, description="Successful operation"),
     * @OA\Response(response=404, description="Entity not found"),
     */
    public function update(Request $request): Response
    {
        // Récupération de l'entité Author
        $entity = $this->getEntityOfRequest($request);

        // Si l'entité n'est pas trouvée
        if (!$entity) {
            return new Response('Entity not found', Response::HTTP_NOT_FOUND);
        }

        // Création du formulaire de mise à jour
        $form = $this->createForm(static::entityUpdateTypeClass, $entity);
        $form->submit($request->request->all(), false);

        // Si le formulaire est valide, on sauvegarde
        if ($form->isValid()) {
            // Mise à jour de l'entité en base de données
            $entity = $this->persistAndFlush($entity);

            // Retourne l'entité mise à jour
            return static::renderEntityResponse($entity, static::serializationGroups, [], Response::HTTP_OK);
        }

        // Si le formulaire est invalide, on lance une exception de type 422 (Unprocessable Entity)
        return $this->throwUnprocessableEntity($form);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, requirements={"id"="\d+"}, name="_delete")
     * @OA\Response(response=204, description="No content"),
     * @OA\Response(response=404, description="Entity not found"),
     */
    public function delete(Request $request): Response
    {
        // Récupération de l'entité Author
        $entity = $this->getEntityOfRequest($request);

        // Suppression de l'entité en base de données
        $this->removeAndFlush($entity);

        // Retourne une réponse sans contenu (HTTP 204)
        return static::renderResponse(null, Response::HTTP_NO_CONTENT);
    }
    /**
     * @Route("/{id}/books", methods={"GET"}, requirements={"id"="\d+"}, name="_get_books")
     * @OA\Response(response=200, description="List of books for the author"),
     * @OA\Response(response=404, description="Author not found"),
     */
    public function getBooksByAuthor(Request $request): Response
    {
        // Récupère l'auteur par son ID
        $author = $this->getEntityOfRequest($request);

        // Si l'auteur n'est pas trouvé, retourne une réponse 404
        if (!$author) {
            return new Response('Author not found', Response::HTTP_NOT_FOUND);
        }

        // Récupère les livres associés à cet auteur
        $books = $author->getBooks();

        // Retourne les livres associés à cet auteur
        return static::renderEntityResponse($books, static::serializationGroups, [], Response::HTTP_OK, []);
    }



}
