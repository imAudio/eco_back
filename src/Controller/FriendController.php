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
public function new(Request $request, EntityManagerInterface $entityManager, FriendRepository $friendRepository, UserRepository $userRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez que les données JSON sont valides
    if ($data === null) {
        return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
    }

    // Vérifiez que `sent_id`, `receiver_id`, et `state` sont présents
    if (!isset($data['sent_id'], $data['receiver_id'])) {
        return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
    }

    // Récupérer les utilisateurs `sent` et `receiver`
    $sentUser = $userRepository->find($data['sent_id']);
    $receiverUser = $userRepository->find($data['receiver_id']);

    if (!$sentUser || !$receiverUser) {
        return new JsonResponse(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
    }

    // Rechercher une relation existante entre les utilisateurs
    $existingFriend = $friendRepository->findOneBy([
        'sent' => $sentUser,
        'receiver' => $receiverUser,
    ]);

    if ($existingFriend) {
        // Si la relation existe et est en "pending", passez-la à "accepted"
        if ($existingFriend->getState() === 'pending') {
            $existingFriend->setState('accepted');
            $entityManager->flush();
            return new JsonResponse(['message' => 'Amitié acceptée'], Response::HTTP_OK);
        }

        // Si la relation est déjà acceptée, retournez un message informatif
        return new JsonResponse(['message' => 'Vous êtes déjà amis avec cet utilisateur'], Response::HTTP_OK);
    }

    // Sinon, créez une nouvelle demande d'ami avec `state` défini à "pending"
    $friend = new Friend();
    $friend->setSent($sentUser);
    $friend->setReceiver($receiverUser);
    $friend->setState('pending');

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
    /*
    #[Route('/friends/check-pending', name: 'friend_check_pending', methods: ['GET'])]
    public function checkPendingFriends(FriendRepository $friendRepository): JsonResponse
    {
        // Récupérer toutes les relations en état "pending"
        $friends = $friendRepository->findBy(['state' => 'pending']);
        $response = [];

        foreach ($friends as $friend) {
            $sentId = $friend->getSent()->getId();
            $receiverId = $friend->getReceiver()->getId();

            // Vérifier s'il existe une relation acceptée inverse
            $acceptedFriend = $friendRepository->findOneBy([
                'sent' => $receiverId,
                'receiver' => $sentId,
                'state' => 'accepted'
            ]);

            if ($acceptedFriend) {
                // Cas où il existe une demande acceptée inverse
                $response[] = [
                    'sent_id' => $sentId,
                    'receiver_id' => $receiverId,
                    'state' => 'pending',
                    'message' => 'This user has already accepted your friend request. You can confirm the friendship by accepting their request.'
                ];
            } else {
                // Cas de demande en attente sans demande acceptée inverse
                $response[] = [
                    'sent_id' => $sentId,
                    'receiver_id' => $receiverId,
                    'state' => 'pending',
                    'message' => 'Waiting for the receiver to accept the friendship request.'
                ];
            }
        }

        return new JsonResponse($response, JsonResponse::HTTP_OK);
    }
    */
    #[Route('/pending/{userId}', name: 'check_pending_requests', methods: ['GET'])]
    public function checkPendingRequests(int $userId, FriendRepository $friendRepository, UserRepository $userRepository): JsonResponse
    {
        $pendingFriends = $friendRepository->findBy(['sent' => $userId, 'state' => 'pending']);
        $response = [];

        foreach ($pendingFriends as $friend) {
            $sentId = $friend->getSent()->getId();
            $receiverId = $friend->getReceiver()->getId();
            $receiverEmail = $friend->getReceiver()->getEmail();

            // Vérifier si l'utilisateur receiver a déjà accepté
            $acceptedFriend = $friendRepository->findOneBy([
                'sent' => $receiverId,
                'receiver' => $sentId,
                'state' => 'accepted'
            ]);

            if ($acceptedFriend) {
                // Cas où le receiver a déjà accepté
                $response[] = [
                    'sent_id' => $sentId,
                    'receiver_id' => $receiverId,
                    'state' => 'pending',
                    'message' => "$receiverEmail a déjà accepté votre demande d'amitié. Vous pouvez confirmer l'amitié en acceptant sa demande."
                ];
            } else {
                // Cas de demande en attente sans acceptation inverse
                $response[] = [
                    'sent_id' => $sentId,
                    'receiver_id' => $receiverId,
                    'state' => 'pending',
                    'message' => "En attente d’acceptation de la demande d’amitié de $receiverEmail."
                ];
            }
        }

        return new JsonResponse($response, JsonResponse::HTTP_OK);
    }


    #[Route('/status/friends', name: 'check_friendship_status', methods: ['GET'])]
public function checkFriendshipStatus(FriendRepository $friendRepository): JsonResponse
{
    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['error' => 'Utilisateur non authentifié'], JsonResponse::HTTP_UNAUTHORIZED);
    }
    
    $userId = $user->getId();
    $friends = $friendRepository->findBy(['receiver' => $userId]);
    $response = [];
    
    foreach ($friends as $friend) {
        $sent = $friend->getSent();
        $receiverId = $sent->getId();
        $receiverEmail = $sent->getEmail();

        $response[] = [
            'friend_id' => $friend->getId(),
            'friend_email' => $receiverEmail,
            'message' => $friend->getState() === 'accepted' ? "Vous êtes amis avec $receiverEmail" : "Demande en attente avec $receiverEmail",
            'state' => $friend->getState()  // Inclut l'état pour distinguer les amis et les demandes en attente
        ];
    }

    return new JsonResponse($response, JsonResponse::HTTP_OK);
}
// Dans FriendController.php

#[Route('/{id}/accept', name: 'app_friend_accept', methods: ['PUT'])]
public function acceptFriend(int $id, FriendRepository $friendRepository, EntityManagerInterface $entityManager): JsonResponse
{
    // Trouver la relation d'amitié par ID
    $friend = $friendRepository->find($id);
   
    if (!$friend) {
        return new JsonResponse(['error' => 'Amitié non trouvée'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Mettre à jour l'état à "accepted"
    $friend->setState('accepted');

    // Enregistrer la modification dans la base de données
    $entityManager->persist($friend);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Amitié acceptée'], JsonResponse::HTTP_OK);
}

// Dans FriendController.php
#[Route('/search/q', name: 'search_user', methods: ['GET'])]
public function search(Request $request, UserRepository $userRepository): JsonResponse
{
    $query = $request->query->get('query', '');

    // Vérifier que la requête a au moins 3 lettres pour la recherche
 

    $users = $userRepository->createQueryBuilder('u')
        ->where('u.email LIKE :query')
        ->setParameter('query', $query . '%') // Utilise les trois premières lettres de `query`
        ->getQuery()
        ->getResult();

    $data = array_map(function ($user) {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ];
    }, $users);

    return new JsonResponse($data, JsonResponse::HTTP_OK);
}


}