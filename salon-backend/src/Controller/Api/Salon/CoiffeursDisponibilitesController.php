<?php

namespace App\Controller\Api\Salon;

use App\Repository\UserRepository;
use App\Repository\PrestationRepository;
use App\Service\DispoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/coiffeurs/disponibilites', name: 'api_coiffeurs_disponibilites', methods: ['GET'])]
class CoiffeursDisponibilitesController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserRepository $userRepo,
        PrestationRepository $prestationRepo,
        DispoService $dispoService
    ): JsonResponse
    {
        $date = new \DateTime($request->query->get('date', 'today'));
        $prestationId = $request->query->get('prestation_id');
        $trancheMinutes = (int)($request->query->get('tranche', 10)); // default 10min

        // Récupère la prestation à checker (ou la plus courte si rien passé)
        if ($prestationId) {
            $prestation = $prestationRepo->find($prestationId);
        } else {
            $prestation = $prestationRepo->findOneBy([], ['duree' => 'ASC']);
        }
        if (!$prestation) {
            return $this->json(['error' => 'Prestation non trouvée'], 404);
        }
        $dureePrestation = $prestation->getDuree();

        // Coiffeurs = tous ceux qui ont ROLE_COIFFEUR
        $coiffeurs = $userRepo->findByRole('ROLE_COIFFEUR');

        $result = [];
        foreach ($coiffeurs as $coiffeur) {
            $slots = [];
            $jourSemaine = (int)$date->format('w');
            foreach ($coiffeur->getCoiffeurHoraires() as $h) {
                if ($h->getJourSemaine() !== $jourSemaine) continue;
                $debut = (clone $h->getHeureDebut());
                $fin = (clone $h->getHeureFin());

                // Planning par tranche (cases horaires)
                while ($debut < $fin) {
                    $slotDebut = clone $debut;
                    $slotFin = (clone $slotDebut)->modify("+$dureePrestation minutes");

                    $isDispo = $dispoService->isDispo($coiffeur, $date, $slotDebut, $prestation);

                    $slots[] = [
                        'heure' => $slotDebut->format('H:i'),
                        'disponible' => $isDispo
                    ];
                    $debut->modify("+$trancheMinutes minutes");
                }
            }
            $result[] = [
                'coiffeur' => [
                    'id' => $coiffeur->getId(),
                    'nom' => trim($coiffeur->getPrenom().' '.$coiffeur->getNom())
                ],
                'planning' => $slots
            ];
        }
        return $this->json($result);
    }
}
CD 