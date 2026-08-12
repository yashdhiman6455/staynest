<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

/**
 * Database-backed copy of a property image.
 *
 * The public disk remains the fast path, but every uploaded image is also
 * persisted here so it survives container redeployments (Render wipes the
 * local filesystem) and is visible from any environment sharing the database.
 */
class PropertyImageBlob extends Model
{
    protected $table = 'property_image_blobs';

    protected $primaryKey = 'path';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'path',
        'data',
        'mime',
    ];

    /**
     * Persist an uploaded file's contents for the given storage path.
     */
    public static function saveFor(UploadedFile $file, string $path): void
    {
        static::updateOrCreate(
            ['path' => $path],
            [
                'data' => file_get_contents($file->getRealPath()),
                'mime' => $file->getMimeType() ?: 'image/jpeg',
            ]
        );
    }

    /**
     * Delete the persisted copy (if any) for the given storage path.
     */
    public static function deleteFor(?string $path): void
    {
        if ($path) {
            static::where('path', $path)->delete();
        }
    }
}
