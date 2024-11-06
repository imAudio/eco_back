<?php

namespace App\Controller;

use App\Entity\Play;
use App\Entity\User;
use App\Entity\Card;
use App\Entity\Party;
use App\Form\PlayType;
use App\Repository\PlayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('api/play')]
final class PlayController extends AbstractController
{
    #[Route('',name:'app_play_index', methods: ['GET'])]
    public function index(PlayRepository $playRepository ): JsonResponse
        {

        $plays = $playRepository->findAll();

        $data = [];

        foreach ($plays as $play) {
            $data[] = [
                "id" => $play->getId(),
                "turn" =>$play->getTurn(),
                "card" => [
                    "id" => $play->getCard()->getId(),
                    "name" => $play->getCard()->getName(),
                ],
                "user" => [
                    "id" => $play->getUser()->getId(),
                    "name" => $play->getUser()->getEmail(),
                ],
                "party" =>[
                    "id" => $play->getParty()->getId(),
                    "code" =>$play->getParty()->getCode(),
                    "winner_id" => $play->getParty()->getWinner()
                ]
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK, []);
    }


    #[Route('/{id}', name: 'app_play_show', methods: ['GET'])]  
    public function show(int $id, PlayRepository $playRepository): JsonResponse
{
    $play = $playRepository->find($id);

    if (!$play) {
        return new JsonResponse(['error' => 'Play not found'], Response::HTTP_NOT_FOUND);
    }

    $data = [
        "id" => $play->getId(),
        "turn" => $play->getTurn(),
        "card" => [
            "id" => $play->getCard()->getId(),
            "name" => $play->getCard()->getName(),
        ],
        "user" => [
            "id" => $play->getUser()->getId(),
            "name" => $play->getUser()->getEmail(),
        ],
        "party" => [
            "id" => $play->getParty()->getId(),
            "code" => $play->getParty()->getCode(),
            "winner_id" => $play->getParty()->getWinner(),
        ]
    ];

    return new JsonResponse($data, Response::HTTP_OK);
}
#[Route('/new', name: 'app_play_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $play = new Play();
    $form = $this->createForm(PlayType::class, $play);
    $form->submit($data);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifiez que les entités liées sont bien définies
        if (!$play->getUser()) {
            return new JsonResponse(['error' => 'User not found or not set'], Response::HTTP_BAD_REQUEST);
        }
        if (!$play->getCard()) {
            return new JsonResponse(['error' => 'Card not found or not set'], Response::HTTP_BAD_REQUEST);
        }
        if (!$play->getParty()) {
            return new JsonResponse(['error' => 'Party not found or not set'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($play);
        $entityManager->flush();

        $responseData = [
            "id" => $play->getId(),
            "turn"=> $play->getTurn(),
            "card" => [
                "id" => $play->getCard()->getId(),
                "name" => $play->getCard()->getName(),
            ],
            "user" => [
                "id" => $play->getUser()->getId(),
                "name" => $play->getUser()->getEmail(),
            ],
            "party" => [
                "id" => $play->getParty()->getId(),
                "code" => $play->getParty()->getCode(),
                "winner_id" => $play->getParty()->getWinner(),
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
#[Route('/put', name: 'app_play_edit', methods: ['PUT'])]
public function edit(Request $request, PlayRepository $playRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $play = $playRepository->find($data['id']);

    if (!$play) {
        return new JsonResponse(['error' => 'Play not found'], Response::HTTP_NOT_FOUND);
    }

    // Mettre à jour le champ `turn` si présent dans les données
    if (isset($data['turn'])) {
        $play->setTurn($data['turn']);
    }

    // Vérifiez la présence des IDs des entités liées
    if (isset($data['user'])) {
        $user = $entityManager->getRepository(User::class)->find($data['user']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }
        $play->setUser($user);
    }

    if (isset($data['card'])) {
        $card = $entityManager->getRepository(Card::class)->find($data['card']);
        if (!$card) {
            return new JsonResponse(['error' => 'Card not found'], Response::HTTP_BAD_REQUEST);
        }
        $play->setCard($card);
    }

    if (isset($data['party'])) {
        $party = $entityManager->getRepository(Party::class)->find($data['party']);
        if (!$party) {
            return new JsonResponse(['error' => 'Party not found'], Response::HTTP_BAD_REQUEST);
        }
        $play->setParty($party);
    }

    // Enregistrer les modifications
    $entityManager->flush();

    // Préparer la réponse JSON
    $responseData = [
        "id" => $play->getId(),
        "turn" => $play->getTurn(),
        "card" => [
            "id" => $play->getCard()->getId(),
            "name" => $play->getCard()->getName(),
        ],
        "user" => [
            "id" => $play->getUser()->getId(),
            "name" => $play->getUser()->getEmail(),
        ],
        "party" => [
            "id" => $play->getParty()->getId(),
            "code" => $play->getParty()->getCode(),
            "winner_id" => $play->getParty()->getWinner(),
        ]
    ];

    return new JsonResponse($responseData, Response::HTTP_OK);
}


#[Route('/delete', name: 'app_play_delete', methods: ['DELETE'])]
public function delete(Request $request, PlayRepository $playRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $play = $playRepository->find($data['id']);

    if (!$play) {
        return new JsonResponse(['error' => 'Play not found'], Response::HTTP_NOT_FOUND);
    }

    // Supprimer l'objet `PLay`
    $entityManager->remove($play);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
}
}
