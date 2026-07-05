<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление поля receiver для админов';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD receiver TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP receiver');
    }
}
