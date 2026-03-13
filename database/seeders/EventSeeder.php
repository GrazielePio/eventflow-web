<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'titulo' => 'Workshop de Tecnologia',
                'data' => '2026-03-10',
                'local' => 'Auditório Central',
                'descricao' => 'Evento sobre desenvolvimento web moderno, incluindo Vue 3, Laravel e práticas de código limpo.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'titulo' => 'Conferência de Inovação',
                'data' => '2026-04-15',
                'local' => 'Centro de Convenções',
                'descricao' => 'Palestras e workshops sobre as últimas tendências em tecnologia e inovação empresarial.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'titulo' => 'Meetup de Desenvolvedores',
                'data' => '2026-05-20',
                'local' => 'Coworking TechHub',
                'descricao' => 'Encontro mensal da comunidade de desenvolvedores para networking e troca de experiências.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'titulo' => 'Hackathon Regional',
                'data' => '2026-06-01',
                'local' => 'Universidade Federal',
                'descricao' => 'Competição de programação de 48 horas com premiações para as melhores soluções.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'titulo' => 'Seminário de Segurança',
                'data' => '2026-07-10',
                'local' => 'Hotel Premium',
                'descricao' => 'Evento focado em boas práticas de segurança, proteção de dados e conformidade com LGPD.',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('events')->insert($events);
    }
}
