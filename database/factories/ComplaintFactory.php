<?php

namespace Database\Factories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $complaint = Complaint::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description'=>$this->faker->paragraph(),
            'location'=>$this->faker->address(),
            'status'=>'new',
            'reference_number'=>strtoupper(uniqid('CMP-')),
            'attachments'=>[],
        ];
    }
}
