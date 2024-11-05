<?php

namespace App\Controller;

use App\Entity\Card;
use App\Form\CardType;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/card')]
final class CardController extends AbstractController
{
    #[Route('', name: 'app_card_index', methods: ['GET'])]
    public function index(CardRepository $cardRepository, SerializerInterface $serializer): JsonResponse
    {
        $cards = $cardRepository->findAll();
        $data = $serializer->serialize($cards, 'json');
        $coucou = 4;
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'app_card_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $card = new Card();

        // Utilisation du formulaire pour la validation et le mapping
        $form = $this->createForm(CardType::class, $card);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($card);
            $entityManager->flush();

            $jsonData = $serializer->serialize($card, 'json');

            return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);
        }

        return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_card_show', methods: ['GET'])]
    public function show(Card $card, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($card, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/edit', name: 'app_card_edit', methods: ['PUT'])]
    public function edit(Request $request, Card $card, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(CardType::class, $card);
        $form->submit($data, false); // Le deuxième paramètre indique de ne pas effacer les valeurs existantes

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $jsonData = $serializer->serialize($card, 'json');

            return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_card_delete', methods: ['DELETE'])]
    public function delete(Card $card, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($card);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Card deleted'], Response::HTTP_NO_CONTENT);
    }
}
