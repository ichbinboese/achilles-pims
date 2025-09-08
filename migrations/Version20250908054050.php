<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250908054050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE APPOrder (id INT AUTO_INCREMENT NOT NULL, orderId VARCHAR(255) NOT NULL, orderNr VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE APPProduct (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, productId INT NOT NULL, productNr VARCHAR(255) NOT NULL, oxOrderNr VARCHAR(255) NOT NULL, ddPosition INT NOT NULL, INDEX IDX_341D54178D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE APPProduct ADD CONSTRAINT FK_341D54178D9F6D38 FOREIGN KEY (order_id) REFERENCES APPOrder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE APPProduct DROP FOREIGN KEY FK_341D54178D9F6D38');
        $this->addSql('DROP TABLE APPOrder');
        $this->addSql('DROP TABLE APPProduct');
    }
}
