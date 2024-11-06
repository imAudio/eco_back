<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Form\FriendType;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('api/friend')]
final class FriendController extends AbstractController
{
    #[Route('',name:'app_friend_index', methods: ['GET'])]
    public function index(FriendRepository $friendRepository): JsonResponse
    {
        $friends = $friendRepository->findAll();
    
        $data = [];
        foreach ($friends as $friend) {
            $data[] = [
                "id" => $friend->getId(),
                "sent_user" => [
                    "id" => $friend->getSent()->getId(),
                    "email" => $friend->getSent()->getEmail(),
                ],
                "receiver_user" => [
                    "id" => $friend->getReceiver()->getId(),
                    "email" => $friend->getReceiver()->getEmail(),
                ],
                "state" => $friend->getState(),
            ];
        }
    
        return new JsonResponse($data, Response::HTTP_OK);
    }
    


    #[Route('/new', name: 'app_friend_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Vérifiez que les données JSON sont valides
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
    
        // Vérifiez que `sent_id`, `receiver_id`, et `state` sont présents
        if (!isset($data['sent_id'], $data['receiver_id'], $data['state'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
    
        // Récupérer les utilisateurs `sent` et `receiver`
        $sentUser = $userRepository->find($data['sent_id']);
        $receiverUser = $userRepository->find($data['receiver_id']);
    
        if (!$sentUser || !$receiverUser) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }
    
        // Créer une nouvelle instance de `Friend`
        $friend = new Friend();
        $friend->setSent($sentUser);
        $friend->setReceiver($receiverUser);
        $friend->setState($data['state']);
    
        // Persister l'entité
        $entityManager->persist($friend);
        $entityManager->flush();
    
        // Préparer la réponse JSON
        $responseData = [
            "id" => $friend->getId(),
            "sent_user" => [
                "id" => $friend->getSent()->getId(),
                "email" => $friend->getSent()->getEmail(),
            ],
            "receiver_user" => [
                "id" => $friend->getReceiver()->getId(),
                "email" => $friend->getReceiver()->getEmail(),
            ],
            "state" => $friend->getState(),
        ];
    
        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }
    

    #[Route('/{id}', name: 'app_friend_show', methods: ['GET'])]  
    public function show(int $id, FriendRepository $friendRepository): JsonResponse
{
    $friend = $friendRepository->find($id);

    if (!$friend) {
        return new JsonResponse(['error' => 'friend not found'], Response::HTTP_NOT_FOUND);
    }

    $data = [
        "id" => $friend->getId(),
                "sent_user" => [
                    "id" => $friend->getSent()->getId(),
                    "email" => $friend->getSent()->getEmail(),
                ],
                "receiver_user" => [
                    "id" => $friend->getReceiver()->getId(),
                    "email" => $friend->getReceiver()->getEmail(),
                ],
                "state" => $friend->getState(),
    ];
    return new JsonResponse($data, Response::HTTP_OK);
}

    #[Route('/put', name: 'app_friend_edit', methods: ['PUT'])]
public function edit(Request $request, FriendRepository $friendRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    // Récupérer la relation d'amitié par son ID
    $friend = $friendRepository->find($data['id']);
    if (!$friend) {
        return new JsonResponse(['error' => 'Friend relationship not found'], Response::HTTP_NOT_FOUND);
    }

    // Mettre à jour l'utilisateur `sent` si `sent_id` est présent dans les données
    if (isset($data['sent_id'])) {
        $sentUser = $userRepository->find($data['sent_id']);
        if (!$sentUser) {
            return new JsonResponse(['error' => 'Sent user not found'], Response::HTTP_BAD_REQUEST);
        }
        $friend->setSent($sentUser);
    }

    // Mettre à jour l'utilisateur `receiver` si `receiver_id` est présent dans les données
    if (isset($data['receiver_id'])) {
        $receiverUser = $userRepository->find($data['receiver_id']);
        if (!$receiverUser) {
            return new JsonResponse(['error' => 'Receiver user not found'], Response::HTTP_BAD_REQUEST);
        }
        $friend->setReceiver($receiverUser);
    }

    // Mettre à jour l'état (`state`) si présent dans les données
    if (isset($data['state'])) {
        $friend->setState($data['state']);
    }

    // Enregistrer les modifications
    $entityManager->flush();

    // Préparer la réponse JSON
    $responseData = [
        "id" => $friend->getId(),
        "sent_user" => [
            "id" => $friend->getSent()->getId(),
            "email" => $friend->getSent()->getEmail(),
        ],
        "receiver_user" => [
            "id" => $friend->getReceiver()->getId(),
            "email" => $friend->getReceiver()->getEmail(),
        ],
        "state" => $friend->getState(),
    ];

    return new JsonResponse($responseData, Response::HTTP_OK);
}


#[Route('/delete', name: 'app_friend_delete', methods: ['DELETE'])]
public function delete(Request $request, FriendRepository $friendRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que l'ID est présent dans la requête
    if (!isset($data['id'])) {
        return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
    }

    $friend = $friendRepository->find($data['id']);

    if (!$friend) {
        return new JsonResponse(['error' => 'friend not found'], Response::HTTP_NOT_FOUND);
    }

    // Supprimer l'objet `Friend`
    $entityManager->remove($friend);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
}
}
