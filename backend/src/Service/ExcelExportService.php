<?php

namespace App\Service;

use App\Entity\Ring;
use App\Entity\RingGroup;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Экспортирует кольца в Excel файл
     * 
     * @param array $filters Массив фильтров для выборки колец
     * @return string Путь к созданному файлу
     */
    public function exportRings(array $filters = []): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Устанавливаем заголовки
        $sheet->setCellValue('A1', 'Группа');
        $sheet->setCellValue('B1', 'Номер');
        $sheet->setCellValue('C1', 'Цена');
        $sheet->setCellValue('D1', 'Количество');
        
        // Стилизуем заголовок
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        
        // Устанавливаем ширину колонок
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        
        // Получаем все группы для маппинга
        $groups = $this->entityManager->getRepository(RingGroup::class)->findAll();
        $groupsMap = [];
        foreach ($groups as $group) {
            $groupsMap[$group->getId()] = $group->getTypeCode();
        }
        
        // Строим запрос с фильтрами
        $qb = $this->entityManager->getRepository(Ring::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.ringGroup', 'g')
            ->orderBy('g.typeCode', 'ASC')
            ->addOrderBy('r.partNumber', 'ASC');
        
        // Применяем фильтры
        if (!empty($filters['search'])) {
            $qb->andWhere('r.partNumber LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }
        
        if (!empty($filters['groupIds']) && is_array($filters['groupIds'])) {
            $qb->andWhere('r.ringGroup IN (:groupIds)')
               ->setParameter('groupIds', $filters['groupIds']);
        }
        
        if (!empty($filters['stockFilter'])) {
            if ($filters['stockFilter'] === 'in_stock') {
                $qb->andWhere('r.inStock > 0');
            } elseif ($filters['stockFilter'] === 'out_of_stock') {
                $qb->andWhere('r.inStock = 0');
            }
        }
        
        if (!empty($filters['priceFilter'])) {
            if ($filters['priceFilter'] === 'with_price') {
                $qb->andWhere('r.price IS NOT NULL')
                   ->andWhere('r.price > 0');
            } elseif ($filters['priceFilter'] === 'without_price') {
                $qb->andWhere('(r.price IS NULL OR r.price <= 0)');
            }
        }
        
        if (!empty($filters['photoFilter'])) {
            if ($filters['photoFilter'] === 'with_photo') {
                $qb->andWhere('JSON_LENGTH(r.photos) > 0');
            } elseif ($filters['photoFilter'] === 'without_photo') {
                $qb->andWhere('(JSON_LENGTH(r.photos) = 0 OR r.photos IS NULL)');
            }
        }
        
        $rings = $qb->getQuery()->getResult();
        
        // Заполняем данные
        $row = 2;
        foreach ($rings as $ring) {
            $groupTypeCode = $groupsMap[$ring->getRingGroup()?->getId()] ?? '';
            
            $sheet->setCellValue('A' . $row, $groupTypeCode);
            $sheet->setCellValue('B' . $row, $ring->getPartNumber());
            $sheet->setCellValue('C' . $row, $ring->getPrice() ?? '');
            $sheet->setCellValue('D' . $row, $ring->getInStock());
            
            $row++;
        }
        
        // Применяем границы ко всем ячейкам с данными
        if ($row > 2) {
            $sheet->getStyle('A1:D' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ],
                ],
            ]);
        }
        
        // Сохраняем файл
        $tempDir = sys_get_temp_dir();
        $filename = 'rings_export_' . time() . '.xlsx';
        $filepath = $tempDir . '/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        // Освобождаем память
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return $filepath;
    }
}
