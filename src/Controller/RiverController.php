<?php

namespace App\Controller;
use App\Entity\River;
use App\Entity\Card;
use App\Entity\Party;
use App\Form\RiverType;
use App\Repository\CardRepository;
use App\Repository\HandRepository;
use App\Repository\PartyRepository;
use App\Repository\PlayerRepository;
use App\Repository\RiverRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('api/river')]
final class RiverController extends AbstractController
{
    #[Route('',name:'app_river_index', methods: ['GET'])]
    public function index(RiverRepository $riverRepository ): JsonResponse
        {

        $rivers = $riverRepository->findAll();

        $data = [];

        foreach ($rivers as $river) {
            $data[] = [
                "id" => $river->getId(),
                "card" => [
                    "id" => $river->getCard()->getId(),
                    "name" => $river->getCard()->getName(),
                ],
                "party" =>[
                    "id" => $river->getParty()->getId(),
                    "code" =>$river->getParty()->getCode(),
                    "winner_id" => $river->getParty()->getWinner()
                ]
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK, []);
    }
    #[Route('/by-party/{id_party}',name:'app_river_by_party', methods: ['GET'])]
    public function byParty($id_party, RiverRepository $riverRepository ): JsonResponse
    {
        $cards = $riverRepository->findBy(['party'=>$id_party]);
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
    }

    #[Route('/switch', name: 'app_river_switch', methods: ['POST'])]
    public function switch(
        Request $request,
        RiverRepository $riverRepository,
        CardRepository $cardRepository,
        EntityManagerInterface $entityManager,
        HandRepository $handRepository,
        HubInterface $hub,
        PartyRepository $partyRepository,
        PlayerRepository $playerRepository,
    ): JsonResponse {
        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données nécessaires sont présentes
        if (!isset($data['id_card_river'], $data['id_card_hand'], $data['id_party'])) {
            return new JsonResponse(['error' => 'Missing required parameters'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer les valeurs des clés dans le tableau JSON
        $idCardRiver = $data['id_card_river'];
        $idCardHand = $data['id_card_hand'];
        $idParty = $data['id_party'];


        $party = $partyRepository->find($idParty);
        $party->setTurn($party->getTurn()+1);




        $entityManager->persist($party);
        $entityManager->flush();


        // Rechercher la carte dans la rivière en fonction des paramètres reçus
        $riverCard = $riverRepository->findOneBy([
            'card' => $idCardRiver,
            'party' => $idParty,
        ]);

        // Gestion des erreurs pour la carte de la rivière
        if (!$riverCard) {
            return new JsonResponse(['error' => 'River card not found'], Response::HTTP_NOT_FOUND);
        }

        // Trouver l'entité Card correspondant à idCardHand
        $cardHand = $cardRepository->find($idCardHand);
        if (!$cardHand) {
            return new JsonResponse(['error' => 'Hand card not found'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour la carte de la rivière avec la carte de la main
        $riverCard->setCard($cardHand);
        $entityManager->flush();

        // Rechercher la carte dans la main en fonction des paramètres reçus
        $handCard = $handRepository->findOneBy([
            'card' => $idCardHand,
            'party' => $idParty,
            'user' => $this->getUser(),
        ]);

        // Gestion des erreurs pour la carte de la main
        if (!$handCard) {
            return new JsonResponse(['error' => 'Hand card not found in river'], Response::HTTP_NOT_FOUND);
        }

        // Trouver l'entité Card correspondant à idCardRiver
        $cardRiver = $cardRepository->find($idCardRiver);
        if (!$cardRiver) {
            return new JsonResponse(['error' => 'River card not found'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour la carte de la main avec la carte de la rivière
        $handCard->setCard($cardRiver);
        $entityManager->flush();

        $cards = $riverRepository->findBy(['party' => $idParty]);
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

        $update = new Update(
            "game_switch_card_river_$idParty",
            json_encode(['message' => 'refresh-river','idParty' => $idParty , 'river' => $data])
        );
        $hub->publish($update);

        if ($party->getTurn() >= 19) {
            $players = $playerRepository->findBy(['party' => $idParty]);
            $riverCards = $riverRepository->findBy(['party' => $idParty]);
            $sortedByType = [];

            // Règles des multiplicateurs basées sur le nombre de cartes identiques
            $multipliers = [
                2 => 3,  // Deux cartes identiques ou complémentaires
                3 => 6,  // Trois cartes identiques d'une même famille + deux autres de familles différentes
                4 => 10, // Trois cartes d'une même famille et deux d'une autre famille
                5 => 12, // Quatre cartes d'une même famille + une d'une autre
                6 => 15  // Cinq cartes d'une même famille
            ];

            // Trier les cartes de la rivière par type et compter les cartes
            foreach ($riverCards as $river) {
                $card = $river->getCard();
                if ($card && $card->getType()) {
                    $type = trim($card->getType());

                    if (!isset($sortedByType['river'][$type])) {
                        $sortedByType['river'][$type] = [
                            'cards' => [],
                            'total_value' => 0,
                            'count' => 0
                        ];
                    }

                    $sortedByType['river'][$type]['cards'][] = $river;
                    $sortedByType['river'][$type]['total_value'] += $card->getValue();
                    $sortedByType['river'][$type]['count']++;
                }
            }

            // Parcourir les joueurs pour inclure leurs cartes de main triées par type
            foreach ($players as $player) {
                $handCards = $handRepository->findBy(['party' => $idParty, 'user' => $player->getUser()]);
                $playerName = $player->getUser()->getEmail();

                if (!isset($sortedByType[$playerName])) {
                    $sortedByType[$playerName] = [
                        'total_hand_value' => 0,
                        'most_frequent_type' => null,
                        'highest_count' => 0,
                        'multiplied_points' => 0  // Initialiser les points avec multiplicateur
                    ];
                }

                foreach ($handCards as $handCard) {
                    $card = $handCard->getCard();
                    if ($card && $card->getType()) {
                        $type = trim($card->getType());

                        if (!isset($sortedByType[$playerName][$type])) {
                            $sortedByType[$playerName][$type] = [
                                'cards' => [],
                                'total_value' => 0,
                                'count' => 0
                            ];
                        }

                        $sortedByType[$playerName][$type]['cards'][] = $handCard;
                        $sortedByType[$playerName][$type]['total_value'] += $card->getValue();
                        $sortedByType[$playerName][$type]['count']++;
                        $sortedByType[$playerName]['total_hand_value'] += $card->getValue();
                    }
                }
            }

            foreach ($players as $player) {
                $playerName = $player->getUser()->getEmail();

                foreach ($sortedByType['river'] as $type => $riverData) {
                    if (!isset($sortedByType[$playerName][$type])) {
                        $sortedByType[$playerName][$type] = [
                            'cards' => [],
                            'total_value' => 0,
                            'count' => 0
                        ];
                    }

                    $sortedByType[$playerName][$type]['count'] += $riverData['count'];
                    $sortedByType[$playerName][$type]['total_value'] += $riverData['total_value'];
                    $sortedByType[$playerName][$type]['cards'] = array_merge(
                        $sortedByType[$playerName][$type]['cards'],
                        $riverData['cards']
                    );
                }
            }

            // Calculer les points avec multiplicateurs pour chaque joueur
            foreach ($players as $player) {
                $playerName = $player->getUser()->getEmail();
                $mostFrequentType = null;
                $highestCount = 0;
                $multipliedPoints = 0;

                foreach ($sortedByType[$playerName] as $type => $data) {
                    if (isset($data['count'])) {
                        // Appliquer le multiplicateur de points selon le nombre de cartes
                        if (isset($multipliers[$data['count']])) {
                            $multipliedPoints += $multipliers[$data['count']];
                        }

                        if ($data['count'] > $highestCount) {
                            $highestCount = $data['count'];
                            $mostFrequentType = $type;
                        }
                    }
                }

                // Enregistrer le type de carte le plus fréquent et les points avec multiplicateur pour le joueur
                $sortedByType[$playerName]['most_frequent_type'] = $mostFrequentType;
                $sortedByType[$playerName]['highest_count'] = $highestCount;
                $sortedByType[$playerName]['multiplied_points'] = $multipliedPoints;
            }



            $update = new Update(
                "game_end_$idParty",
                json_encode(['message' => 'refresh-river','idParty' => $idParty , 'sortedByType' => $sortedByType])
            );
            $hub->publish($update);

        }

        return new JsonResponse(['status' => 'Cards switched successfully'], Response::HTTP_OK);
    }
    #[Route('/{id}', name: 'app_river_show', methods: ['GET'])]  
    public function show(int $id, RiverRepository $riverRepository): JsonResponse
    {
        $river = $riverRepository->find($id);

        if (!$river) {
            return new JsonResponse(['error' => 'River not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            "id" => $river->getId(),
            "card" => [
                "id" => $river->getCard()->getId(),
                "name" => $river->getCard()->getName(),
            ],
            "party" => [
                "id" => $river->getParty()->getId(),
                "code" => $river->getParty()->getCode(),
                "winner_id" => $river->getParty()->getWinner(),
            ]
        ];
        return new JsonResponse($data, Response::HTTP_OK);
    }
    #[Route('/new', name: 'app_river_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $river = new River();
    $form = $this->createForm(RiverType::class, $river);
    $form->submit($data);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifiez que les entités liées sont bien définies
        if (!$river->getCard()) {
            return new JsonResponse(['error' => 'Card not found or not set'], Response::HTTP_BAD_REQUEST);
        }
        if (!$river->getParty()) {
            return new JsonResponse(['error' => 'Party not found or not set'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($river);
        $entityManager->flush();

        $responseData = [
            "id" => $river->getId(),
            "card" => [
                "id" => $river->getCard()->getId(),
                "name" => $river->getCard()->getName(),
            ],
            "party" => [
                "id" => $river->getParty()->getId(),
                "code" => $river->getParty()->getCode(),
                "winner_id" => $river->getParty()->getWinner(),
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
    #[Route('/put', name: 'app_river_edit', methods: ['PUT'])]
    public function edit(Request $request, RiverRepository $riverRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifiez que l'ID est présent dans la requête
        if (!isset($data['id'])) {
            return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $river = $riverRepository->find($data['id']);

        if (!$river) {
            return new JsonResponse(['error' => 'River not found'], Response::HTTP_NOT_FOUND);
        }
        // Vérifiez la présence des IDs des entités liées

        if (isset($data['card'])) {
            $card = $entityManager->getRepository(Card::class)->find($data['card']);
            if (!$card) {
                return new JsonResponse(['error' => 'Card not found'], Response::HTTP_BAD_REQUEST);
            }
            $river->setCard($card);
        }

        if (isset($data['party'])) {
            $party = $entityManager->getRepository(Party::class)->find($data['party']);
            if (!$party) {
                return new JsonResponse(['error' => 'Party not found'], Response::HTTP_BAD_REQUEST);
            }
            $river->setParty($party);
        }

        // Enregistrer les modifications
        $entityManager->flush();

        // Préparer la réponse JSON
        $responseData = [
            "id" => $river->getId(),
            "card" => [
                "id" => $river->getCard()->getId(),
                "name" => $river->getCard()->getName(),
            ],
            "party" => [
                "id" => $river->getParty()->getId(),
                "code" => $river->getParty()->getCode(),
                "winner_id" => $river->getParty()->getWinner(),
            ]
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
    #[Route('/delete', name: 'app_river_delete', methods: ['DELETE'])]
    public function delete(Request $request, RiverRepository $riverRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifiez que l'ID est présent dans la requête
        if (!isset($data['id'])) {
            return new JsonResponse(['error' => 'ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $river = $riverRepository->find($data['id']);

        if (!$river) {
            return new JsonResponse(['error' => 'river not found'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'objet `River`
        $entityManager->remove($river);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
    }
    }
