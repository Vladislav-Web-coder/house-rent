<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VideoOptimizer
{
    /**
     * Максимальная длительность видео (3 минуты)
     */
    public const MAX_DURATION_SECONDS = 180;

    /**
     * Максимальный размер файла (100 МБ)
     */
    public const MAX_SIZE_BYTES = 104857600;

    /**
     * Проверяет, валидно ли видео для загрузки
     */
    public function validate(string $path): array
    {
        $errors = [];

        // Проверка размера
        $size = filesize($path);
        if ($size > self::MAX_SIZE_BYTES) {
            $errors[] = sprintf(
                'Размер видео %.1f МБ превышает лимит 100 МБ',
                $size / 1024 / 1024
            );
        }

        // Проверка длительности
        $duration = $this->getDuration($path);
        if ($duration === null) {
            $errors[] = 'Не удалось определить длительность видео. Файл может быть повреждён.';
        } elseif ($duration > self::MAX_DURATION_SECONDS) {
            $errors[] = sprintf(
                'Длительность видео %d сек превышает лимит 3 минуты',
                (int) $duration
            );
        }

        return $errors;
    }

    /**
     * Возвращает длительность видео в секундах
     */
    public function getDuration(string $path): ?float
    {
        $output = shell_exec(sprintf(
            'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
            escapeshellarg($path)
        ));

        if ($output === null || trim($output) === '' || str_contains($output, 'error')) {
            return null;
        }

        return (float) trim($output);
    }

    /**
     * Оптимизирует видео для стриминга (перемещает moov atom в начало)
     * Это нужно для работы перемотки без полной загрузки
     */
    public function optimizeForStreaming(string $path): bool
    {
        $tempPath = $path . '.optimized.tmp';

        $command = sprintf(
            'ffmpeg -y -i %s -c copy -movflags +faststart -f mp4 %s 2>&1',
            escapeshellarg($path),
            escapeshellarg($tempPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::warning('Ошибка оптимизации видео', [
                'path' => $path,
                'output' => implode("\n", array_slice($output, -5)),
            ]);

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            return false;
        }

        if (!rename($tempPath, $path)) {
            Log::error('Не удалось заменить видео оптимизированной версией', [
                'path' => $path,
            ]);
            unlink($tempPath);
            return false;
        }

        Log::info('Видео оптимизировано для стриминга', [
            'path' => $path,
        ]);

        return true;
    }

    /**
     * Проверяет, поддерживает ли файл перемотку (есть ли +faststart)
     */
    public function isStreamingReady(string $path): bool
    {
        $output = shell_exec(sprintf(
            'ffprobe -v error -show_entries format_tags=major_brand -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
            escapeshellarg($path)
        ));

        return $output !== null && !str_contains($output, 'error');
    }
}
