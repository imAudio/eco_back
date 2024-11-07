<?php

namespace App\Controller;

use App\Entity\ChatParty;
use App\Repository\ChatPartyRepository;
use App\Repository\UserRepository;
use App\Repository\PartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;


#[Route('api/chat-party')]
class ChatPartyController extends AbstractController
{
    #[Route('/add', name: 'chat_party_add', methods: ['POST'])]
    public function addMessage(Request $request, HubInterface $hub, EntityManagerInterface $entityManager, UserRepository $userRepository, PartyRepository $partyRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer le contenu, l'utilisateur et la party à partir des données
        $content = $data['content'] ?? null;
        $partyId = $data['party_id'] ?? null;

        if (!$content  || !$partyId) {
            return new JsonResponse(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }


        $party = $partyRepository->find($partyId);

        if (!$party) {
            return new JsonResponse(['error' => 'Party not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Créer et sauvegarder le message
        $chatParty = new ChatParty();
        $chatParty->setContent($content);
        $chatParty->setUser($this->getUser());
        $chatParty->setParty($party);

        $entityManager->persist($chatParty);
        $entityManager->flush();
        $update = new Update(
            "game_chat_{$partyId}",  // Spécifier le topic de la partie pour Mercure
            json_encode([
                'action' => 'reload_chat',
                'content' => $chatParty->getContent(),
                'user_id' => $chatParty->getUser()->getId(),
                'party_id' => $chatParty->getParty()->getId(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ])
        );
        $hub->publish($update);

        // Retourner les informations demandées
        return new JsonResponse([
            'content' => $chatParty->getContent(),
            'user_id' => $chatParty->getUser()->getId(),
            'party_id' => $chatParty->getParty()->getId()
        ], JsonResponse::HTTP_CREATED);
    }


    #[Route('/{partyId}', name: 'chat_party_get_by_party', methods: ['GET'])]
    public function getMessagesByParty(int $partyId, ChatPartyRepository $chatPartyRepository): JsonResponse
    {
        $messages = $chatPartyRepository->findMessagesByPartyId($partyId);

        // Vérifier s'il y a des messages
        if (empty($messages)) {
            return new JsonResponse(['message' => 'No messages found for this party'], JsonResponse::HTTP_OK);
        }

        return new JsonResponse($messages, JsonResponse::HTTP_OK);
    }




    #[Route('/user/{userId}', name: 'chat_party_get_by_user', methods: ['GET'])]
    public function getMessagesByUser(int $userId, ChatPartyRepository $chatPartyRepository, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $messages = $chatPartyRepository->findBy(['user' => $user]);

        $data = [];
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'party_id' => $message->getParty()->getId(),
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/update', name: 'chat_party_update_message', methods: ['PUT'])]
    public function updateMessage(Request $request, ChatPartyRepository $chatPartyRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si `id` et `content` sont présents dans les données
        if (!isset($data['id']) || !isset($data['content'])) {
            return new JsonResponse(['error' => 'Message ID and content are required'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Récupérer le message par ID
        $message = $chatPartyRepository->findOneBy(['id' => $data['id']]);
    
        // Vérifier si le message existe
        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Mettre à jour le contenu du message
        $message->setContent($data['content']);
        $entityManager->flush();
    
        return new JsonResponse([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'user_id' => $message->getUser()->getId(),
            'party_id' => $message->getParty()->getId(),
        ], JsonResponse::HTTP_OK);
    }
    
    #[Route('/delete', name: 'chat_party_delete_message', methods: ['DELETE'])]
    public function deleteMessage(Request $request, ChatPartyRepository $chatPartyRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si l'ID du message est présent dans les données
        if (!isset($data['id'])) {
            return new JsonResponse(['error' => 'Message ID is required'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Récupérer le message par ID
        $message = $chatPartyRepository->findOneBy(['id' => $data['id']]);
    
        // Vérifier si le message existe
        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        // Supprimer le message
        $entityManager->remove($message);
        $entityManager->flush();
    
        return new JsonResponse(['success' => 'Message successfully deleted'], JsonResponse::HTTP_OK);
    }
    

}

