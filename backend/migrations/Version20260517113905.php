<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517113905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_emails (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chat_messages (id INT AUTO_INCREMENT NOT NULL, message_text LONGTEXT NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, chat_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_EF20C9A6F624B39D (sender_id), INDEX idx_chat_id (chat_id), INDEX idx_is_read (is_read), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chats (id INT AUTO_INCREMENT NOT NULL, last_message_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, admin_id INT DEFAULT NULL, INDEX IDX_2D68180F642B8210 (admin_id), INDEX idx_user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE import_logs (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, errors JSON DEFAULT NULL, created_at DATETIME NOT NULL, admin_id INT NOT NULL, INDEX idx_admin_id (admin_id), INDEX idx_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, chat_id INT NOT NULL, ring_id INT NOT NULL, INDEX IDX_62809DB01A9A7125 (chat_id), INDEX IDX_62809DB0D0935A5A (ring_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ring_groups (id INT AUTO_INCREMENT NOT NULL, type_code VARCHAR(50) NOT NULL, name_ru VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, brand VARCHAR(100) DEFAULT NULL, material_en VARCHAR(100) DEFAULT NULL, material_ru VARCHAR(100) DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, dimensions_photo_url VARCHAR(255) DEFAULT NULL, column_names JSON DEFAULT NULL, is_hidden TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX idx_type_code (type_code), INDEX idx_brand (brand), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rings (id INT AUTO_INCREMENT NOT NULL, part_number VARCHAR(100) NOT NULL, dimensions JSON NOT NULL, in_stock TINYINT NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, is_hidden TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, ring_group_id INT NOT NULL, INDEX IDX_A2E7C7F76723F36A (ring_group_id), INDEX idx_part_number (part_number), INDEX idx_in_stock (in_stock), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE site_settings (id INT AUTO_INCREMENT NOT NULL, setting_key VARCHAR(100) NOT NULL, setting_value LONGTEXT DEFAULT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E9081F1F5FA1E697 (setting_key), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_blocked TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT FK_EF20C9A61A9A7125 FOREIGN KEY (chat_id) REFERENCES chats (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT FK_EF20C9A6F624B39D FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chats ADD CONSTRAINT FK_2D68180FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chats ADD CONSTRAINT FK_2D68180F642B8210 FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE import_logs ADD CONSTRAINT FK_1DA328DC642B8210 FOREIGN KEY (admin_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB01A9A7125 FOREIGN KEY (chat_id) REFERENCES chats (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0D0935A5A FOREIGN KEY (ring_id) REFERENCES rings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rings ADD CONSTRAINT FK_A2E7C7F76723F36A FOREIGN KEY (ring_group_id) REFERENCES ring_groups (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY FK_EF20C9A61A9A7125');
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY FK_EF20C9A6F624B39D');
        $this->addSql('ALTER TABLE chats DROP FOREIGN KEY FK_2D68180FA76ED395');
        $this->addSql('ALTER TABLE chats DROP FOREIGN KEY FK_2D68180F642B8210');
        $this->addSql('ALTER TABLE import_logs DROP FOREIGN KEY FK_1DA328DC642B8210');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB01A9A7125');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0D0935A5A');
        $this->addSql('ALTER TABLE rings DROP FOREIGN KEY FK_A2E7C7F76723F36A');
        $this->addSql('DROP TABLE admin_emails');
        $this->addSql('DROP TABLE chat_messages');
        $this->addSql('DROP TABLE chats');
        $this->addSql('DROP TABLE import_logs');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE ring_groups');
        $this->addSql('DROP TABLE rings');
        $this->addSql('DROP TABLE site_settings');
        $this->addSql('DROP TABLE users');
    }
}
