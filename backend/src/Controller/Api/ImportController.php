<?php

namespace App\Controller\Api;

use App\Service\ExcelImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/import', name: 'api_import_')]
#[IsGranted('ROLE_ADMIN')]
class ImportController extends AbstractController
{
    public function __construct(
        private ExcelImportService $importService
    ) {
    }

    #[Route('/rings', name: 'rings', methods: ['POST'])]
    public function importRings(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json([
                'success' => false,
                'message' => 'Файл не загружен'
            ], 400);
        }

        // Проверяем расширение файла
        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getClientOriginalExtension();

        if (!in_array(strtolower($extension), $allowedExtensions)) {
            return $this->json([
                'success' => false,
                'message' => 'Неверный формат файла. Разрешены только .xlsx и .xls'
            ], 400);
        }

        // Проверяем размер файла (максимум 100MB)
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($file->getSize() > $maxSize) {
            return $this->json([
                'success' => false,
                'message' => 'Файл слишком большой. Максимальный размер: 100MB'
            ], 400);
        }

        try {
            // Сохраняем файл во временную директорию
            $uploadDir = $this->getParameter('kernel.project_dir') . '/var/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = uniqid('import_') . '.' . $extension;
            $file->move($uploadDir, $filename);
            $filePath = $uploadDir . '/' . $filename;

            // Выполняем импорт
            $stats = $this->importService->importFromFile($filePath);

            // Удаляем временный файл
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return $this->json([
                'success' => true,
                'message' => 'Импорт завершен',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            // Удаляем временный файл в случае ошибки
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }

            return $this->json([
                'success' => false,
                'message' => 'Ошибка при импорте: ' . $e->getMessage()
            ], 500);
        }
    }
}
