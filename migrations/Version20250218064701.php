<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218064701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE document_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql( "create type document_state as enum('draft','published')");

        $this->addSql("CREATE TABLE document (idocid INT NOT NULL default nextval('document_seq'), iuserid INT NOT NULL, state document_state NOT NULL, payload JSONB NOT NULL, create_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, modify_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(idocid))");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE document_seq CASCADE');
        $this->addSql('DROP TABLE document');
    }
}
