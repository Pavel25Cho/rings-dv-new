<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create chat_attachments table for secure file storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chat_attachments (
            id INT AUTO_INCREMENT NOT NULL,
            message_id INT NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            stored_filename VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_stored_filename (stored_filename),
            INDEX idx_message_id (message_id),
            INDEX IDX_chat_attachments_message_id (message_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE chat_attachments ADD CONSTRAINT FK_chat_attachments_message_id 
            FOREIGN KEY (message_id) REFERENCES chat_messages (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE chat_messages MODIFY message_text TEXT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_attachments DROP FOREIGN KEY FK_chat_attachments_message_id');
        $this->addSql('DROP TABLE chat_attachments');
        $this->addSql('ALTER TABLE chat_messages MODIFY message_text TEXT NOT NULL');
    }
}
