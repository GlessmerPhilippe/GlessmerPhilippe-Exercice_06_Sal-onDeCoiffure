<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609182918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE coiffeur_horaire (id SERIAL NOT NULL, coiffeur_id INT DEFAULT NULL, jour_semaine INT DEFAULT NULL, heure_debut TIME(0) WITHOUT TIME ZONE DEFAULT NULL, heure_fin TIME(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E462D40BBD427C57 ON coiffeur_horaire (coiffeur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE fermeture_exceptionnelle (id SERIAL NOT NULL, date DATE NOT NULL, motif VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prestation (id SERIAL NOT NULL, nom VARCHAR(150) NOT NULL, duree INT DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rendez_vous (id SERIAL NOT NULL, client_id INT DEFAULT NULL, coiffeur_id INT DEFAULT NULL, prestation_id INT DEFAULT NULL, date DATE NOT NULL, heure_debut TIME(0) WITHOUT TIME ZONE NOT NULL, statut VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_65E8AA0A19EB6921 ON rendez_vous (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_65E8AA0ABD427C57 ON rendez_vous (coiffeur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_65E8AA0A9E45C554 ON rendez_vous (prestation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coiffeur_horaire ADD CONSTRAINT FK_E462D40BBD427C57 FOREIGN KEY (coiffeur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A19EB6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0ABD427C57 FOREIGN KEY (coiffeur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A9E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coiffeur_horaire DROP CONSTRAINT FK_E462D40BBD427C57
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous DROP CONSTRAINT FK_65E8AA0A19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous DROP CONSTRAINT FK_65E8AA0ABD427C57
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous DROP CONSTRAINT FK_65E8AA0A9E45C554
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coiffeur_horaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fermeture_exceptionnelle
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prestation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rendez_vous
        SQL);
    }
}
