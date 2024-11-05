<?php

namespace App\Controller;

use App\Entity\Combo;
use App\Form\ComboType;
use App\Repository\ComboRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/combo')]
final class ComboController extends AbstractController
{
    #[Route(name: 'app_combo_index', methods: ['GET'])]
    public function index(ComboRepository $comboRepository): JsonResponse
    {
        $combos = $comboRepository->findAll();
        
        $data = array_map(function (Combo $combo) {
            return [
                'id' => $combo->getId(),
                'name' => $combo->getName(),
                // Ajoutez ici les autres champs nécessaires de l'entité Combo
            ];
        }, $combos);

        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_combo_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $combo = new Combo();
        $combo->setName($data['name']);
        // Renseignez les autres champs nécessaires

        $entityManager->persist($combo);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'Combo created!',
            'combo' => [
                'id' => $combo->getId(),
                'name' => $combo->getName(),
                // Ajoutez ici les autres champs nécessaires
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_combo_show', methods: ['GET'])]
    public function show(Combo $combo): JsonResponse
    {
        $data = [
            'id' => $combo->getId(),
            'name' => $combo->getName(),
            // Ajoutez ici les autres champs nécessaires de l'entité Combo
        ];

        return new JsonResponse($data);
    }

    #[Route('/{id}/edit', name: 'app_combo_edit', methods: ['PUT'])]
    public function edit(Request $request, Combo $combo, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $combo->setName($data['name']);
        // Mettez à jour les autres champs nécessaires

        $entityManager->flush();

        return new JsonResponse([
            'status' => 'Combo updated!',
            'combo' => [
                'id' => $combo->getId(),
                'name' => $combo->getName(),
                // Ajoutez ici les autres champs nécessaires
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_combo_delete', methods: ['DELETE'])]
    public function delete(Combo $combo, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($combo);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Combo deleted'], Response::HTTP_NO_CONTENT);
    }
}
