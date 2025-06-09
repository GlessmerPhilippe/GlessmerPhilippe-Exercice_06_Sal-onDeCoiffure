<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Prestation;
use App\Repository\RendezVousRepository;
use App\Repository\FermetureExceptionnelleRepository;
use App\Repository\CoiffeurHoraireRepository;

class DispoService
{
    public function __construct(
        private RendezVousRepository $rdvRepo,
        private FermetureExceptionnelleRepository $fermetureRepo,
        private CoiffeurHoraireRepository $horaireRepo
    ) {}

    /**
     * Convertit un DateTime en nombre de minutes depuis minuit (HH:MM)
     */
    private function toMinutes(\DateTime $dt): int
    {
        return ((int)$dt->format('H')) * 60 + (int)$dt->format('i');
    }

    public function isDispo(
        User $coiffeur,
        \DateTime $date,
        \DateTime $heureDebut,
        Prestation $prestation
    ): bool
    {
        // Refuse tout créneau de durée nulle ou négative
        if ($prestation->getDuree() <= 0) {
            return false;
        }
        // (Optionnel) Refuse créneau début = fin (si besoin)
        // On peut le déduire car durée 0 => même heure, mais au cas où :
        // if ($prestation->getDuree() === 0) return false;
        
        // 1. Vérifier si le jour est une fermeture exceptionnelle
        $fermeture = $this->fermetureRepo->findOneBy(['date' => $date]);
        if ($fermeture) return false;

        // 2. Vérifier que le coiffeur travaille ce jour-là
        $jourSemaine = (int)$date->format('w'); // 0=dimanche, 1=lundi,...
        $horaires = $this->horaireRepo->findBy(['coiffeur' => $coiffeur, 'jourSemaine' => $jourSemaine]);

        $dispo = false;
        // Calcul des minutes depuis minuit pour la demande
        $hdMinutes = $this->toMinutes($heureDebut);
        $hfMinutes = $hdMinutes + $prestation->getDuree();

        foreach ($horaires as $h) {
            $horaireDebutMinutes = $this->toMinutes($h->getHeureDebut());
            $horaireFinMinutes   = $this->toMinutes($h->getHeureFin());
            if (
                $horaireDebutMinutes <= $hdMinutes &&
                $horaireFinMinutes >= $hfMinutes
            ) {
                $dispo = true;
                break;
            }
        }
        if (!$dispo) return false;

        // 3. Vérifier qu’aucun autre RDV du coiffeur ne chevauche ce créneau
        $rdvs = $this->rdvRepo->findBy(['coiffeur' => $coiffeur, 'date' => $date]);
        foreach ($rdvs as $rdv) {
            $rdvDebutMinutes = $this->toMinutes($rdv->getHeureDebut());
            $rdvFinMinutes = $rdvDebutMinutes + $rdv->getPrestation()->getDuree();
            // chevauchement ?
            if (
                ($hdMinutes < $rdvFinMinutes) &&
                ($hfMinutes > $rdvDebutMinutes)
            ) {
                return false;
            }
        }
        // Si tout est OK
        return true;
    }
    
}
