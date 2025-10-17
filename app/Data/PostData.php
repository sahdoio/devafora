<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class PostData extends Data
{
    public function __construct(
        #[Required, Exists('profiles', 'id')]
        public int $profile_id,

        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $excerpt,

        #[Nullable]
        public ?string $content,

        #[Nullable, Max(255)]
        public ?string $author,

        #[Nullable, Mimes('jpeg', 'png', 'jpg', 'gif', 'webp'), Max(2048)]
        public UploadedFile|string|null $image,

        #[Nullable]
        public ?int $read_time,

        #[Nullable, ArrayType]
        public ?array $tags,

        public bool $is_published = false,

        public string|Optional|null $published_at = new Optional(),
    ) {}
}
