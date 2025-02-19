<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219064713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE users_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql("CREATE TABLE users (iuserid INT NOT NULL default nextval('users_seq'), vclogin VARCHAR(255)  NOT NULL, vcpassword VARCHAR(255)  NOT NULL, PRIMARY KEY(iuserid))");
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76C3B21E85 FOREIGN KEY (iuserid) REFERENCES users (iuserid) on delete restrict on update restrict');
        $this->addSql('CREATE INDEX IDX_D8698A76C3B21E85 ON document (iuserid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE users_seq CASCADE');
        $this->addSql('DROP TABLE users');
    }
}
