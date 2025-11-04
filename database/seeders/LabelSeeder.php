<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Label;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            ['name' => 'VIP Customer', 'color' => '#FFD700'],
            ['name' => 'High Priority', 'color' => '#FF6B6B'],
            ['name' => 'Corporate', 'color' => '#4ECDC4'],
            ['name' => 'New Customer', 'color' => '#45B7D1'],
            ['name' => 'Returning Customer', 'color' => '#96CEB4'],
            ['name' => 'Premium', 'color' => '#FFEAA7'],
            ['name' => 'Potential', 'color' => '#DDA0DD'],
            ['name' => 'Follow Up', 'color' => '#FFA07A'],
        ];

        foreach ($labels as $label) {
            Label::updateOrCreate(
                ['name' => $label['name']],
                $label
            );
        }
    }
}
