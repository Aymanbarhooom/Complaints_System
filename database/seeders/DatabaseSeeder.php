<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Complaint;
use App\Models\GovernmentAgency;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $citizens = User::factory()->count(10)->create(['role'=>'citizen',]);

        $agincies = GovernmentAgency::factory()->count(5)->create()->each(function ($agency) use ($citizens) {
            User::factory()->count(5)->create(['role'=>'employee','agency_id'=>$agency->id]);
            Complaint::factory()->count(10)->create([
                'agency_id'=>$agency->id,
                'user_id'=>$citizens->random()->id
            ]);
        });

        

        $this->call(AdminUserSeeder::class);
    }
}
