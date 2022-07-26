<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220726110747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE twitter_profile twitter_profile VARCHAR(255) DEFAULT NULL, CHANGE facebook_profile facebook_profile VARCHAR(255) DEFAULT NULL, CHANGE instagram_profile instagram_profile VARCHAR(255) DEFAULT NULL, CHANGE web web VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business CHANGE description description VARCHAR(255) NOT NULL, CHANGE twitter_profile twitter_profile VARCHAR(255) NOT NULL, CHANGE facebook_profile facebook_profile VARCHAR(255) NOT NULL, CHANGE instagram_profile instagram_profile VARCHAR(255) NOT NULL, CHANGE web web VARCHAR(255) NOT NULL');
    }
}
