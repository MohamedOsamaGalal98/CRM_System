<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Label;

class CustomerLabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(5)->get();
        $labels = Label::take(4)->get();

        if ($customers->count() > 0 && $labels->count() > 0) {
            foreach ($customers as $customer) {
                // Attach 1-3 random labels to each customer
                $randomLabels = $labels->random(rand(1, 3));
                $customer->labels()->attach($randomLabels->pluck('id'));
            }
            
            echo "Created relationships between {$customers->count()} customers and labels\n";
        } else {
            echo "No customers or labels found to create relationships\n";
        }
    }
}
