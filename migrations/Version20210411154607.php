<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210411154607 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE im2021_asso_utilisateurs_produits (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_utilisateur INTEGER NOT NULL, id_produit INTEGER NOT NULL, quantite INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_99944CAC50EAE44 ON im2021_asso_utilisateurs_produits (id_utilisateur)');
        $this->addSql('CREATE INDEX IDX_99944CACF7384557 ON im2021_asso_utilisateurs_produits (id_produit)');
        $this->addSql('CREATE UNIQUE INDEX aup_index ON im2021_asso_utilisateurs_produits (id_utilisateur, id_produit)');
        $this->addSql('DROP TABLE asso_utilisateurs_produits');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asso_utilisateurs_produits (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_utilisateur INTEGER NOT NULL, id_produit INTEGER NOT NULL, quantite INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_B052AF0650EAE44 ON asso_utilisateurs_produits (id_utilisateur)');
        $this->addSql('CREATE INDEX IDX_B052AF06F7384557 ON asso_utilisateurs_produits (id_produit)');
        $this->addSql('CREATE UNIQUE INDEX aup_idx ON asso_utilisateurs_produits (id_utilisateur, id_produit)');
        $this->addSql('DROP TABLE im2021_asso_utilisateurs_produits');
    }
}
