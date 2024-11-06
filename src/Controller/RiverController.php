<?php

namespace App\Controller;
use App\Entity\River;
use App\Entity\Card;
use App\Entity\Party;
use App\Form\RiverType;
use App\Repository\RiverRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('api/river')]
final class RiverController extends AbstractController
{
    #[Route('',name:'app_river_index', methods: ['GET'])]
    public function index(RiverRepository $riverRepository ): JsonResponse
        {

        $rivers = $riverRepository->findAll();

        $data = [];

        foreach ($rivers as $river) {
            $data[] = [
                "id" => $river->getId(),
                "card" => [
                    "id" => $river->getCard()->getId(),
                    "name" => $river->getCard()->getName(),
                ],
                "party" =>[
                    "id" => $river->getParty()->getId(),
                    "code" =>$river->getParty()->getCode(),
                    "winner_id" => $river->getParty()->getWinner()
                ]
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK, []);
    }

    #[Route('/{id}', name: 'app_river_show', methods: ['GET'])]  
    public function show(int $id, RiverRepository $riverRepository): JsonResponse
{
    $river = $riverRepository->find($id);

    if (!$river) {
        return new JsonResponse(['error' => 'River not found'], Response::HTTP_NOT_FOUND);
    }

    $data = [
        "id" => $river->getId(),
        "card" => [
            "id" => $river->getCard()->getId(),
            "name" => $river->getCard()->getName(),
        ],
        "party" => [
            "id" => $river->getParty()->getId(),
            "code" => $river->getParty()->getCode(),
            "winner_id" => $river->getParty()->getWinner(),
        ]
    ];
    return new JsonResponse($data, Response::HTTP_OK);
}
#[Route('/new', name: 'app_river_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $river = new River();
    $form = $this->createForm(RiverType::class, $river);
    $form->submit($data);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifiez que les entités liées sont bien définies
        if (!$river->getCard()) {
            return new JsonResponse(['error' => 'Card not found or not set'], Response::HTTP_BAD_REQUEST);
        }
        if (!$river->getParty()) {
            return new JsonResponse(['error' => 'Party not found or not set'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($river);
        $entityManager->flush();

        $responseData = [
            "id" => $river->getId(),
            "card" => [
                "id" => $river->getCard()->getId(),
                "name" => $river->getCard()->getName(),
            ],
            "party" => [
                "id" => $river->getParty()->getId(),
                "code" => $river->getParty()->getCode(),
                "winner_id" => $river->getParty()->getWinner(),
            ]
        ];

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }

    // En cas d'erreur de validation, retourner les erreurs en JSON
    $errors = [];
    foreach ($form->getErrors(true) as $error) {
        $errors[] = $error->getMessage();
    }

    return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
}
#[Route('/put', name: 'app_river_edit', methods: ['PUT'])]
public function edit(Request $request, RiverRepository $riverRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $river = $riverRepository->find($data['id']);

    if (!$river) {
        return new JsonResponse(['error' => 'River not found'], Response::HTTP_NOT_FOUND);
    }
    // Vérifiez la présence des IDs des entités liées

    if (isset($data['card'])) {
        $card = $entityManager->getRepository(Card::class)->find($data['card']);
        if (!$card) {
            return new JsonResponse(['error' => 'Card not found'], Response::HTTP_BAD_REQUEST);
        }
        $river->setCard($card);
    }

    if (isset($data['party'])) {
        $party = $entityManager->getRepository(Party::class)->find($data['party']);
        if (!$party) {
            return new JsonResponse(['error' => 'Party not found'], Response::HTTP_BAD_REQUEST);
        }
        $river->setParty($party);
    }

    // Enregistrer les modifications
    $entityManager->flush();

    // Préparer la réponse JSON
    $responseData = [
        "id" => $river->getId(),
        "card" => [
            "id" => $river->getCard()->getId(),
            "name" => $river->getCard()->getName(),
        ],
        "party" => [
            "id" => $river->getParty()->getId(),
            "code" => $river->getParty()->getCode(),
            "winner_id" => $river->getParty()->getWinner(),
        ]
    ];

    return new JsonResponse($responseData, Response::HTTP_OK);
}
#[Route('/delete', name: 'app_river_delete', methods: ['DELETE'])]
public function delete(Request $request, RiverRepository $riverRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $river = $riverRepository->find($data['id']);

    if (!$river) {
        return new JsonResponse(['error' => 'river not found'], Response::HTTP_NOT_FOUND);
    }

    // Supprimer l'objet `River`
    $entityManager->remove($river);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
}
}
