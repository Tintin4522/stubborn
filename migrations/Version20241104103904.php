<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104103904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock ADD quantity INT NOT NULL, DROP quantity_xs, DROP quantity_s, DROP quantity_m, DROP quantity_l, DROP quantity_xl, CHANGE size size VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock ADD quantity_xs INT DEFAULT NULL, ADD quantity_s INT DEFAULT NULL, ADD quantity_m INT DEFAULT NULL, ADD quantity_l INT DEFAULT NULL, ADD quantity_xl INT DEFAULT NULL, DROP quantity, CHANGE size size VARCHAR(5) NOT NULL');
    }
}
