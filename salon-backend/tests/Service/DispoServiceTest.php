<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\DispoService;
use App\Entity\User;
use App\Entity\Prestation;
use App\Entity\RendezVous;
use App\Entity\CoiffeurHoraire;
use App\Entity\FermetureExceptionnelle;

class DispoServiceTest extends TestCase
{
    private $rdvRepo;
    private $fermetureRepo;
    private $horaireRepo;
    private $dispoService;
    private $coiffeur;
    private $prestation;

    protected function setUp(): void
    {
        $this->rdvRepo = $this->createMock(\App\Repository\RendezVousRepository::class);
        $this->fermetureRepo = $this->createMock(\App\Repository\FermetureExceptionnelleRepository::class);
        $this->horaireRepo = $this->createMock(\App\Repository\CoiffeurHoraireRepository::class);

        $this->dispoService = new DispoService(
            $this->rdvRepo,
            $this->fermetureRepo,
            $this->horaireRepo
        );

        $this->coiffeur = new User();
        $this->prestation = (new Prestation())->setDuree(20); // 20 minutes
    }

    public function testCreneauDisponible()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut((new \DateTime('08:00')))
            ->setHeureFin((new \DateTime('14:00')));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);

        $this->rdvRepo->method('findBy')->willReturn([]);

        $date = new \DateTime('2025-06-11'); // mercredi
        $heureDebut = new \DateTime('10:00');
        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertTrue($result, 'Créneau libre doit être dispo');
    }

    public function testFermetureExceptionnelle()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(new FermetureExceptionnelle());
        $this->horaireRepo->method('findBy')->willReturn([]);
        $this->rdvRepo->method('findBy')->willReturn([]);

        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('10:00');
        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertFalse($result, 'Fermeture exceptionnelle doit empêcher la réservation');
    }

    public function testCreneauHorsHoraire()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut((new \DateTime('14:00')))
            ->setHeureFin((new \DateTime('18:00')));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);
        $this->rdvRepo->method('findBy')->willReturn([]);

        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('10:00');
        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertFalse($result, 'Créneau hors horaires doit être refusé');
    }

    public function testCreneauChevauchement()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut((new \DateTime('08:00')))
            ->setHeureFin((new \DateTime('14:00')));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);

        // RDV existant : 10:00–10:20
        $rdv = (new RendezVous())
            ->setCoiffeur($this->coiffeur)
            ->setHeureDebut((new \DateTime('10:00')))
            ->setPrestation((new Prestation())->setDuree(20));

        $this->rdvRepo->method('findBy')->willReturn([$rdv]);

        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('10:10'); // chevauche 10:00–10:20

        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertFalse($result, 'Chevauchement doit être refusé');
    }

    public function testCreneauJusteApresUnRdv()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut((new \DateTime('08:00')))
            ->setHeureFin((new \DateTime('14:00')));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);

        // RDV existant : 10:00–10:20
        $rdv = (new RendezVous())
            ->setCoiffeur($this->coiffeur)
            ->setHeureDebut((new \DateTime('10:00')))
            ->setPrestation((new Prestation())->setDuree(20));
        $this->rdvRepo->method('findBy')->willReturn([$rdv]);

        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('10:20'); // juste après

        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertTrue($result, 'Créneau juste après doit être accepté');
    }

    public function testLonguePrestationPasseSiPlageSuffisante()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('14:00'));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);
        $this->rdvRepo->method('findBy')->willReturn([]);

        $longuePrestation = (new Prestation())->setDuree(240); // 4h
        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('09:00'); // 09:00–13:00

        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $longuePrestation);

        $this->assertTrue($result, 'Longue prestation possible sur grande plage horaire');
    }

    public function testLonguePrestationHorsPlageRefusee()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('10:00'));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);
        $this->rdvRepo->method('findBy')->willReturn([]);

        $longuePrestation = (new Prestation())->setDuree(150); // 2h30
        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('08:00'); // 08:00–10:30 (dépasse)

        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $longuePrestation);

        $this->assertFalse($result, 'Longue prestation refusée si dépasse la plage');
    }

    public function testPlusieursCoiffeursPasDeConflit()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $coiffeur2 = new User();

        // Coiffeur 1
        $horaire1 = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('14:00'));

        // Coiffeur 2
        $horaire2 = (new CoiffeurHoraire())
            ->setCoiffeur($coiffeur2)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('14:00'));

        $this->horaireRepo->method('findBy')->willReturnCallback(function ($params) use ($horaire1, $horaire2, $coiffeur2) {
            if (isset($params['coiffeur']) && $params['coiffeur'] === $coiffeur2) return [$horaire2];
            return [$horaire1];
        });

        // Un RDV déjà pris pour coiffeur 1, pas pour coiffeur 2
        $rdv = (new RendezVous())
            ->setCoiffeur($this->coiffeur)
            ->setHeureDebut(new \DateTime('10:00'))
            ->setPrestation((new Prestation())->setDuree(20));
        $this->rdvRepo->method('findBy')->willReturnCallback(function ($params) use ($rdv, $coiffeur2) {
            if (isset($params['coiffeur']) && $params['coiffeur'] === $coiffeur2) return [];
            return [$rdv];
        });

        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('10:00');

        // Coiffeur 1 : conflit
        $result1 = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);
        // Coiffeur 2 : pas de conflit
        $result2 = $this->dispoService->isDispo($coiffeur2, $date, $heureDebut, $this->prestation);

        $this->assertFalse($result1, 'Conflit sur coiffeur 1');
        $this->assertTrue($result2, 'Pas de conflit sur coiffeur 2');
    }

    public function testCreneauEnBordDeFermeture()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('14:00'));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);
        $this->rdvRepo->method('findBy')->willReturn([]);

        // Début à 13:40, coupe = 20 min, finit pile à 14:00
        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('13:40');

        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertTrue($result, 'Créneau en bord de fermeture accepté (pile à la fin)');
    }

    public function testPlusieursRdvAlaSuiteOK()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('14:00'));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);

        // RDV existant 10:00–10:20
        $rdv = (new RendezVous())
            ->setCoiffeur($this->coiffeur)
            ->setHeureDebut(new \DateTime('10:00'))
            ->setPrestation((new Prestation())->setDuree(20));
        $this->rdvRepo->method('findBy')->willReturn([$rdv]);

        $date = new \DateTime('2025-06-11');

        // Juste après 10:20
        $heureDebut = new \DateTime('10:20');
        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $this->prestation);

        $this->assertTrue($result, 'Deux RDV à la suite doivent être autorisés');
    }

    public function testCreneauDebutFinIdentiquesAvecDureeZero()
    {
        $this->fermetureRepo->method('findOneBy')->willReturn(null);

        $horaire = (new CoiffeurHoraire())
            ->setCoiffeur($this->coiffeur)
            ->setJourSemaine(3)
            ->setHeureDebut(new \DateTime('08:00'))
            ->setHeureFin(new \DateTime('14:00'));
        $this->horaireRepo->method('findBy')->willReturn([$horaire]);
        $this->rdvRepo->method('findBy')->willReturn([]);

        $date = new \DateTime('2025-06-11');
        $heureDebut = new \DateTime('10:00'); // même heure de début et fin

        // Durée de la prestation à 0 minutes
        $prestationZeroDuree = (new Prestation())->setDuree(0);

        $result = $this->dispoService->isDispo($this->coiffeur, $date, $heureDebut, $prestationZeroDuree);

        $this->assertFalse($result, 'Créneau avec durée zéro doit être refusé');
    }
}
