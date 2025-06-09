<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérifie la présence de l'email et du mot de passe
        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Email et mot de passe obligatoires.'], 400);
        }

        // Vérification préalable (évite 500 sur doublon)
        $existing = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return $this->json(['error' => 'Cet email est déjà utilisé.'], 409);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $user->setRoles($data['roles'] ?? ['ROLE_CLIENT']);
        $user->setNom($data['nom'] ?? null);
        $user->setPrenom($data['prenom'] ?? null);
        $user->setCivilite($data['civilite'] ?? null);
        $user->setTel($data['tel'] ?? null);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        try {
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            // Double sécurité (cas de race condition)
            return $this->json(['error' => 'Cet email est déjà utilisé.'], 409);
        }

        return $this->json(['message' => 'Inscription réussie!'], 201);
    }
}
