<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Stores base64 images outside the public folder (storage/app/private/uploads).
 * Files are served only to authenticated users through the "uploads.show" route,
 * keeping the same "/uploads/{folder}/{file}" URLs already stored in the database.
 */
class ImageUploads
{
    /** Folders that may hold uploaded images. */
    public const FOLDERS = ['students', 'instructors', 'licenses'];

    /** Validation rule for a base64 image field (~8 MB decoded). */
    public const RULE = 'nullable|string|max:11500000';

    private const MAX_BYTES = 8 * 1024 * 1024;

    private const MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public static function root(): string
    {
        return storage_path('app/private/uploads');
    }

    /**
     * True only for well-formed URLs of an uploaded file,
     * e.g. /uploads/students/student_profile_abc123.jpeg (no "..", no subfolders).
     */
    public static function isValidUrl(?string $url): bool
    {
        if (!is_string($url)) {
            return false;
        }

        $folders = implode('|', self::FOLDERS);

        return (bool) preg_match('#^/uploads/(' . $folders . ')/[A-Za-z0-9_\-]+\.(jpe?g|png|gif|webp)$#', $url);
    }

    /** Absolute path on disk for a valid upload URL. */
    public static function path(string $url): string
    {
        return self::root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, substr($url, strlen('/uploads/')));
    }

    /**
     * Save a "data:image/...;base64," string and return its URL.
     * An existing "/uploads/..." URL is kept only if it is valid and the file exists.
     */
    public static function store(?string $value, string $folder, string $prefix): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, '/uploads/')) {
            return self::isValidUrl($value) && is_file(self::path($value)) ? $value : null;
        }

        if (!in_array($folder, self::FOLDERS, true)) {
            throw new InvalidArgumentException('Carpeta de imágenes no permitida.');
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $value, $match)) {
            return null;
        }

        $ext = strtolower($match[1]);
        if (!in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'], true)) {
            throw new InvalidArgumentException('Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.');
        }

        $data = base64_decode(substr($value, strpos($value, ',') + 1), true);
        if ($data === false || $data === '') {
            throw new InvalidArgumentException('No se pudo leer la imagen.');
        }

        if (strlen($data) > self::MAX_BYTES) {
            throw new InvalidArgumentException('La imagen supera el tamaño máximo permitido (8 MB).');
        }

        // Make sure the content really is an image of an allowed type
        $info = @getimagesizefromstring($data);
        if ($info === false || !in_array($info['mime'], self::MIMES, true)) {
            throw new InvalidArgumentException('El archivo enviado no es una imagen válida.');
        }

        $dir = self::root() . DIRECTORY_SEPARATOR . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileName = $prefix . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
        file_put_contents($dir . DIRECTORY_SEPARATOR . $fileName, $data);

        return '/uploads/' . $folder . '/' . $fileName;
    }

    /** Delete an uploaded file; ignores anything that is not a valid upload URL. */
    public static function delete(?string $url): void
    {
        if (!self::isValidUrl($url)) {
            return;
        }

        $path = self::path($url);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
