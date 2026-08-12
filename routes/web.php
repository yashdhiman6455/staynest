<?php

use App\Models\PropertyImageBlob;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Storage / image serving
|--------------------------------------------------------------------------
|
| Property images are stored on the "public" disk (storage/app/public) and
| normally served directly by the web server through the public/storage
| symlink. On Render the filesystem is ephemeral, so uploads are also
| persisted in the database (property_image_blobs). When the file is missing
| from disk - for example after a redeploy, or from another environment that
| shares the database - this route streams the stored bytes instead of
| falling through to the SPA catch-all (which used to return HTML for a
| missing image).
|
*/

Route::get('/storage/properties/{file}', function (string $file) {
    $path = 'properties/'.$file;

    if (Storage::disk('public')->exists($path)) {
        return response()->file(Storage::disk('public')->path($path));
    }

    $blob = PropertyImageBlob::find($path);

    if (! $blob) {
        abort(404);
    }

    return response($blob->data, 200, [
        'Content-Type' => $blob->mime ?? 'image/jpeg',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('file', '[A-Za-z0-9._-]+');

/*
|--------------------------------------------------------------------------
| SPA catch-all
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
