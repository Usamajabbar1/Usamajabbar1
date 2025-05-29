<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyProfile;

class CompanyProfileSeeder extends Seeder
{
    public function run()
    {
        CompanyProfile::create([
            'name' => 'Merkevo Solutions',
            'address' => 'Plot 123, G-10 Markaz, Islamabad',
            'phone' => '051-1234567',
            'email' => 'support@merkevotech.com',
        ]);
    }
}
