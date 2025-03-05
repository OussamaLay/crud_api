<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/book", name="book")
 * @OA\Tag(name="Books")
 */
class BookController extends AbstractSimpleApiController
{
    public const entityClass = Book::class;
    public const entityCreateTypeClass = BookType::class;
    /**
     *@Route (methods={"POST"}, name="_create")
     *
     * @OA\RequestBody(
     *   required=true,
     *   @OA\JsonContent(ref=@Model(type=self::entityCreateTypeClass))
     *),
     * @OA\Response(response=200, description="Successful operation"),
     */
     public function create (Request $request): Response
     {
         // génération d'une instance de formulaire
         $form = $this->createForm(static::entityCreateTypeClass);

         // validation des données envoyées
         $form->submit($request->request->all());

         // si les données sont valides on sauvegarde
         if ($form->isValid()) {
             // enregistrement en bdd
             $entity = $this->persistAndFlush($form->getData());

             // retourne l'entité au format json avec un code de retour 201
             return static::renderEntityResponse($entity, static::serializationGroups, [], Response::HTTP_CREATED);
         }


         $this->throwUnprocessableEntity($form);
     }



    /**
     * @Route("/{id}", methods={"GET"}, requirements={"id"="\d+"}, name="_get", )
     *
     * @OA\Response (response=200, description="Successful operation"),
     * @OA\Response (response="484", description="Entity not found"),
     */
    public function read (Request $request): Response
    {
        $entity = $this->getEntityOfRequest($request);
        return static::renderEntityResponse($entity,static::serializationGroups, [], Response::HTTP_OK, []);
    }


    /**
     * @Route("", methods={"GET"}, name="_getAll", )
     *
     * @OA\Response(response=200, description="Successful operation"),
     */
    public function getAll()
    {
        // on demande à Doctrine toutes les entités de la classe
        $entities = $this->getRepository(self::entityClass)-> findAll();
        // on les retourne sérialisées en json
        return static::renderEntityResponse ($entities, static::serializationGroups, [], Response::HTTP_OK, []);
    }


    /**
     * @Route("/{id}", methods={"DELETE"}, requirements={"id"="\d+"}, name="_delete")
     *
     * @OA\Response (response=204, description="Successful operation"),
     * @OA\Response (response="404", description="Entity not found"),
     */
    public function delete (Request $request): Response
    {
        $entity = $this->getEntityOfRequest($request);
        $this->removeAndFlush($entity);
        return static::renderResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * @Route("/{id}", methods={"PUT"}, requirements={"id"="\d+"}, name="_update")
     *
     * @OA\RequestBody(required=true, @OA\JsonContent (ref=@Model (type=self::entityUpdateTypeClass))),
     * @OpenApi\Annotations\Response (response=200, description="Successful operation"),
     * @OA\Response (response="404", description="Entity not found"),
     */
    public function update (Request $request): Response
    {
        $entity = $this->getEntityOfRequest($request);
        $form = $this->createForm( static::entityUpdateTypeClass, $entity);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $entity = $this->persistAndFlush($entity);
            return static::renderEntityResponse ($entity, static::serializationGroups, [], Response::HTTP_OK);
        }

        $this->throwUnprocessableEntity($form);

    }
}