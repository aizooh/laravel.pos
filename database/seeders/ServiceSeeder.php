<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Printing (B&W per page)',  'price' => 10],
            ['name' => 'Printing (Colour per page)', 'price' => 30],
            ['name' => 'Photocopy (B&W per page)',  'price' => 5],
            ['name' => 'Scanning (per page)',       'price' => 20],
            ['name' => 'Typing (per page)',         'price' => 30],
            ['name' => 'Lamination',                'price' => 50],
            ['name' => 'Binding',                   'price' => 100],
            ['name' => 'KRA Services',              'price' => 100],
            ['name' => 'eCitizen Services',         'price' => 100],
            ['name' => 'Passport Application',      'price' => 200],
            ['name' => 'CV Printing',               'price' => 100],
            ['name' => 'Internet / Computer Use (per hour)', 'price' => 100],
            ['name' => 'Online Application',        'price' => 150],
        ];

        foreach ($services as $s) {
            Service::updateOrCreate(['name' => $s['name']], $s + ['active' => true]);
        }
    }
}