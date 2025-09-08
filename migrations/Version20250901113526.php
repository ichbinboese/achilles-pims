<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901113526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE EasyOrder (id INT AUTO_INCREMENT NOT NULL, orderId VARCHAR(255) NOT NULL, orderNr VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE EasyProduct (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, productId INT NOT NULL, productNr VARCHAR(255) NOT NULL, oxOrderNr VARCHAR(255) NOT NULL, ddPosition INT NOT NULL, INDEX IDX_917FCEB8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE EasyProduct ADD CONSTRAINT FK_917FCEB8D9F6D38 FOREIGN KEY (order_id) REFERENCES EasyOrder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE EasyProduct DROP FOREIGN KEY FK_917FCEB8D9F6D38');
        $this->addSql('DROP TABLE EasyOrder');
        $this->addSql('DROP TABLE EasyProduct');
    }
}
