<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OptimizeVideos extends Command
{
    protected $signature = 'video:optimize {--id= : Оптимизировать конкретный media ID}';
    protected $description = 'Применяет +faststart ко всем видео (moov в начало) для работы перемотки';

    public function handle(): int
    {
        $query = Media::where('mime_type', 'like', 'video/%');

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        $videos = $query->get();

        if ($videos->isEmpty()) {
            $this->info('Видео не найдены.');
            return self::SUCCESS;
        }

        foreach ($videos as $video) {
            $path = $video->getPath();

            if (!file_exists($path)) {
                $this->warn("Файл не найден: media #{$video->id}");
                continue;
            }

            if ($this->isFaststart($path)) {
                $this->info("media #{$video->id} — уже оптимизировано ✔");
                continue;
            }

            $this->optimize($path);
            $this->info("media #{$video->id} — оптимизировано ✅");
        }

        return self::SUCCESS;
    }

    /**
     * Проверяет порядок атомов: moov должен идти РАНЬШЕ mdat
     */
    protected function isFaststart(string $path): bool
    {
        $order = $this->atomOrder($path);
        $moov = array_search('moov', $order);
        $mdat = array_search('mdat', $order);

        return $moov !== false && $mdat !== false && $moov < $mdat;
    }

    /**
     * Читает порядок MP4-атомов без внешних утилит
     */
    protected function atomOrder(string $path): array
    {
        $order = [];
        $h = fopen($path, 'rb');
        if (!$h) return $order;

        $pos = 0;
        $size = filesize($path);

        while ($pos < $size && count($order) < 8) {
            fseek($h, $pos);
            $header = fread($h, 8);
            if (strlen($header) < 8) break;

            $len = unpack('N', substr($header, 0, 4))[1];
            $type = substr($header, 4, 4);
            $order[] = $type;

            if ($len === 1) {
                $len = unpack('J', fread($h, 8))[1]; // 64-bit размер
            } elseif ($len === 0) {
                break; // до конца файла
            }

            $pos += $len;
        }

        fclose($h);
        return $order;
    }

    /**
     * Применяет +faststart (без перекодирования, быстро)
     */
    protected function optimize(string $path): void
    {
        $temp = $path . '.faststart.tmp';

        exec(sprintf(
            'ffmpeg -y -i %s -c copy -movflags +faststart -f mp4 %s 2>&1',
            escapeshellarg($path),
            escapeshellarg($temp)
        ), $output, $code);

        if ($code === 0 && file_exists($temp)) {
            rename($temp, $path);
        } elseif (file_exists($temp)) {
            unlink($temp);
            $this->error("Ошибка ffmpeg для: {$path}");
        }
    }
}
