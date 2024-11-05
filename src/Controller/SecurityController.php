<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        dd("la");
        // Cette méthode est appelée automatiquement par LexikJWTAuthenticationBundle pour gérer la génération du token JWT
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Si une erreur d'authentification est détectée, renvoie une réponse appropriée
        if ($error) {
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // En cas de succès, LexikJWTAuthenticationBundle renvoie automatiquement le token
        return new JsonResponse(['message' => 'Logged in successfully']);
    }
}

