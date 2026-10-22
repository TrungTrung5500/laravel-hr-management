<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('license_plate', 30);
            $table->string('vehicle_type', 30)->default('motorbike'); // motorbike, car, electric_bike, bicycle, other
            $table->string('brand_model', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('card_number', 50)->nullable()->unique();
            $table->string('status', 20)->default('pending'); // pending, active, suspended, revoked
            $table->text('note')->nullable();
            $table->date('registered_date')->default(now()->toDateString());
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index('license_plate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_registrations');
    }
};
