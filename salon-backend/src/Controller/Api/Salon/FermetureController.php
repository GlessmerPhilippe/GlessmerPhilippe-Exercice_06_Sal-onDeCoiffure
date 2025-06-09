<?php

namespace App\Controller\Api\Salon;

use App\Entity\FermetureExceptionnelle;
use App\Repository\FermetureExceptionnelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FermetureController extends AbstractController
{
    // Lister toutes les fermetures exceptionnelles
    #[Route('/api/fermetures', name: 'api_fermetures_list', methods: ['GET'])]
    public function list(FermetureExceptionnelleRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    // Ajouter une fermeture (admin uniquement)
    #[Route('/api/fermetures', name: 'api_fermetures_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        if (!isset($data['date'], $data['motif'])) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        $fermeture = new FermetureExceptionnelle();
        $fermeture->setDate(\DateTime::createFromFormat('Y-m-d', $data['date']));
        $fermeture->setMotif($data['motif']);

        $em->persist($fermeture);
        $em->flush();

        return $this->json([
            'message' => 'Fermeture ajoutée.',
            'id' => $fermeture->getId()
        ], 201);
    }

    // Supprimer une fermeture (admin uniquement)
    #[Route('/api/fermetures/{id}', name: 'api_fermetures_delete', methods: ['DELETE'])]
    public function delete(FermetureExceptionnelle $fermeture = null, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$fermeture) {
            return $this->json(['error' => 'Fermeture non trouvée'], 404);
        }

        $em->remove($fermeture);
        $em->flush();

        return $this->json(['message' => 'Fermeture supprimée.']);
    }
}
