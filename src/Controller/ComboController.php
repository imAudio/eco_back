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
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/combo')]
final class ComboController extends AbstractController
{
    #[Route(name: 'app_combo_index', methods: ['GET'])]
    public function index(ComboRepository $comboRepository): JsonResponse
    {
        $combos = $comboRepository->findAll();
        $data = [];

        foreach ($combos as $combo) {
            $data[] = [
                "id" => $combo->getId(),
                "name" => $combo->getName(),
                "value" => $combo->getValue(),
                "relateds" => array_map(function($related) {
                    return [
                        "id" => $related->getId(),
                        "card" => [
                            "id" => $related->getCard()->getId(),
                            "name" => $related->getCard()->getName(),
                            "value" => $related->getCard()->getValue(),
                        ],
                    ];
                }, $combo->getRelateds()->toArray())
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'app_combo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $combo = new Combo();
        $form = $this->createForm(ComboType::class, $combo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($combo);
            $entityManager->flush();

            return $this->redirectToRoute('app_combo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('combo/new.html.twig', [
            'combo' => $combo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_combo_show', methods: ['GET'])]
    public function show(Combo $combo): Response
    {
        return $this->render('combo/show.html.twig', [
            'combo' => $combo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_combo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Combo $combo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ComboType::class, $combo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_combo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('combo/edit.html.twig', [
            'combo' => $combo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_combo_delete', methods: ['POST'])]
    public function delete(Request $request, Combo $combo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $combo->getId(), $request->request->get('_token'))) {
            $entityManager->remove($combo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_combo_index', [], Response::HTTP_SEE_OTHER);
    }
}
