<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030132658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier_item ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE panier_item ADD CONSTRAINT FK_EBFD00674584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_EBFD00674584665A ON panier_item (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier_item DROP FOREIGN KEY FK_EBFD00674584665A');
        $this->addSql('DROP INDEX IDX_EBFD00674584665A ON panier_item');
        $this->addSql('ALTER TABLE panier_item DROP product_id');
    }
}
