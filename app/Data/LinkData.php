<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Data;

class LinkData extends Data
{
    public function __construct(
        #[Required, Exists('profiles', 'id')]
        public int $profile_id,

        #[Required, Max(255)]
        public string $title,

        #[Nullable]
        public ?string $description,

        #[Required, Url, Max(255)]
        public string $url,

        #[Nullable, Max(255)]
        public ?string $icon,

        #[Nullable, Min(0)]
        public ?int $order,

        public bool $is_active = true,
    ) {}
}
