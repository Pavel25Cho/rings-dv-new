<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавление полей имени и телефона в таблицу пользователей
 */
final class Version20260614170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление полей name и phone в таблицу users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD phone VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP name');
        $this->addSql('ALTER TABLE users DROP phone');
    }
}
