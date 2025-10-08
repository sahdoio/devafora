<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::factory()->create([
            'name' => 'DevAfora',
            'bio' => 'Engenheiro de Software Sênior com mais de uma década de experiência em desenvolvimento de software. Minha jornada começou em 2010 e, desde então, aprimorei minhas habilidades em desenvolvimento backend, tecnologias frontend, automação de servidores e design de arquitetura.',
            'photo' => 'https://ui-avatars.com/api/?name=DevAfora&size=256&background=3b82f6&color=ffffff&bold=true',
            'is_active' => true,
        ]);
    }
}
