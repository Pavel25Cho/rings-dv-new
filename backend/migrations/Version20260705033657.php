<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260705033657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_chat_attachments_message_id ON chat_attachments');
        $this->addSql('ALTER TABLE chat_attachments RENAME INDEX uniq_stored_filename TO UNIQ_78B60D4EDF8EB9B7');
        $this->addSql('ALTER TABLE chat_messages ADD email_notification_sent TINYINT DEFAULT 0 NOT NULL, CHANGE message_text message_text LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_62809DB08D9F6D38 ON order_items');
        $this->addSql('ALTER TABLE order_items RENAME INDEX idx_62809db0a4be4a58 TO IDX_62809DB0D0935A5A');
        $this->addSql('ALTER TABLE ring_groups CHANGE description_ru description_ru LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_verification_token ON users');
        $this->addSql('ALTER TABLE users CHANGE email_verified email_verified TINYINT NOT NULL, CHANGE receiver receiver TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_chat_attachments_message_id ON chat_attachments (message_id)');
        $this->addSql('ALTER TABLE chat_attachments RENAME INDEX uniq_78b60d4edf8eb9b7 TO UNIQ_stored_filename');
        $this->addSql('ALTER TABLE chat_messages DROP email_notification_sent, CHANGE message_text message_text TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_62809DB08D9F6D38 ON order_items (order_id)');
        $this->addSql('ALTER TABLE order_items RENAME INDEX idx_62809db0d0935a5a TO IDX_62809DB0A4BE4A58');
        $this->addSql('ALTER TABLE ring_groups CHANGE description_ru description_ru TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email_verified email_verified TINYINT DEFAULT 0 NOT NULL, CHANGE receiver receiver TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX idx_verification_token ON users (verification_token)');
    }
}
