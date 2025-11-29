<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primeiro, criar os planos
        $this->call(PlanSeeder::class);

        // Buscar plano free para o usuário de teste
        $freePlan = Plan::where('slug', 'free')->first();

        // Criar usuário admin de teste
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'plan_id' => $freePlan?->id,
        ]);

        // Criar usuário comum de teste
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'plan_id' => $freePlan?->id,
        ]);
    }
}
