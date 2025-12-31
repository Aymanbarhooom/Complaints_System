<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agency_id')->constrained('government_agencies')->cascadeOnDelete();
            $table->text('description');
            $table->string('location')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['new', 'in_progress', 'resolved', 'rejected'])->default('new');
            $table->string('reference_number')->unique();
            $table->foreignId('assigned_employee_id')->conxstrained('users')->nullable()->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('complaints');
    }
};