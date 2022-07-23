<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220723173612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business CHANGE country country_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT FK_8D36E38F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_8D36E38F92F3E70 ON business (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business DROP FOREIGN KEY FK_8D36E38F92F3E70');
        $this->addSql('DROP INDEX IDX_8D36E38F92F3E70 ON business');
        $this->addSql('ALTER TABLE business CHANGE country_id country INT DEFAULT NULL');
    }
}
