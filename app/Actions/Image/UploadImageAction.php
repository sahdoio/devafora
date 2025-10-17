<?php

declare(strict_types=1);

namespace App\Actions\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadImageAction
{
    public function execute(UploadedFile $uploadedFile, string $directory = 'images'): string
    {
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();

        // Store the file using the 'public' disk
        $path = $uploadedFile->storeAs(
            $directory,
            $filename,
            'public'
        );

        return $path;
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
