<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/player')]
final class PlayerController extends AbstractController
{
    #[Route('', name: 'app_player_index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository, SerializerInterface $serializer): JsonResponse
    {
        $players = $playerRepository->findAll();

        $data = $serializer->serialize($players, 'json', ['groups' => 'player:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
    #[Route('/by-party/{id_party}', name: 'app_player_by_party', methods: ['GET'])]
    public function byParty(PlayerRepository $playerRepository,$id_party): JsonResponse
    {

        $players = $playerRepository->findBy(['party' => $id_party]);
        $data = [];

        foreach ($players as $player) {
            $data[] = [
                "id_user" => $player->getUser()->getId(),
                "username" => explode('@', $player->getUser()->getEmail())[0],
                "point" => $player->getPoint(),
                "order_turn" => $player->getOrderTurn(),
            ];

        }
        return new JsonResponse($data, Response::HTTP_OK, []);
    }

    #[Route('/my-turn/{id_party}', name: 'app_player_my_turn', methods: ['GET'])]
    public function myTurn(Request $request,PlayerRepository $playerRepository, $id_party): JsonResponse
    {
        $playerTurn = $playerRepository->findOneBy(['party' => $id_party,'user' => $this->getUser()]);

        return new JsonResponse(['turn' => $playerTurn->getOrderTurn()], Response::HTTP_OK);
    }

    #[Route('/new', name: 'app_player_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $player = new Player();

        $form = $this->createForm(PlayerType::class, $player);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($player);
            $entityManager->flush();

            $jsonData = $serializer->serialize($player, 'json', ['groups' => 'player:read']);

            return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);
        }

        return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }

    //#[Route('/{id}', name: 'app_player_show', methods: ['GET'])]
    //public function show(Player $player, SerializerInterface $serializer): JsonResponse
    //{
    //    $data = $serializer->serialize($player, 'json', ['groups' => 'player:read']);
//
    //    return new JsonResponse($data, Response::HTTP_OK, [], true);
    //}
//
    #[Route('/{id}/edit', name: 'app_player_edit', methods: ['PUT'])]
    public function edit(Request $request, Player $player, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(PlayerType::class, $player);
        $form->submit($data, false); // Le second paramètre "false" indique de ne pas effacer les valeurs existantes

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $jsonData = $serializer->serialize($player, 'json', ['groups' => 'player:read']);

            return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_player_delete', methods: ['DELETE'])]
    public function delete(Player $player, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($player);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Player deleted'], Response::HTTP_NO_CONTENT);
    }
}
