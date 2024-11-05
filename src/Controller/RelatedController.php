<?php

namespace App\Controller;

use App\Entity\Related;
use App\Form\RelatedType;
use App\Repository\RelatedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/related')]
final class RelatedController extends AbstractController
{
    #[Route('', name: 'app_related_index', methods: ['GET'])]
    public function index(RelatedRepository $relatedRepository, SerializerInterface $serializer): JsonResponse
    {

        $relateds = $relatedRepository->findAll();
        $data = $serializer->serialize($relateds, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'app_related_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $related = new Related();

        // Utilisation du formulaire pour la validation et le mapping
        $form = $this->createForm(RelatedType::class, $related);

        $form->submit($data);


        $entityManager->persist($related);
        $entityManager->flush();

        $jsonData = $serializer->serialize($related, 'json', ['groups' => 'related:read']);


        return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);

    }

    #[Route('/{id}', name: 'app_related_show', methods: ['GET'])]
    public function show(Related $related, SerializerInterface $serializer): JsonResponse
    {

        $data = $serializer->serialize($related, 'json', ['groups' => 'related:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/edit', name: 'app_related_edit', methods: ['PUT'])]
    public function edit(Request $request, Related $related, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(RelatedType::class, $related);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $jsonData = $serializer->serialize($related, 'json', ['groups' => 'related:read']);

            return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_related_delete', methods: ['DELETE'])]
    public function delete(Related $related, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($related);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Related entity deleted'], Response::HTTP_NO_CONTENT);
    }
}
