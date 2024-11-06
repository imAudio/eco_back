<?php

namespace App\Controller;

use App\Entity\SearchGame;
use App\Repository\SearchGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[Route('api/search-game')]
final class SearchGameController extends AbstractController
{
    #[Route('', name: 'app_search_game', methods: ['GET'])]
    public function index(SearchGameRepository $searchGameRepository): JsonResponse
    {
        $searchGames = $searchGameRepository->findAll();
        $data = [];
        foreach ($searchGames as $searchGame) {
            $data[] = [
                'id' => $searchGame->getId(),
                'user' => [
                    'id' => $searchGame->getUser()->getId(),
                    'email' => $searchGame->getUser()->getEmail(),
                ]
            ];
        }

        return $this->json($data, Response::HTTP_OK,[]);
    }

    #[Route('/new', name: 'app_search_game_new', methods: ['POST'])]
    public function new(EntityManagerInterface $entityManager,SearchGameRepository $searchGameRepository, HubInterface $hub): JsonResponse
    {
        $userSearchGame = $searchGameRepository->find($this->getUser());
        if ($userSearchGame === null) {
            $searchGame = new SearchGame();

            $searchGame->setUser($this->getUser());

            $entityManager->persist($searchGame);
            $entityManager->flush();

            $gameStartded =  $searchGameRepository->findAll();
            if (count($gameStartded) >= 4 ) {
                $update = new Update(
                    "https://example.com/game",
                    json_encode(['message' => 'Le jeu commence !'])
                );
                $hub->publish($update);

                return new JsonResponse(['status' => 'Game started']);
            }

            return new JsonResponse($searchGame, Response::HTTP_CREATED, []);
        }


        return new JsonResponse("deja la", Response::HTTP_OK, []);
    }

}
