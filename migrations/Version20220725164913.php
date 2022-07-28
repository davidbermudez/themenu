<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220725164913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD caption VARCHAR(50) DEFAULT NULL, ADD caption_en VARCHAR(50) DEFAULT NULL, ADD caption_ca VARCHAR(50) DEFAULT NULL, ADD description VARCHAR(255) DEFAULT NULL, ADD description_en VARCHAR(255) DEFAULT NULL, ADD description_ca VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP caption, DROP caption_en, DROP caption_ca, DROP description, DROP description_en, DROP description_ca');
    }
}
