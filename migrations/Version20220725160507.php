<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220725160507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1CCD7E912 ON category (menu_id)');
        $this->addSql('ALTER TABLE dishes DROP FOREIGN KEY FK_584DD35DCCD7E912');
        $this->addSql('DROP INDEX IDX_584DD35DCCD7E912 ON dishes');
        $this->addSql('ALTER TABLE dishes DROP menu_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1CCD7E912');
        $this->addSql('DROP INDEX IDX_64C19C1CCD7E912 ON category');
        $this->addSql('ALTER TABLE category DROP menu_id');
        $this->addSql('ALTER TABLE dishes ADD menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE dishes ADD CONSTRAINT FK_584DD35DCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_584DD35DCCD7E912 ON dishes (menu_id)');
    }
}
