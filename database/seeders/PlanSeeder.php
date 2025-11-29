<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Plano gratuito com recursos básicos para começar.',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_presentations' => 3,
                    'max_slides_per_presentation' => 10,
                    'basic_templates' => true,
                    'export_pdf' => false,
                    'collaboration' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                ],
                'max_slides' => 30,
                'max_presentations' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Plano premium com recursos avançados para profissionais.',
                'price' => 29.90,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_presentations' => 50,
                    'max_slides_per_presentation' => 100,
                    'basic_templates' => true,
                    'premium_templates' => true,
                    'export_pdf' => true,
                    'export_pptx' => true,
                    'collaboration' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'analytics' => true,
                ],
                'max_slides' => 5000,
                'max_presentations' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Plano empresarial com recursos ilimitados e suporte dedicado.',
                'price' => 99.90,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_presentations' => null, // ilimitado
                    'max_slides_per_presentation' => null, // ilimitado
                    'basic_templates' => true,
                    'premium_templates' => true,
                    'export_pdf' => true,
                    'export_pptx' => true,
                    'collaboration' => true,
                    'team_management' => true,
                    'custom_branding' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'dedicated_support' => true,
                    'analytics' => true,
                    'api_access' => true,
                    'sso' => true,
                ],
                'max_slides' => null, // ilimitado
                'max_presentations' => null, // ilimitado
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

