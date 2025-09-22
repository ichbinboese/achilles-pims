<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922113217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE PrintList (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE PrintListItem (id INT AUTO_INCREMENT NOT NULL, printList_id INT NOT NULL, easyProduct_id INT NOT NULL, INDEX IDX_F8E2D2F77E7B626 (printList_id), INDEX IDX_F8E2D2F73548A9D6 (easyProduct_id), UNIQUE INDEX uniq_list_product (printlist_id, easyproduct_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE PrintListItem ADD CONSTRAINT FK_F8E2D2F77E7B626 FOREIGN KEY (printList_id) REFERENCES PrintList (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE PrintListItem ADD CONSTRAINT FK_F8E2D2F73548A9D6 FOREIGN KEY (easyProduct_id) REFERENCES EasyProduct (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE PrintListItem DROP FOREIGN KEY FK_F8E2D2F77E7B626');
        $this->addSql('ALTER TABLE PrintListItem DROP FOREIGN KEY FK_F8E2D2F73548A9D6');
        $this->addSql('DROP TABLE PrintList');
        $this->addSql('DROP TABLE PrintListItem');
    }
}
