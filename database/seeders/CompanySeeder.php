<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::truncate();
        $companyFile = File::get(public_path('/dataset/companies.json'));
        $companies = json_decode($companyFile);

        foreach ($companies as $company) {
            $current = Company::create([
                "company_logo" => $company->company_logo,
                "company_name" => $company->company_name,
                "company_email" => $company->company_email ?? 'info@company.com',
                "industry" => $company->industry,
                "company_location" => $company->company_location ?? '',
                "company_url" => $company->company_url,
                "telephone_number" => $company->telephone_number,
                "established" => $company->established,
                "number_of_employees" => $company->number_of_employees,
                "capital" => $company->capital,
                "dispatch_business_license" => $company->dispatch_business_license,
                "area_of_expertise" => $company->area_of_expertise,
                "specialized_industries" => $company->specialized_industries,
                "introduction" => $company->introduction,
                "company_information_disclose" => $company->company_information_disclose,
                "updater_id" => $company->updater_id,
                "deleter_id" => $company->deleter_id,
                'user_id' => $company->user_id,
            ]);

            $current->addresses()->create([
                'street_address' => random_int(1, 20) . ' street ',
                'city' => random_int(1, 20) . ' city',
                'zip_code' => random_int(11111, 999999),
                'state' => Str::random(8),
                'country' => "Japan"
            ]);
        }
    }
}
