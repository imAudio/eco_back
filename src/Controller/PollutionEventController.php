<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\PollutionEventRepository;


class PollutionEventController extends AbstractController
{
    #[Route('/pollution-event/random', name: 'pollution_event_random', methods: ['GET'])]
public function randomEvent(PollutionEventRepository $pollutionEventRepository): JsonResponse
{
    $events = $pollutionEventRepository->findAll();

    // Vérifiez qu'il y a des événements disponibles
    if (empty($events)) {
        return new JsonResponse(['error' => 'No pollution events available'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Sélectionner un événement aléatoire
    $randomEvent = $events[array_rand($events)];

    // Préparer les données de l'événement pour la réponse JSON
    $data = [
        'id' => $randomEvent->getId(),
        'evenement' => $randomEvent->getEvenement(),
        'typePollution' => $randomEvent->getTypePollution(),
        'indicePollution' => $randomEvent->getIndicePollution(),
    ];

    return new JsonResponse($data, JsonResponse::HTTP_OK);
}

}
