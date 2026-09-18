<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260917110425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE config_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE config (iconfigid INT NOT NULL DEFAULT nextval(\'config_seq\'), vcname VARCHAR(50) NOT NULL, vctitle TEXT NOT NULL, vcvalue JSONB DEFAULT NULL, vctype VARCHAR(50) DEFAULT \'text\' NOT NULL, vcoptions JSONB DEFAULT NULL, bvisible BOOLEAN DEFAULT true NOT NULL, nweight SMALLINT NOT NULL, iparentid INT DEFAULT NULL, PRIMARY KEY (iconfigid))');
        $this->addSql('CREATE UNIQUE INDEX config_vcname_ukey ON config (vcname, iparentid)');
        $this->addSql('CREATE INDEX IDX_D48A2F7CF6819474 ON config (iparentid)');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7CF6819474 FOREIGN KEY (iparentid) REFERENCES config (iconfigid) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE users ADD vcemail TEXT');
        $this->addSql('ALTER TABLE users ADD istateid INT');
        $this->addSql("update users set vcemail='test@mail.ru', istateid=2");
        $this->addSql('ALTER TABLE users ALTER COLUMN vcemail SET NOT NULL ');
        $this->addSql('ALTER TABLE users ALTER COLUMN istateid SET NOT NULL ');

        // внесем конфигурационные переменные
        $this->addSql(<<<SQL
DO
$$

declare
    parentId    integer;
    configId    integer;
begin

    INSERT INTO config (iparentid, vcname, vctitle, vcvalue, vctype, vcoptions, nweight)
    VALUES (NULL, 'mailer', 'Почтовик', NULL, 'group', null, 10);
    configId := currval('config_seq');

    INSERT INTO config (iparentid, vcname, vctitle, vcvalue, vctype, vcoptions, nweight)
    VALUES (configId, 'support_mail', 'Обратный адрес', to_jsonb('admin@jdoc-mail.ru'::text), 'string', null, 10);
    INSERT INTO config (iparentid, vcname, vctitle, vcvalue, vctype, vcoptions, nweight)
    VALUES (configId, 'message_signature', 'Подпись', to_jsonb('С уважением, Администратор.\nЭто письмо отправлено автоматически. Отвечать на него не нужно.'::text), 'string', null, 20);
end;
$$ language plpgsql;
SQL);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE config_seq CASCADE');
        $this->addSql('ALTER TABLE config DROP CONSTRAINT FK_D48A2F7CF6819474');
        $this->addSql('DROP TABLE config');
        $this->addSql('ALTER TABLE users DROP vcemail');
    }

    public function postUp(Schema $schema): void
    {
        //throw new \RuntimeException('ТЕСТ: Проверка структуры прошла успешно, отменяем изменения.');
    }
}
