<?php

namespace App\Controller\Api\Salon;

use DateTime;
use App\Entity\User;
use App\Entity\RendezVous;
use App\Service\DispoService;
use App\Entity\CoiffeurHoraire;
use App\Repository\UserRepository;
use App\Repository\PrestationRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CoiffeurHoraireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoiffeursController extends AbstractController
{
    // Liste tous les coiffeurs (users avec rôle coiffeur)
    #[Route('/api/coiffeurs', name: 'api_coiffeurs_list', methods: ['GET'])]
    public function list(UserRepository $repo): JsonResponse
    {
        $coiffeurs = $repo->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode('ROLE_COIFFEUR'))
            ->getQuery()
            ->getResult();

        return $this->json($coiffeurs, 200, [], ['groups' => ['user:read']]);
    }

    // Voir les horaires d'un coiffeur
    #[Route('/api/coiffeurs/{id}/horaires', name: 'api_coiffeur_horaires', methods: ['GET'])]
    public function horaires(User $coiffeur, CoiffeurHoraireRepository $repo): JsonResponse
    {
        $horaires = $repo->findBy(['coiffeur' => $coiffeur]);
        return $this->json($horaires, 200, [], ['groups' => ['horaire:read']]);
    }

    // Ajouter/éditer un horaire (coiffeur ou admin)
    #[Route('/api/coiffeurs/{id}/horaires', name: 'api_coiffeur_add_horaire', methods: ['POST'])]
    public function addHoraire(
        User $coiffeur,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        // Vérifie que l'utilisateur connecté est le coiffeur ou un admin
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            $this->getUser()?->getId() !== $coiffeur->getId()
        ) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (
            !isset($data['jourSemaine'], $data['heureDebut'], $data['heureFin'])
        ) {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        $horaire = new CoiffeurHoraire();
        $horaire->setCoiffeur($coiffeur);
        $horaire->setJourSemaine($data['jourSemaine']);
        $horaire->setHeureDebut(\DateTime::createFromFormat('H:i', $data['heureDebut']));
        $horaire->setHeureFin(\DateTime::createFromFormat('H:i', $data['heureFin']));
        $em->persist($horaire);
        $em->flush();

        return $this->json(['message' => 'Horaire ajouté !'], 201);
    }

    #[Route('/api/coiffeurs/horaires/batch', name: 'api_coiffeurs_horaires_batch', methods: ['POST'])]
    public function batch(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Le payload doit être un tableau'], 400);
        }

        $created = [];
        foreach ($data as $item) {
            if (
                !isset($item['coiffeur_id'], $item['jourSemaine'], $item['heureDebut'], $item['heureFin'])
            ) {
                continue; // ignore si incomplet
            }
            $coiffeur = $userRepo->find($item['coiffeur_id']);
            if (!$coiffeur) continue;

            $horaire = new CoiffeurHoraire();
            $horaire->setCoiffeur($coiffeur);
            $horaire->setJourSemaine($item['jourSemaine']);
            $horaire->setHeureDebut(\DateTime::createFromFormat('H:i', $item['heureDebut']));
            $horaire->setHeureFin(\DateTime::createFromFormat('H:i', $item['heureFin']));
            $em->persist($horaire);

            $created[] = [
                'coiffeur' => $coiffeur->getId(),
                'jourSemaine' => $item['jourSemaine'],
                'heureDebut' => $item['heureDebut'],
                'heureFin' => $item['heureFin'],
            ];
        }
        $em->flush();

        return $this->json([
            'message' => count($created) . ' horaires créés',
            'horaires' => $created
        ], 201);
    }

    // Voir tous les rendez-vous d’un coiffeur (planning)
    #[Route('/api/coiffeurs/{id}/rendezvous', name: 'api_coiffeur_rdv', methods: ['GET'])]
    public function rdvs(User $coiffeur, RendezVousRepository $repo): JsonResponse
    {
        $rdvs = $repo->findBy(['coiffeur' => $coiffeur]);
        return $this->json($rdvs, 200, [], ['groups' => ['rdv:read']]);
    }

    #[Route('/api/coiffeurs/{id}/disponibilites', name: 'api_coiffeur_disponibilites', methods: ['GET'])]
    public function disponibilites(
        User $coiffeur,
        Request $request,
        DispoService $dispoService,
        PrestationRepository $prestationRepo // pour le temps min d’une prestation
    ): JsonResponse {
        $date = new \DateTime($request->query->get('date', 'today'));
        // Tu peux ajouter un param pour la prestation aussi

        // Exemple : on propose par défaut des créneaux de 20 min (ou durée min des prestations)
        $dureeSlot = 20;

        // Récupère les horaires du coiffeur ce jour-là
        $jourSemaine = (int)$date->format('w');
        $horaires = $coiffeur->getCoiffeurHoraires()->filter(fn($h) => $h->getJourSemaine() === $jourSemaine);
        if ($horaires->isEmpty()) {
            return $this->json([]);
        }

        $result = [];
        foreach ($horaires as $h) {
            $debut = clone $h->getHeureDebut();
            $fin = clone $h->getHeureFin();
            while ($debut < $fin) {
                // Teste la dispo (pour la prestation “coupe” ou slot par défaut)
                $prestation = $prestationRepo->findOneBy(['nom' => 'Coupe']) ?? $prestationRepo->find(1);
                if ($dispoService->isDispo($coiffeur, $date, $debut, $prestation)) {
                    $result[] = $debut->format('H:i');
                }
                $debut->modify("+$dureeSlot minutes");
            }
        }

        return $this->json($result);
    }

    
}
