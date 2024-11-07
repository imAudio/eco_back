<?php

namespace App\Controller;

use App\Entity\Hand;
use App\Entity\Party;
use App\Entity\Player;
use App\Entity\River;
use App\Entity\SearchGame;
use App\Repository\CardRepository;
use App\Repository\PartyRepository;
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
    public function new(EntityManagerInterface $entityManager,SearchGameRepository $searchGameRepository, CardRepository $cardRepository, HubInterface $hub, PartyRepository $partyRepository): JsonResponse
    {

        $userSearchGame = $searchGameRepository->findOneBy(["user" => $this->getUser()]);

        if ($userSearchGame === null) {
            $searchGame = new SearchGame();

            $searchGame->setUser($this->getUser());

            $entityManager->persist($searchGame);
            $entityManager->flush();

            $gameStartded =  $searchGameRepository->findAll();
            if (count($gameStartded) >= 4 ) {

                $party = new Party();
                $party->setCode("lala");
                $party->setTurn(1);

                $entityManager->persist($party);
                $entityManager->flush();

                $players = $searchGameRepository->findAll();
                $cards = $cardRepository->findAll();

                shuffle($cards);

                foreach ($players as $player) {
                    $play = new Player();

                    $play->setUser($player->getUser());
                    $play->setParty($party);
                    $play->setPoint(0);
                    $entityManager->persist($play);
                    $entityManager->flush();

                    for ($i = 0; $i < 2; $i++) {
                        $hand = new Hand();
                        $hand->setUser($player->getUser());
                        $hand->setCard(array_shift($cards));
                        $hand->setParty($party);
                        $entityManager->persist($hand);
                        $entityManager->flush();
                    }

                }

                for ($i = 0; $i < 5; $i++) {
                    $river = new River();
                    $river->setCard(array_shift($cards));
                    $river->setParty($party);
                    $entityManager->persist($river);
                    $entityManager->flush();
                }
                $idParty = $party->getId();
                $update = new Update(
                    "game_start",
                    json_encode(['message' => 'start','id' => $idParty])
                );
                $hub->publish($update);

                return new JsonResponse(['status' => 'Game started']);
            }

            return new JsonResponse($searchGame, Response::HTTP_CREATED, []);
        }
        return new JsonResponse("deja la", Response::HTTP_OK, []);
    }

}
