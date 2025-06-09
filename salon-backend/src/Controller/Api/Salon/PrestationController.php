<?php

namespace App\Controller\Api\Salon;

use App\Entity\Prestation;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PrestationController extends AbstractController
{
    // Liste toutes les prestations
    #[Route('/api/prestations', name: 'api_prestations_list', methods: ['GET'])]
    public function list(PrestationRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    // Ajout d'une prestation (admin uniquement)
    #[Route('/api/prestations', name: 'api_prestations_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        if (!isset($data['nom'], $data['duree'], $data['prix'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $prestation = new Prestation();
        $prestation->setNom($data['nom']);
        $prestation->setDuree($data['duree']);
        $prestation->setPrix($data['prix']);
        $em->persist($prestation);
        $em->flush();

        return $this->json([
            'message' => 'Prestation ajoutée !',
            'id' => $prestation->getId()
        ], 201);
    }

    // Récupère une prestation (optionnel, pratique pour l’édition)
    #[Route('/api/prestations/{id}', name: 'api_prestations_show', methods: ['GET'])]
    public function show(Prestation $prestation = null): JsonResponse
    {
        if (!$prestation) {
            return $this->json(['error' => 'Prestation non trouvée'], 404);
        }
        return $this->json($prestation);
    }

    // Modification d'une prestation (admin uniquement)
    #[Route('/api/prestations/{id}', name: 'api_prestations_update', methods: ['PUT', 'PATCH'])]
    public function update(Prestation $prestation = null, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$prestation) {
            return $this->json(['error' => 'Prestation non trouvée'], 404);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) $prestation->setNom($data['nom']);
        if (isset($data['duree'])) $prestation->setDuree($data['duree']);
        if (isset($data['prix'])) $prestation->setPrix($data['prix']);

        $em->flush();

        return $this->json([
            'message' => 'Prestation modifiée !',
            'id' => $prestation->getId()
        ]);
    }

    // Suppression d'une prestation (admin uniquement)
    #[Route('/api/prestations/{id}', name: 'api_prestations_delete', methods: ['DELETE'])]
    public function delete(Prestation $prestation = null, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$prestation) {
            return $this->json(['error' => 'Prestation non trouvée'], 404);
        }
        $em->remove($prestation);
        $em->flush();

        return $this->json(['message' => 'Prestation supprimée !']);
    }

    // Ajout en masse de prestations (batch) (admin uniquement)
    #[Route('/api/prestations/batch', name: 'api_prestations_batch', methods: ['POST'])]
    public function batch(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload, array expected.'], 400);
        }

        $created = [];
        foreach ($data as $item) {
            if (
                !isset($item['nom']) ||
                !isset($item['duree']) ||
                !isset($item['prix'])
            ) {
                continue; // Ignore les lignes incomplètes
            }

            $prestation = new Prestation();
            $prestation->setNom($item['nom']);
            $prestation->setDuree($item['duree']);
            $prestation->setPrix($item['prix']);
            $em->persist($prestation);
            $created[] = $item['nom'];
        }
        $em->flush();

        return $this->json([
            'message' => count($created) . ' prestations ajoutées.',
            'prestations' => $created
        ], 201);
    }
}
