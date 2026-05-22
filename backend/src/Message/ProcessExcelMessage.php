<?php

namespace App\Message;

class ProcessExcelMessage
{
    public function __construct(
        private string $filePath,
        private int $adminId
    ) {}

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getAdminId(): int
    {
        return $this->adminId;
    }
}
