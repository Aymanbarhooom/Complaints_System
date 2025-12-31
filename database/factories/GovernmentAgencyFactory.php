<?php

namespace Database\Factories;

use App\Models\GovernmentAgency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GovernmentAgency>
 */
class GovernmentAgencyFactory extends Factory
{
   protected $agency = GovernmentAgency::class;
    public function definition(): array
    {
        return [
            'name'=>$this->faker->company().'agency',
            'description'=>$this->faker->sentence(),
        ];
    }
}
