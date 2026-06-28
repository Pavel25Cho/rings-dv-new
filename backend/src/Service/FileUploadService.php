<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    
    private const ALLOWED_MIME_TYPES = [
        // Images
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        // PDF
        'application/pdf',
        // Word
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        // Excel
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf',
        'doc', 'docx',
        'xls', 'xlsx'
    ];

    public function __construct(
        private SluggerInterface $slugger,
        private string $chatUploadsDirectory
    ) {
    }

    /**
     * Валидация и загрузка файла с безопасным хранением
     * 
     * @throws \RuntimeException
     */
    public function uploadChatFile(UploadedFile $file): array
    {
        // Валидация размера
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \RuntimeException('Файл слишком большой. Максимальный размер: 10 МБ');
        }

        // Валидация MIME типа
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \RuntimeException('Недопустимый тип файла. Разрешены: изображения, PDF, Word, Excel');
        }

        // Валидация расширения
        $originalFilename = $file->getClientOriginalName();
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \RuntimeException('Недопустимое расширение файла');
        }

        // Дополнительная проверка: расширение должно соответствовать MIME типу
        if (!$this->validateMimeTypeExtension($mimeType, $extension)) {
            throw new \RuntimeException('Несоответствие типа файла и расширения');
        }

        // Санитизация имени файла
        $safeFilename = $this->sanitizeFilename($originalFilename);

        // Генерация уникального имени
        $uniqueFilename = $this->generateUniqueFilename($extension);

        // Получаем размер файла ДО перемещения
        $fileSize = $file->getSize();

        try {
            // Перемещение файла в безопасную директорию
            $file->move($this->chatUploadsDirectory, $uniqueFilename);
            
            // Установка прав доступа (только чтение для веб-сервера)
            chmod($this->chatUploadsDirectory . '/' . $uniqueFilename, 0640);

            return [
                'originalFilename' => $safeFilename,
                'storedFilename' => $uniqueFilename,
                'mimeType' => $mimeType,
                'fileSize' => $fileSize
            ];
        } catch (FileException $e) {
            throw new \RuntimeException('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    /**
     * Удаление файла
     */
    public function deleteFile(string $filename): void
    {
        $filepath = $this->chatUploadsDirectory . '/' . $filename;
        
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Получение пути к файлу
     */
    public function getFilePath(string $filename): string
    {
        return $this->chatUploadsDirectory . '/' . $filename;
    }

    /**
     * Проверка существования файла
     */
    public function fileExists(string $filename): bool
    {
        return file_exists($this->getFilePath($filename));
    }

    /**
     * Санитизация имени файла
     */
    private function sanitizeFilename(string $filename): string
    {
        // Убираем потенциально опасные символы, но сохраняем кириллицу
        // Разрешаем: буквы (латиница + кириллица), цифры, пробелы, дефис, точка, подчеркивание
        $filename = preg_replace('/[^\p{L}\p{N}\s\-\._]/u', '', $filename);
        
        // Заменяем множественные пробелы на один
        $filename = preg_replace('/\s+/', ' ', $filename);
        
        // Убираем опасные последовательности
        $filename = str_replace(['..', '//', '\\\\'], '', $filename);
        $filename = trim($filename, '._- ');
        
        // Ограничиваем длину
        if (mb_strlen($filename) > 200) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $basename = mb_substr($basename, 0, 200 - mb_strlen($extension) - 1);
            $filename = $basename . '.' . $extension;
        }
        
        return $filename;
    }

    /**
     * Генерация уникального имени файла
     */
    private function generateUniqueFilename(string $extension): string
    {
        return sprintf(
            '%s_%s.%s',
            date('Y-m-d'),
            bin2hex(random_bytes(16)),
            $extension
        );
    }

    /**
     * Проверка соответствия MIME типа и расширения
     */
    private function validateMimeTypeExtension(string $mimeType, string $extension): bool
    {
        $mimeToExt = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/jpg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        ];

        return isset($mimeToExt[$mimeType]) && in_array($extension, $mimeToExt[$mimeType], true);
    }

    /**
     * Форматирование размера файла для отображения
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
