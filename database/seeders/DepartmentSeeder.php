<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'Department of Information Technology',
            ],
            [
                'name' => 'Electrical and Telecommunication Technology',
                'code' => 'ETT',
                'description' => 'Department of Electrical and Telecommunication Technology',
            ],
            [
                'name' => 'Renewable Energy',
                'code' => 'RE',
                'description' => 'Department of Renewable Energy',
            ],
            [
                'name' => 'Mechanical Engineering',
                'code' => 'MECH',
                'description' => 'Department of Mechanical Engineering',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
