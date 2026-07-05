<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление полей для подтверждения email и отслеживания уведомлений';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD verification_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD email_notification_sent_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_verification_token ON users (verification_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_verification_token ON users');
        $this->addSql('ALTER TABLE users DROP verification_token');
        $this->addSql('ALTER TABLE users DROP email_notification_sent_at');
    }
}
