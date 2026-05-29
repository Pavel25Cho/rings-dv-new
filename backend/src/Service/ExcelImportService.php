<?php

namespace App\Service;

use App\Entity\Ring;
use App\Entity\RingGroup;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Log\LoggerInterface;

class ExcelImportService
{
    private const GROUP_SHEET_INDEX = 2; // 3-я страница (индексация с 0)
    private const DEFAULT_COLUMN_NAMES = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'K'];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function importFromFile(string $filePath): array
    {
        // Увеличиваем лимит времени выполнения до 10 минут
        set_time_limit(600);
        
        // Увеличиваем лимит памяти до 1GB
        ini_set('memory_limit', '1024M');
        
        // Настраиваем PhpSpreadsheet для минимального потребления памяти
        \PhpOffice\PhpSpreadsheet\Settings::setCache(
            new \PhpOffice\PhpSpreadsheet\Collection\Memory\SimpleCache3()
        );
        
        // Читаем файл в режиме только для чтения с минимальным потреблением памяти
        $reader = IOFactory::createReader(IOFactory::identify($filePath));
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        
        $spreadsheet = $reader->load($filePath);
        
        $stats = [
            'groups_created' => 0,
            'groups_updated' => 0,
            'rings_created' => 0,
            'rings_updated' => 0,
            'errors' => []
        ];

        try {
            // Шаг 1: Импорт групп со страницы 3
            $this->importGroups($spreadsheet, $stats);
            
            // Освобождаем память после импорта групп
            gc_collect_cycles();

            // Шаг 2: Импорт колец со страниц 4+
            $this->importRings($spreadsheet, $stats);
        } catch (\Exception $e) {
            $this->logger->error('Import error: ' . $e->getMessage());
            $stats['errors'][] = 'Критическая ошибка: ' . $e->getMessage();
        } finally {
            // Освобождаем память
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            gc_collect_cycles();
        }

        return $stats;
    }

    private function importGroups(Spreadsheet $spreadsheet, array &$stats): void
    {
        $sheet = $spreadsheet->getSheet(self::GROUP_SHEET_INDEX);
        $highestRow = $sheet->getHighestRow();

        // Начинаем со 2-й строки (пропускаем заголовок)
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $nameEn = $this->getCellValue($sheet, 'B', $row);
                $nameRu = $this->getCellValue($sheet, 'C', $row);
                $typeCode = $this->getCellValue($sheet, 'D', $row);
                $brand = $this->getCellValue($sheet, 'F', $row);
                $materialRaw = $this->getCellValue($sheet, 'G', $row);

                // Пропускаем пустые строки
                if (empty($typeCode)) {
                    continue;
                }

                // Удаляем иероглифы из материала
                $material = $this->removeNonLatinCharacters($materialRaw);

                // Ищем существующую группу или создаем новую
                $group = $this->entityManager->getRepository(RingGroup::class)
                    ->findOneBy(['typeCode' => $typeCode]);

                if ($group) {
                    $stats['groups_updated']++;
                } else {
                    $group = new RingGroup();
                    $group->setTypeCode($typeCode);
                    $stats['groups_created']++;
                }

                $group->setNameEn($nameEn);
                $group->setNameRu($nameRu);
                $group->setBrand($brand);
                $group->setMaterialEn($material);
                $group->setColumnNames(self::DEFAULT_COLUMN_NAMES);

                $this->entityManager->persist($group);
            } catch (\Exception $e) {
                $stats['errors'][] = "Ошибка импорта группы (строка $row): " . $e->getMessage();
                $this->logger->warning("Group import error at row $row: " . $e->getMessage());
            }
        }

        $this->entityManager->flush();
    }

    private function importRings(Spreadsheet $spreadsheet, array &$stats): void
    {
        $sheetCount = $spreadsheet->getSheetCount();

        // Обрабатываем страницы начиная с 4-й (индекс 3)
        for ($sheetIndex = 3; $sheetIndex < $sheetCount; $sheetIndex++) {
            $sheet = $spreadsheet->getSheet($sheetIndex);

            try {
                // Извлекаем typeCode из A1
                $cellA1 = $this->getCellValue($sheet, 'A', 1);
                $typeCode = $this->extractTypeCode($cellA1);

                if (empty($typeCode)) {
                    $stats['errors'][] = "Страница " . ($sheetIndex + 1) . ": не найден typeCode в A1";
                    continue;
                }

                // Ищем группу по typeCode
                $group = $this->entityManager->getRepository(RingGroup::class)
                    ->findOneBy(['typeCode' => $typeCode]);

                if (!$group) {
                    $this->logger->info("Skipping sheet " . ($sheetIndex + 1) . ": group with typeCode '$typeCode' not found");
                    continue;
                }

                // Сохраняем ID группы
                $groupId = $group->getId();

                // Находим все таблицы на листе
                $tables = $this->findTablesOnSheet($sheet);
                
                $this->logger->info("Sheet " . ($sheetIndex + 1) . " (typeCode: $typeCode): Found " . count($tables) . " tables");

                foreach ($tables as $tableIndex => $table) {
                    $this->logger->info("Processing table " . ($tableIndex + 1) . " from column {$table['startColumn']}, rows {$table['startRow']}-{$table['endRow']}");
                    
                    // Передаём ID группы вместо объекта
                    $this->importRingsFromTable($sheet, $table, $groupId, $stats);
                }
                
                // Освобождаем память после обработки листа
                unset($sheet);
                gc_collect_cycles();
                
            } catch (\Exception $e) {
                $stats['errors'][] = "Ошибка на странице " . ($sheetIndex + 1) . ": " . $e->getMessage();
                $this->logger->error("Sheet $sheetIndex error: " . $e->getMessage());
            }
        }
        
        // Финальный flush для всех оставшихся данных
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function findTablesOnSheet($sheet): array
    {
        $tables = [];
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $usedColumns = []; // Отслеживаем уже использованные колонки для избежания дублирования

        // Ищем все ячейки с "Part No."
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                
                // Пропускаем уже обработанные колонки
                if (isset($usedColumns[$row . '_' . $columnLetter])) {
                    continue;
                }
                
                $value = $this->getCellValue($sheet, $columnLetter, $row);

                if ($this->containsPartNo($value)) {
                    // Находим последнюю заполненную строку в этой колонке
                    $lastRow = $this->findLastFilledRow($sheet, $columnLetter, $row + 1, $highestRow);

                    if ($lastRow > $row) {
                        $tables[] = [
                            'startColumn' => $columnLetter,
                            'startRow' => $row,
                            'endRow' => $lastRow
                        ];
                        
                        // Отмечаем эту колонку как использованную
                        $usedColumns[$row . '_' . $columnLetter] = true;
                    }
                    
                    // НЕ делаем break - продолжаем искать другие таблицы в этой же строке
                }
            }
        }

        return $tables;
    }

    private function findLastFilledRow($sheet, string $column, int $startRow, int $maxRow): int
    {
        $lastFilledRow = $startRow - 1;

        for ($row = $startRow; $row <= $maxRow; $row++) {
            $value = $this->getCellValue($sheet, $column, $row);
            if (!empty($value)) {
                $lastFilledRow = $row;
            }
        }

        return $lastFilledRow;
    }

    private function importRingsFromTable($sheet, array $table, int $groupId, array &$stats): void
    {
        $startColumn = $table['startColumn'];
        $startRow = $table['startRow'];
        $endRow = $table['endRow'];

        $startColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startColumn);

        $batchSize = 25; // Уменьшили batch size для экономии памяти
        $processedCount = 0;

        // Получаем свежую копию группы
        $group = $this->entityManager->getRepository(RingGroup::class)->find($groupId);
        if (!$group) {
            return;
        }

        // Читаем заголовок таблицы и определяем количество колонок
        $columnNames = [];
        $maxColumns = 0;
        
        for ($i = 0; $i < 20; $i++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex + 1 + $i);
            $headerValue = $this->getCellValue($sheet, $columnLetter, $startRow);
            
            // Если встретили "Part No." - значит началась следующая таблица
            if ($this->containsPartNo($headerValue)) {
                break;
            }
            
            // Если ячейка не пустая
            if (!empty($headerValue)) {
                $cleanedHeader = $this->removeNonLatinCharacters($headerValue);
                if (!empty($cleanedHeader)) {
                    $columnNames[] = $cleanedHeader;
                }
                $maxColumns = $i + 1; // Запоминаем позицию последней заполненной колонки
            }
        }
        
        // Если не нашли ни одной колонки с данными, используем дефолт (8 колонок)
        if ($maxColumns === 0) {
            $maxColumns = 8;
        }
        
        // Если названия колонок были найдены и отличаются от дефолтных, обновляем группу
        if (!empty($columnNames) && $columnNames !== self::DEFAULT_COLUMN_NAMES) {
            $currentColumnNames = $group->getColumnNames();
            // Обновляем только если текущие названия - дефолтные или пустые
            if (empty($currentColumnNames) || $currentColumnNames === self::DEFAULT_COLUMN_NAMES) {
                $group->setColumnNames($columnNames);
                $this->entityManager->persist($group);
                $this->entityManager->flush();
            }
        }

        // Загружаем все существующие кольца группы в память для оптимизации
        $existingRings = [];
        $existingRingsData = $this->entityManager->getRepository(Ring::class)
            ->findBy(['ringGroup' => $group]);
        
        foreach ($existingRingsData as $ring) {
            $existingRings[$ring->getPartNumber()] = $ring;
        }

        // Импортируем строки
        for ($row = $startRow + 1; $row <= $endRow; $row++) {
            try {
                // Первая колонка - артикул
                $partNumber = $this->getCellValue($sheet, $startColumn, $row);

                // Пропускаем строки без артикула
                if (empty($partNumber)) {
                    continue;
                }

                // Читаем размеры из следующих колонок (строго в пределах maxColumns)
                $dimensions = [];
                $colIndex = $startColIndex + 1;

                // Читаем только определенное количество колонок
                for ($i = 0; $i < $maxColumns; $i++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $i);
                    $value = $this->getCellValue($sheet, $columnLetter, $row);

                    // Если ячейка пустая - добавляем null
                    if (empty($value)) {
                        $dimensions[] = null;
                        continue;
                    }

                    // Проверяем, является ли значение числом
                    if ($this->isNumericValue($value)) {
                        $dimensions[] = $this->normalizeNumber($value);
                    } else {
                        // Если это не число, сохраняем как строку (может быть подтип, артикул, и т.д.)
                        $dimensions[] = $value;
                    }
                }
                
                // Удаляем trailing null значения
                while (!empty($dimensions) && end($dimensions) === null) {
                    array_pop($dimensions);
                }

                // Пропускаем, если нет размеров
                if (empty($dimensions)) {
                    continue;
                }

                // Проверяем, существует ли кольцо в кэше
                if (isset($existingRings[$partNumber])) {
                    $ring = $existingRings[$partNumber];
                    $stats['rings_updated']++;
                } else {
                    $ring = new Ring();
                    $ring->setPartNumber($partNumber);
                    $ring->setRingGroup($group);
                    $existingRings[$partNumber] = $ring;
                    $stats['rings_created']++;
                }

                $ring->setDimensions($dimensions);

                $this->entityManager->persist($ring);
                
                $processedCount++;
                
                // Flush каждые N записей для оптимизации
                if ($processedCount % $batchSize === 0) {
                    $this->entityManager->flush();
                    
                    // После flush очищаем только кольца
                    $this->entityManager->clear(Ring::class);
                    
                    // Перезагружаем группу, так как она могла быть cleared
                    $group = $this->entityManager->getRepository(RingGroup::class)->find($groupId);
                    if (!$group) {
                        return;
                    }
                    
                    // Перезагружаем кольца в кэш
                    $existingRings = [];
                    $existingRingsData = $this->entityManager->getRepository(Ring::class)
                        ->findBy(['ringGroup' => $group]);
                    
                    foreach ($existingRingsData as $ring) {
                        $existingRings[$ring->getPartNumber()] = $ring;
                    }
                    
                    // Освобождаем память
                    gc_collect_cycles();
                }
            } catch (\Exception $e) {
                $stats['errors'][] = "Ошибка импорта кольца (строка $row): " . $e->getMessage();
                $this->logger->warning("Ring import error at row $row: " . $e->getMessage());
            }
        }
        
        // Финальный flush для оставшихся записей
        if ($processedCount % $batchSize !== 0) {
            $this->entityManager->flush();
        }
        
        $this->entityManager->clear(Ring::class);
        gc_collect_cycles();
    }

    private function getCellValue($sheet, string $column, int $row): ?string
    {
        $value = $sheet->getCell($column . $row)->getValue();
        if ($value === null) {
            return null;
        }
        
        // Преобразуем в строку и заменяем все переносы строк на пробелы
        $stringValue = (string)$value;
        $stringValue = str_replace(["\r\n", "\r", "\n"], ' ', $stringValue);
        
        return trim($stringValue);
    }

    private function containsPartNo(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }
        return stripos($value, 'Part No') !== false || stripos($value, 'Part_No') !== false;
    }

    private function removeNonLatinCharacters(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        // Оставляем только латиницу, цифры, пробелы, +, -, скобки
        $cleaned = preg_replace('/[^\x20-\x7E]+/u', '', $text);
        
        // Удаляем множественные символы + и пробелы
        $cleaned = preg_replace('/\+{2,}/', '+', $cleaned); // Убираем ++ на +
        $cleaned = preg_replace('/\s{2,}/', ' ', $cleaned); // Убираем множественные пробелы
        $cleaned = preg_replace('/\+\s+\+/', '+', $cleaned); // Убираем + + на +
        $cleaned = preg_replace('/^\+|\+$/', '', $cleaned); // Убираем + в начале и конце
        
        return trim($cleaned);
    }

    private function extractTypeCode(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        // Извлекаем текст до первой скобки
        $parts = explode('(', $text);
        $typeCode = trim($parts[0]);

        return !empty($typeCode) ? $typeCode : null;
    }

    private function isNumericValue(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Заменяем запятую на точку для проверки
        $normalized = str_replace(',', '.', $value);

        // Проверяем, является ли строка числом (целым или дробным)
        if (is_numeric($normalized)) {
            return true;
        }
        
        // Проверяем значения типа "14/18" или "17/21" (дроби или диапазоны)
        if (preg_match('/^\d+(\.\d+)?\/\d+(\.\d+)?$/', $normalized)) {
            return true;
        }
        
        // Проверяем значения типа "12*24*7/8" (умножение и дроби)
        if (preg_match('/^\d+(\.\d+)?[\*\/\+\-]\d+/', $normalized)) {
            return true;
        }

        return false;
    }

    private function normalizeNumber(?string $value)
    {
        if (empty($value)) {
            return null;
        }

        // Заменяем запятую на точку
        $normalized = str_replace(',', '.', $value);

        // Если это обычное число - возвращаем как float
        if (is_numeric($normalized)) {
            return (float)$normalized;
        }
        
        // Если это сложное значение (дробь, умножение и т.д.) - возвращаем как строку
        if (preg_match('/^[\d\.\*\/\+\-]+$/', $normalized)) {
            return $normalized;
        }

        return $normalized;
    }
}
