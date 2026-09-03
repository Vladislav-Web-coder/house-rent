<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractFileProvider
{
    // Путь к файлу договора относительно корня диска 'local'
    private const CONTRACT_PATH = 'contracts/rental-agreement.pdf';

    // Имя файла, которое увидит клиент во вложении
    private const CONTRACT_DISPLAY_NAME = 'Договор-аренды-УЮТНЫЙДОМ.pdf';

    /**
     * Использует Storage::path(), который корректно работает с любым диском
     */
    public function getPath(): ?string
    {
        if (!$this->exists()) {
            Log::warning('Файл договора не найден', [
                'expected_path' => $this->getExpectedPath(),
            ]);
            return null;
        }

        // Storage::path() возвращает реальный путь с учётом корня диска
        return Storage::disk('local')->path(self::CONTRACT_PATH);
    }

    /**
     * Возвращает путь для отображения в логах/админке
     */
    public function getExpectedPath(): string
    {
        return Storage::disk('local')->path(self::CONTRACT_PATH);
    }

    /**
     * Возвращает имя файла для отображения во вложении
     */
    public function getDisplayName(): string
    {
        return self::CONTRACT_DISPLAY_NAME;
    }

    /**
     * Проверяет, существует ли файл договора
     */
    public function exists(): bool
    {
        return Storage::disk('local')->exists(self::CONTRACT_PATH);
    }

    /**
     * Возвращает размер файла в байтах
     */
    public function getSize(): int
    {
        if (!$this->exists()) {
            return 0;
        }

        return Storage::disk('local')->size(self::CONTRACT_PATH);
    }
}
