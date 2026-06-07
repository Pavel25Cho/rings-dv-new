<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Изменение структуры хранения фотографий колец с одной фотографии на массив
 */
final class Version20260607093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Преобразование поля photo_url в photos (JSON массив) в таблице rings';
    }

    public function up(Schema $schema): void
    {
        // Добавляем новое поле photos как JSON
        $this->addSql('ALTER TABLE rings ADD photos JSON NOT NULL');
        
        // Переносим существующие данные из photo_url в photos
        $this->addSql("UPDATE rings SET photos = CASE 
            WHEN photo_url IS NOT NULL AND photo_url != '' THEN JSON_ARRAY(photo_url)
            ELSE JSON_ARRAY()
        END");
        
        // Удаляем старое поле photo_url
        $this->addSql('ALTER TABLE rings DROP COLUMN photo_url');
    }

    public function down(Schema $schema): void
    {
        // Добавляем обратно поле photo_url
        $this->addSql('ALTER TABLE rings ADD photo_url VARCHAR(255) DEFAULT NULL');
        
        // Переносим первую фотографию из массива обратно в photo_url
        $this->addSql("UPDATE rings SET photo_url = CASE 
            WHEN JSON_LENGTH(photos) > 0 THEN JSON_UNQUOTE(JSON_EXTRACT(photos, '$[0]'))
            ELSE NULL
        END");
        
        // Удаляем поле photos
        $this->addSql('ALTER TABLE rings DROP COLUMN photos');
    }
}
