<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'New Lead', 'position' => 1, 'is_default' => true],
            ['name' => 'Contacted', 'position' => 2, 'is_default' => false],
            ['name' => 'Qualified', 'position' => 3, 'is_default' => false],
            ['name' => 'Proposal Sent', 'position' => 4, 'is_default' => false],
            ['name' => 'Negotiation', 'position' => 5, 'is_default' => false],
            ['name' => 'Closed Won', 'position' => 6, 'is_default' => false],
            ['name' => 'Closed Lost', 'position' => 7, 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
