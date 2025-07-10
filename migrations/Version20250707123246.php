<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707123246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsDruckfarben ADD easymapping VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsFormat ADD easymapping VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsKaschierung ADD easysmapping VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsPapier ADD easysmapping VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsProdukt ADD easysmapping VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsKaschierung DROP easysmapping
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsProdukt DROP easysmapping
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsFormat DROP easymapping
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsPapier DROP easysmapping
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE PimsDruckfarben DROP easymapping
        SQL);
    }
}
