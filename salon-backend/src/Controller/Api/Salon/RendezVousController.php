<?php

namespace App\Controller\Api\Salon;

use App\Entity\User;
use App\Entity\Prestation;
use App\Entity\RendezVous;
use App\Service\DispoService;
use App\Repository\UserRepository;
use App\Repository\PrestationRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RendezVousController extends AbstractController
{
    // Lister tous les rendez-vous du user connecté (client OU coiffeur)
    #[Route('/api/rendezvous', name: 'api_rdv_list', methods: ['GET'])]
    public function list(RendezVousRepository $repo): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (in_array('ROLE_COIFFEUR', $user->getRoles())) {
            $rdvs = $repo->findBy(['coiffeur' => $user]);
        } else {
            $rdvs = $repo->findBy(['client' => $user]);
        }
        return $this->json($rdvs, 200, [], ['groups' => ['rdv:read']]);
    }

    // Prendre rendez-vous (création) — client connecté
    #[Route('/api/rendezvous', name: 'api_rdv_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        PrestationRepository $prestationRepo,
        UserRepository $userRepo,
        DispoService $dispoService
    ): JsonResponse {
        $user = $this->getUser();
        if (!in_array('ROLE_CLIENT', $user->getRoles())) {
            return $this->json(['error' => 'Seuls les clients peuvent réserver.'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (
            !isset($data['prestation_id'], $data['coiffeur_id'], $data['date'], $data['heureDebut'])
        ) {
            return $this->json(['error' => 'Champs obligatoires manquants.'], 400);
        }

        $prestation = $prestationRepo->find($data['prestation_id']);
        $coiffeur = $userRepo->find($data['coiffeur_id']);
        if (!$prestation || !$coiffeur) {
            return $this->json(['error' => 'Prestation ou coiffeur introuvable.'], 404);
        }

        $dateObj = \DateTime::createFromFormat('Y-m-d', $data['date']);
        $heureDebutObj = \DateTime::createFromFormat('H:i', $data['heureDebut']);

        if (!$dispoService->isDispo($coiffeur, $dateObj, $heureDebutObj, $prestation)) {
            return $this->json(['error' => 'Ce créneau n\'est pas disponible'], 409);
        }
        try {
            $rdv = new RendezVous();
            $rdv->setClient($user);
            $rdv->setCoiffeur($coiffeur);
            $rdv->setPrestation($prestation);
            $rdv->setDate(\DateTime::createFromFormat('Y-m-d', $data['date']));
            $rdv->setHeureDebut(\DateTime::createFromFormat('H:i', $data['heureDebut']));
            $rdv->setStatut('à venir');
            $em->persist($rdv);
            $em->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return $this->json(['error' => "Ce créneau est déjà réservé"], 409);
        }


        return $this->json(['message' => 'Rendez-vous réservé !', 'id' => $rdv->getId()], 201);
    }

    // Voir le détail d'un rendez-vous (s’il appartient au user)
    #[Route('/api/rendezvous/{id}', name: 'api_rdv_show', methods: ['GET'])]
    public function show(RendezVous $rdv = null): JsonResponse
    {
        if (!$rdv) {
            return $this->json(['error' => 'RDV non trouvé'], 404);
        }
        $user = $this->getUser();
        if (
            $rdv->getClient() !== $user &&
            $rdv->getCoiffeur() !== $user &&
            !in_array('ROLE_ADMIN', $user->getRoles())
        ) {
            return $this->json(['error' => 'Accès interdit'], 403);
        }
        return $this->json($rdv, 200, [], ['groups' => ['rdv:read']]);
    }

    // Modifier un RDV (client propriétaire ou admin)
    #[Route('/api/rendezvous/{id}', name: 'api_rdv_update', methods: ['PUT', 'PATCH'])]
    public function update(
        RendezVous $rdv = null,
        Request $request,
        EntityManagerInterface $em,
        PrestationRepository $prestationRepo,
        UserRepository $userRepo
    ): JsonResponse {
        if (!$rdv) {
            return $this->json(['error' => 'RDV non trouvé'], 404);
        }
        $user = $this->getUser();
        if (
            $rdv->getClient() !== $user &&
            !in_array('ROLE_ADMIN', $user->getRoles())
        ) {
            return $this->json(['error' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['prestation_id'])) {
            $prestation = $prestationRepo->find($data['prestation_id']);
            if ($prestation) $rdv->setPrestation($prestation);
        }
        if (isset($data['coiffeur_id'])) {
            $coiffeur = $userRepo->find($data['coiffeur_id']);
            if ($coiffeur) $rdv->setCoiffeur($coiffeur);
        }
        if (isset($data['date'])) {
            $rdv->setDate(\DateTime::createFromFormat('Y-m-d', $data['date']));
        }
        if (isset($data['heureDebut'])) {
            $rdv->setHeureDebut(\DateTime::createFromFormat('H:i', $data['heureDebut']));
        }
        if (isset($data['statut'])) {
            $rdv->setStatut($data['statut']);
        }

        $em->flush();

        return $this->json(['message' => 'Rendez-vous modifié']);
    }

    // Annuler (supprimer) un rendez-vous (client propriétaire ou admin)
    #[Route('/api/rendezvous/{id}', name: 'api_rdv_delete', methods: ['DELETE'])]
    public function delete(RendezVous $rdv = null, EntityManagerInterface $em): JsonResponse
    {
        if (!$rdv) {
            return $this->json(['error' => 'RDV non trouvé'], 404);
        }
        $user = $this->getUser();
        if (
            $rdv->getClient() !== $user &&
            !in_array('ROLE_ADMIN', $user->getRoles())
        ) {
            return $this->json(['error' => 'Accès interdit'], 403);
        }

        $em->remove($rdv);
        $em->flush();

        return $this->json(['message' => 'Rendez-vous annulé.']);
    }
}
