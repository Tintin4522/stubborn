<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102182935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock ADD quantity_xs INT NOT NULL, ADD quantity_s INT NOT NULL, ADD quantity_m INT NOT NULL, ADD quantity_l INT NOT NULL, ADD quantity_xl INT NOT NULL, DROP quantityXS, DROP quantityS, DROP quantityM, DROP quantityL, DROP quantityXL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock ADD quantityXS INT NOT NULL, ADD quantityS INT NOT NULL, ADD quantityM INT NOT NULL, ADD quantityL INT NOT NULL, ADD quantityXL INT NOT NULL, DROP quantity_xs, DROP quantity_s, DROP quantity_m, DROP quantity_l, DROP quantity_xl');
    }
}
