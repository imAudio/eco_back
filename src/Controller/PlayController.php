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
    #[Route('',name:'app_hand_index', methods: ['GET'])]
    public function index(PlayRepository $handRepository ): JsonResponse
        {

        $hands = $handRepository->findAll();

        $data = [];

        foreach ($hands as $hand) {
            $data[] = [
                "id" => $hand->getId(),
                "turn" =>$hand->getTurn(),
                "card" => [
                    "id" => $hand->getCard()->getId(),
                    "name" => $hand->getCard()->getName(),
                ],
                "user" => [
                    "id" => $hand->getUser()->getId(),
                    "name" => $hand->getUser()->getEmail(),
                ],
                "party" =>[
                    "id" => $hand->getParty()->getId(),
                    "code" =>$hand->getParty()->getCode(),
                    "winner_id" => $hand->getParty()->getWinner()
                ]
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK, []);
    }


    #[Route('/{id}', name: 'app_hand_show', methods: ['GET'])]  
    public function show(int $id, PlayRepository $handRepository): JsonResponse
{
    $hand = $handRepository->find($id);

    if (!$hand) {
        return new JsonResponse(['error' => 'Play not found'], Response::HTTP_NOT_FOUND);
    }

    $data = [
        "id" => $hand->getId(),
        "turn" => $hand->getTurn(),
        "card" => [
            "id" => $hand->getCard()->getId(),
            "name" => $hand->getCard()->getName(),
        ],
        "user" => [
            "id" => $hand->getUser()->getId(),
            "name" => $hand->getUser()->getEmail(),
        ],
        "party" => [
            "id" => $hand->getParty()->getId(),
            "code" => $hand->getParty()->getCode(),
            "winner_id" => $hand->getParty()->getWinner(),
        ]
    ];

    return new JsonResponse($data, Response::HTTP_OK);
}
#[Route('/new', name: 'app_hand_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $hand = new Play();
    $form = $this->createForm(PlayType::class, $hand);
    $form->submit($data);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifiez que les entités liées sont bien définies
        if (!$hand->getUser()) {
            return new JsonResponse(['error' => 'User not found or not set'], Response::HTTP_BAD_REQUEST);
        }
        if (!$hand->getCard()) {
            return new JsonResponse(['error' => 'Card not found or not set'], Response::HTTP_BAD_REQUEST);
        }
        if (!$hand->getParty()) {
            return new JsonResponse(['error' => 'Party not found or not set'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($hand);
        $entityManager->flush();

        $responseData = [
            "id" => $hand->getId(),
            "turn"=> $hand->getTurn(),
            "card" => [
                "id" => $hand->getCard()->getId(),
                "name" => $hand->getCard()->getName(),
            ],
            "user" => [
                "id" => $hand->getUser()->getId(),
                "name" => $hand->getUser()->getEmail(),
            ],
            "party" => [
                "id" => $hand->getParty()->getId(),
                "code" => $hand->getParty()->getCode(),
                "winner_id" => $hand->getParty()->getWinner(),
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
    #[Route('/{id}/edit', name: 'app_play_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Play $play, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlayType::class, $play);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_play_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('play/edit.html.twig', [
            'play' => $play,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_play_delete', methods: ['POST'])]
    public function delete(Request $request, Play $play, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$play->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($play);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_play_index', [], Response::HTTP_SEE_OTHER);
    }
}
