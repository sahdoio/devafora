<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'name' => 'DevAfora',
            'bio' => 'Desenvolvedor Full Stack apaixonado por tecnologia e inovação. Compartilho conhecimento sobre Laravel, Vue.js, e as melhores práticas de desenvolvimento web. Junte-se à comunidade para aprender e crescer juntos!',
            'photo' => null,
            'is_active' => true,
        ];
    }
}
