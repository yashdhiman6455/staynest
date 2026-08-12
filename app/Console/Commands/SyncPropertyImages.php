<?php

namespace App\Console\Commands;

use App\Models\PropertyImageBlob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncPropertyImages extends Command
{
    protected $signature = 'images:sync';

    protected $description = 'Persist every image on the public disk into the database';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        $imported = 0;
        $skipped = 0;

        foreach ($disk->files('properties') as $path) {
            if (! preg_match('/\.(jpe?g|png|webp|gif)$/i', $path)) {
                $skipped++;

                continue;
            }

            $mime = mime_content_type($disk->path($path));

            PropertyImageBlob::updateOrCreate(
                ['path' => $path],
                [
                    'data' => $disk->get($path),
                    'mime' => $mime ?: 'image/jpeg',
                ]
            );

            $imported++;
        }

        $this->info("Imported {$imported} image(s) into the database ({$skipped} skipped).");

        return self::SUCCESS;
    }
}
