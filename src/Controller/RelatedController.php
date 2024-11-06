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
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/related')]
final class RelatedController extends AbstractController
{
    #[Route('', name: 'app_related_index', methods: ['GET'])]
    public function index(RelatedRepository $relatedRepository): JsonResponse
    {

        $relateds = $relatedRepository->findAll();

        $data = [];

        foreach ($relateds as $related) {
            $data[] = [
                "id" => $related->getId(),
                "card" => [
                    "id" => $related->getCard()->getId(),
                    "name" => $related->getCard()->getName(),
                ],
                "combo" => [
                    "id" => $related->getCombo()->getId(),
                    "name" => $related->getCombo()->getName(),
                ]
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK, []);
    }

    #[Route('/new', name: 'app_related_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $related = new Related();

        $form = $this->createForm(RelatedType::class, $related);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($related);
            $entityManager->flush();

            $response = [
                "id" => $related->getId(),
                // Ajoutez d'autres champs si nécessaire
            ];

            return new JsonResponse($response, Response::HTTP_CREATED);
        }

        return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_related_show', methods: ['GET'])]
    public function show(Related $related): JsonResponse
    {
        $data = [
            "id" => $related->getId(),
            // Ajoutez d'autres champs si nécessaire
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_related_edit', methods: ['PUT'])]
    public function edit(Request $request, Related $related, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(RelatedType::class, $related);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $response = [
                "id" => $related->getId(),
                // Ajoutez d'autres champs si nécessaire
            ];

            return new JsonResponse($response, Response::HTTP_OK);
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
