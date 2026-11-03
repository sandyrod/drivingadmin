<?php

namespace App\Http\Controllers;

use App\Support\ImageUploads;

class UploadController extends Controller
{
    /**
     * Serve an uploaded image to authenticated users only.
     */
    public function show(string $folder, string $file)
    {
        $url = '/uploads/' . $folder . '/' . $file;

        abort_unless(ImageUploads::isValidUrl($url), 404);

        $path = ImageUploads::path($url);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
