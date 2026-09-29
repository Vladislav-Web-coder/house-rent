<?php

namespace App\Listeners;

use App\Services\VideoOptimizer;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProcessVideoMedia
{
    public function __construct(
        protected VideoOptimizer $videoOptimizer
    ) {}

    public function handle(MediaHasBeenAdded $event): void
    {
        $media = $event->media;

        // Обрабатываем только видео из галереи
        if (!str_starts_with($media->mime_type, 'video/')) {
            return;
        }

        if ($media->collection_name !== 'gallery') {
            return;
        }

        $path = $media->getPath();

        if (!file_exists($path)) {
            Log::warning('Файл видео не найден (MediaHasBeenAdded)', [
                'media_id' => $media->id,
                'path' => $path,
            ]);
            return;
        }

        try {
            Log::info('Начало обработки видео', [
                'media_id' => $media->id,
                'path' => $path,
            ]);

            // 1. Оптимизация для стриминга (+faststart)
            $this->videoOptimizer->optimizeForStreaming($path);

            // 2. Извлекаем кадр для превью через ffmpeg
            $this->extractVideoThumbnail($media, $path);

            Log::info('Видео полностью обработано', [
                'media_id' => $media->id,
                'duration' => $this->videoOptimizer->getDuration($path),
            ]);
        } catch (\Throwable $e) {
            Log::error('Ошибка обработки видео', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Ручное извлечение кадра из видео
     * Не полагаемся на extractVideoFrameAtSecond от Spatie — делаем сами
     */
    private function extractVideoThumbnail(Media $media, string $videoPath): void
    {
        // Путь куда сохраним превью
        $conversionsDir = dirname($videoPath) . '/conversions';
        if (!is_dir($conversionsDir)) {
            mkdir($conversionsDir, 0755, true);
        }

        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);
        $thumbPath = $conversionsDir . '/' . $fileName . '-thumb_800x600.jpg';

        // Если превью уже есть — не делаем заново
        if (file_exists($thumbPath)) {
            Log::info('Превью уже существует, пропускаем', [
                'media_id' => $media->id,
                'thumb_path' => $thumbPath,
            ]);
            return;
        }

        // Извлекаем кадр на 1 секунде
        // -update 1 — перезаписывать, если файл существует
        $command = sprintf(
            'ffmpeg -y -ss 1 -i %s -vframes 1 -vf "scale=800:600:force_original_aspect_ratio=decrease,pad=800:600:(ow-iw)/2:(oh-ih)/2" -q:v 2 -update 1 %s 2>&1',
            escapeshellarg($videoPath),
            escapeshellarg($thumbPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($thumbPath)) {
            Log::error('Не удалось извлечь кадр из видео', [
                'media_id' => $media->id,
                'video_path' => $videoPath,
                'thumb_path' => $thumbPath,
                'return_code' => $returnCode,
                'output' => implode("\n", array_slice($output ?? [], -10)),
            ]);
            return;
        }

        Log::info('Превью видео успешно извлечено', [
            'media_id' => $media->id,
            'thumb_path' => $thumbPath,
            'size' => filesize($thumbPath),
        ]);

        // Помечаем конверсию как выполненную
        $media->markAsConversionGenerated('thumb_800x600');
        $media->save();
    }
}
