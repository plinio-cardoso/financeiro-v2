<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Alimentação', 'color' => '#FF6B6B'],
            ['name' => 'Transporte', 'color' => '#4ECDC4'],
            ['name' => 'Moradia', 'color' => '#45B7D1'],
            ['name' => 'Saúde', 'color' => '#96CEB4'],
            ['name' => 'Educação', 'color' => '#FFEAA7'],
            ['name' => 'Lazer', 'color' => '#3CB5EAc'],
            ['name' => 'Salário', 'color' => '#55E6C1'],
            ['name' => 'Freelance', 'color' => '#A29BFE'],
            ['name' => 'Investimento', 'color' => '#FD79A8'],
            ['name' => 'Outros', 'color' => '#B2BEC3'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
