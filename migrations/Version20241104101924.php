<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104101924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock CHANGE quantity_xs quantity_xs INT DEFAULT NULL, CHANGE quantity_s quantity_s INT DEFAULT NULL, CHANGE quantity_m quantity_m INT DEFAULT NULL, CHANGE quantity_l quantity_l INT DEFAULT NULL, CHANGE quantity_xl quantity_xl INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock CHANGE quantity_xs quantity_xs INT NOT NULL, CHANGE quantity_s quantity_s INT NOT NULL, CHANGE quantity_m quantity_m INT NOT NULL, CHANGE quantity_l quantity_l INT NOT NULL, CHANGE quantity_xl quantity_xl INT NOT NULL');
    }
}
