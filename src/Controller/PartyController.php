<?php

namespace App\Controller;

use App\Entity\Party;
use App\Entity\User;
use App\Form\PartyType;
use App\Repository\PartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('api/party')]
final class PartyController extends AbstractController
{
    #[Route('',name:'app_party_index', methods: ['GET'])]
    public function index(PartyRepository $partyRepository ): JsonResponse
        {

        $partys = $partyRepository->findAll();

        $data = [];

        foreach ($partys as $party) {
            $data[] = [
                "id" => $party->getId(),
                "turn" =>$party->getTurn(),
                "code" =>$party->getCode(),
                "winner_id" =>[
                    "winner_id_" => $party->getWinner()->getId(),
                    "email" =>$party->getWinner()->getEmail(),
                ]
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK, []);
    }

    #[Route('/new', name: 'app_party_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez si les données JSON sont valides
    if ($data === null) {
        return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
    }

    $party = new Party();
    $form = $this->createForm(PartyType::class, $party);
    $form->submit($data);

    if ($form->isSubmitted() && $form->isValid()) {
        // Assigner manuellement le winner si winner_id est présent dans les données
        if (isset($data['winner_id'])) {
            $winner = $entityManager->getRepository(User::class)->find($data['winner_id']);
            if (!$winner) {
                return new JsonResponse(['error' => 'Winner not found'], Response::HTTP_BAD_REQUEST);
            }
            $party->setWinner($winner);
        }

        $entityManager->persist($party);
        $entityManager->flush();

        $responseData = [
            "id" => $party->getId(),
            "turn" => $party->getTurn(),
            "code" => $party->getCode(),
            "winner_id" => [
                "winner_id" => $party->getWinner()->getId(),
                "email" => $party->getWinner()->getEmail(),
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

    


    #[Route('/{id}', name: 'app_party_show', methods: ['GET'])]
    public function show(int $id, PartyRepository $partyRepository): JsonResponse
{
    $party = $partyRepository->find($id);

    if (!$party) {
        return new JsonResponse(['error' => 'party not found'], Response::HTTP_NOT_FOUND);
    }

    $data = [
        "id" => $party->getId(),
        "turn" => $party->getTurn(),
        "code" =>$party->getCode(),
        "winner_id" =>[
                    "winner_id_" => $party->getWinner()->getId(),
                    "email" =>$party->getWinner()->getEmail(),
                ]
    ];

    return new JsonResponse($data, Response::HTTP_OK);
}

#[Route('/put', name: 'app_party_edit', methods: ['PUT'])]
public function edit(Request $request, PartyRepository $partyRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $party = $partyRepository->find($data['id']);

    if (!$party) {
        return new JsonResponse(['error' => 'Party not found'], Response::HTTP_NOT_FOUND);
    }

    // Mettre à jour le champ `turn` si présent dans les données
    if (isset($data['turn'])) {
        $party->setTurn($data['turn']);
    }

    if (isset($data['code'])) {
        $party->setCode($data['code']);
    }

    // Mettre à jour le champ `winner` si `winner_id` est présent dans les données
    if (isset($data['winner_id'])) {
        $winner = $entityManager->getRepository(User::class)->find($data['winner_id']);
        if (!$winner) {
            return new JsonResponse(['error' => 'Winner not found'], Response::HTTP_BAD_REQUEST);
        }
        $party->setWinner($winner);
    }

    // Enregistrer les modifications
    $entityManager->flush();

    // Préparer la réponse JSON
    $responseData = [
        "id" => $party->getId(),
        "turn" => $party->getTurn(),
        "code" => $party->getCode(),
        "winner_id" => [
            "winner_id" => $party->getWinner()->getId(),
            "email" => $party->getWinner()->getEmail(),
        ]
    ];

    return new JsonResponse($responseData, Response::HTTP_OK);
}


#[Route('/delete', name: 'app_party_delete', methods: ['DELETE'])]
public function delete(Request $request, PartyRepository $partyRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $party = $partyRepository->find($data['id']);

    if (!$party) {
        return new JsonResponse(['error' => 'party not found'], Response::HTTP_NOT_FOUND);
    }

    // Supprimer l'objet `party`
    $entityManager->remove($party);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
}
}
