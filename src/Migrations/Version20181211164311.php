<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181211164311 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE comments ADD visible SMALLINT DEFAULT NULL');
        $this->addSql('DROP INDEX tconst ON episodes');
        $this->addSql('ALTER TABLE episodes CHANGE tconst tconst TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX primaryTitle ON titles');
        $this->addSql('ALTER TABLE titles CHANGE primaryTitle primaryTitle TEXT DEFAULT NULL, CHANGE startYear startYear TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX userid ON watched');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE comments DROP visible');
        $this->addSql('ALTER TABLE episodes CHANGE tconst tconst VARCHAR(20) DEFAULT NULL COLLATE utf8_general_ci');
        $this->addSql('CREATE INDEX tconst ON episodes (tconst)');
        $this->addSql('ALTER TABLE titles CHANGE primaryTitle primaryTitle VARCHAR(150) DEFAULT NULL COLLATE utf8_general_ci, CHANGE startYear startYear INT DEFAULT NULL');
        $this->addSql('CREATE FULLTEXT INDEX primaryTitle ON titles (primaryTitle)');
        $this->addSql('CREATE INDEX userid ON watched (userid, episode)');
    }
}
