<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220725161020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dishes ADD caption_es VARCHAR(255) DEFAULT NULL, ADD caption_en VARCHAR(255) DEFAULT NULL, ADD caption_ca VARCHAR(255) DEFAULT NULL, ADD description_es VARCHAR(255) DEFAULT NULL, ADD description_en VARCHAR(255) DEFAULT NULL, ADD description_ca VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dishes DROP caption_es, DROP caption_en, DROP caption_ca, DROP description_es, DROP description_en, DROP description_ca');
    }
}
