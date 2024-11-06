<?php

require 'vendor/autoload.php';

use Firebase\JWT\JWT;

// Clé secrète pour Mercure (doit correspondre à `MERCURE_JWT_SECRET` dans la configuration)
$secretKey = 'PZVJXlIsOGMgHpBFX50HHD3201/K1j+zbbokHKs9bjA=';

// Création du payload pour Mercure
$payload = [
    'mercure' => [
        'publish' => ['*'], // Autorise la publication sur tous les topics (si nécessaire)
        'subscribe' => ['*'], // Autorise l'abonnement à tous les topics
    ],
    'iat' => time(),
    'exp' => time() + 3600 // Durée de validité d'une heure
];

// Génération du token
$jwt = JWT::encode($payload, $secretKey, 'HS256');

echo "Generated Mercure JWT:\n$jwt\n";
