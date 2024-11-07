<?php

namespace App\Controller;

use App\Entity\Hand;
use App\Entity\User;
use App\Entity\Card;
use App\Entity\Party;

use App\Form\HandType;
use App\Repository\HandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('api/hand')]
final class HandController extends AbstractController
{

    #[Route('', name: 'app_hand', methods: ['GET'])]
    public function index(HandRepository $handRepository ): JsonResponse
        {

        $hands = $handRepository->findAll();

        $data = [];

        foreach ($hands as $hand) {
            $data[] = [
                "id" => $hand->getId(),
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

    #[Route('/new', name: 'app_hand_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $hand = new Hand();
    $form = $this->createForm(HandType::class, $hand);
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
#[Route('/by-user-party/{id_party}', name: 'app_hand_user_party', methods: ['GET'])]
    public function getHandByUserAndParty(HandRepository $handRepository,$id_party): JsonResponse
    {
        try {
            $cards = $handRepository->findBy([
                "party" => $id_party,
                "user" => $this->getUser()
            ]);

            $data = [];
            foreach ($cards as $card) {
                $data[] = [
                    "id_card" => $card->getCard()->getId(),
                    "image" => $card->getCard()->getImage(),
                    "name" => $card->getCard()->getName(),
                    "value" => $card->getCard()->getValue(),
                    "capacity" => $card->getCard()->getCapacity(),
                    "type" => $card->getCard()->getType(),
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK, []);
        }catch (\Exception $exception){
            return new JsonResponse([$exception]);
        }
    }
    #[Route('/{id}', name: 'app_hand_show', methods: ['GET'])]
    public function show(int $id, HandRepository $handRepository): JsonResponse
    {
        $hand = $handRepository->find($id);

        if (!$hand) {
            return new JsonResponse(['error' => 'Hand not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            "id" => $hand->getId(),
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


#[Route('/put', name: 'app_hand_edit', methods: ['PUT'])]
public function edit(Request $request, HandRepository $handRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $hand = $handRepository->find($data['id']);

    if (!$hand) {
        return new JsonResponse(['error' => 'Hand not found'], Response::HTTP_NOT_FOUND);
    }

    // Vérifiez la présence des IDs des entités liées
    if (isset($data['user'])) {
        $user = $entityManager->getRepository(User::class)->find($data['user']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }
        $hand->setUser($user);
    }

    if (isset($data['card'])) {
        $card = $entityManager->getRepository(Card::class)->find($data['card']);
        if (!$card) {
            return new JsonResponse(['error' => 'Card not found'], Response::HTTP_BAD_REQUEST);
        }
        $hand->setCard($card);
    }

    if (isset($data['party'])) {
        $party = $entityManager->getRepository(Party::class)->find($data['party']);
        if (!$party) {
            return new JsonResponse(['error' => 'Party not found'], Response::HTTP_BAD_REQUEST);
        }
        $hand->setParty($party);
    }

    // Enregistrer les modifications
    $entityManager->flush();

    // Préparer la réponse JSON
    $responseData = [
        "id" => $hand->getId(),
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

    return new JsonResponse($responseData, Response::HTTP_OK);
}



#[Route('/delete', name: 'app_hand_delete', methods: ['DELETE'])]
public function delete(Request $request, HandRepository $handRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $hand = $handRepository->find($data['id']);

    if (!$hand) {
        return new JsonResponse(['error' => 'Hand not found'], Response::HTTP_NOT_FOUND);
    }

    // Supprimer l'objet `Hand`
    $entityManager->remove($hand);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
}
}
