<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Создание таблиц для системы заказов через чаты
 */
final class Version20260614050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблиц orders и order_items для системы заказов';
    }

    public function up(Schema $schema): void
    {
        // Создаем таблицу заказов
        $this->addSql('CREATE TABLE orders (
            id INT AUTO_INCREMENT NOT NULL,
            message_id INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            created_at DATETIME NOT NULL,
            confirmed_at DATETIME DEFAULT NULL,
            cancelled_at DATETIME DEFAULT NULL,
            INDEX idx_message_id (message_id),
            INDEX idx_status (status),
            UNIQUE INDEX UNIQ_E52FFDEE537A1329 (message_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Создаем таблицу элементов заказа
        $this->addSql('CREATE TABLE order_items (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            ring_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            part_number VARCHAR(255) NOT NULL,
            brand VARCHAR(255) DEFAULT NULL,
            INDEX idx_order_id (order_id),
            INDEX IDX_62809DB08D9F6D38 (order_id),
            INDEX IDX_62809DB0A4BE4A58 (ring_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Добавляем внешние ключи
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE537A1329 FOREIGN KEY (message_id) REFERENCES chat_messages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0A4BE4A58 FOREIGN KEY (ring_id) REFERENCES rings (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Удаляем внешние ключи
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0A4BE4A58');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE537A1329');

        // Удаляем таблицы
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
    }
}
