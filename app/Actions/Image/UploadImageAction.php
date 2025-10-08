<?php

namespace App\Actions\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadImageAction
{
    public function execute(UploadedFile $file, string $directory = 'images'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs(
            "public/{$directory}",
            $filename
        );

        // Return the public path
        return str_replace('public/', '', $path);
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
