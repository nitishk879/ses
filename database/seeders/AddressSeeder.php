<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses = array();

        foreach ($addresses as $address){
            Address::create([
                'street_address'    => $address->street_address,
                'city'  => $address->city,
                'state' => $address->state,
                'zip_code'  => $address->zip_code,
                'country'   => $address->country,
                'company_id'    => $address->company_id,
            ]);
        }
    }
}
