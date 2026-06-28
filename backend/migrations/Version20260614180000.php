<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавление полей описания в таблицу ring_groups
 */
final class Version20260614180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление поля description_ru в таблицу ring_groups';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ring_groups ADD description_ru TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ring_groups DROP description_ru');
    }
}
