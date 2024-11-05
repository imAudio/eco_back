<?php

namespace App\Controller;

use App\Entity\Hand;
use App\Form\HandType;
use App\Repository\HandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('api/hand')]
final class HandController extends AbstractController
{
    #[Route('',name:'app_hand_index', methods: ['GET'])]
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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $hand = new Hand();
        $form = $this->createForm(HandType::class, $hand);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hand);
            $entityManager->flush();

            return $this->redirectToRoute('app_hand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hand/new.html.twig', [
            'hand' => $hand,
            'form' => $form,
        ]);
    }
    //
    {
        $data = json_decode($request->getContent(), true);
        $related = new Related();

        // Utilisation du formulaire pour la validation et le mapping
        $form = $this->createForm(RelatedType::class, $related);



        $entityManager->persist($related);
        $entityManager->flush();

        $jsonData = $serializer->serialize($related, 'json', ['groups' => 'related:read']);


        return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);

    }
    #[Route('/{id}', name: 'app_hand_show', methods: ['GET'])]
    public function show(Hand $hand): Response
    {
        return $this->render('hand/show.html.twig', [
            'hand' => $hand,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hand_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hand $hand, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HandType::class, $hand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hand/edit.html.twig', [
            'hand' => $hand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hand_delete', methods: ['POST'])]
    public function delete(Request $request, Hand $hand, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hand->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hand);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hand_index', [], Response::HTTP_SEE_OTHER);
    }
}
