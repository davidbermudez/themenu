<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220716202637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE business (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, caption VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postcode VARCHAR(5) DEFAULT NULL, phone VARCHAR(13) DEFAULT NULL, country INT DEFAULT NULL, date_created DATETIME NOT NULL, date_modify DATETIME DEFAULT NULL, INDEX IDX_8D36E38A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, caption VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dishes (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, menu_id INT NOT NULL, caption VARCHAR(255) NOT NULL, INDEX IDX_584DD35D12469DE2 (category_id), INDEX IDX_584DD35DCCD7E912 (menu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, business_id INT NOT NULL, INDEX IDX_7D053A93A89DB457 (business_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE variation (id INT AUTO_INCREMENT NOT NULL, dishe_id INT NOT NULL, price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_629B33EA9EA120EE (dishe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT FK_8D36E38A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dishes ADD CONSTRAINT FK_584DD35D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE dishes ADD CONSTRAINT FK_584DD35DCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE variation ADD CONSTRAINT FK_629B33EA9EA120EE FOREIGN KEY (dishe_id) REFERENCES dishes (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93A89DB457');
        $this->addSql('ALTER TABLE dishes DROP FOREIGN KEY FK_584DD35D12469DE2');
        $this->addSql('ALTER TABLE variation DROP FOREIGN KEY FK_629B33EA9EA120EE');
        $this->addSql('ALTER TABLE dishes DROP FOREIGN KEY FK_584DD35DCCD7E912');
        $this->addSql('DROP TABLE business');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE dishes');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE variation');
    }
}
