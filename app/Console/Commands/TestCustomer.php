<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;

class TestCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Customer model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $customer = Customer::create([
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'test@example.com',
                'phone' => '123456789',
            ]);
            
            $this->info('Customer created successfully: ' . $customer->id);
            $this->info('Customer count: ' . Customer::count());
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
