<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\GovernmentAgency;
use App\Models\User;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder{
    public function run():void
    {
        //citizens
        $citizens = User::factory()->count(10)->create(['role'=>'citizen',]);
        //agency
        $agincies = GovernmentAgency::factory()->count(5)->create();
        foreach($agincies as $agincie){
            $employees = User::factory()->count(2)->create(['role'=>'employee', 'agency_id'=>$agincie->id]);
             $complaints = Complaint::factory()->count(10)->create(['agency_id'=>$agincie->id,'user_id'=>$citizens->random()]);
        }
    }

}