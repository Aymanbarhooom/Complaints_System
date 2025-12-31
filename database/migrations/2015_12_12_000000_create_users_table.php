<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

             $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
             $table->string('cardId')->unique()->nullable();
            $table->date('birthday')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['citizen', 'employee', 'admin'])->default('citizen');
            $table->foreignId('agency_id')
                  ->nullable()
                  ->constrained('government_agencies')
                  ->nullOnDelete();

            $table->timestamps();
            /** $table->string('employee_code')->unique(); 
            $table->foreignId('department_id')
            ->constrained('departments')   
            ->onDelete('restrict');
            $table->boolean('is_department_manager')
            ->default(false);
            $table->timestamps(); */
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
 
