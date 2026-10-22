<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('base_salary', 14, 2)->default(10000000)->after('hire_date');
            $table->decimal('meal_allowance_daily', 10, 2)->default(35000)->after('base_salary');
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('standard_days')->default(22);
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('daily_salary', 14, 2)->default(0);
            $table->decimal('meal_allowance_rate', 10, 2)->default(35000);
            $table->decimal('full_work_days', 4, 1)->default(0);
            $table->decimal('half_work_days', 4, 1)->default(0);
            $table->decimal('leave_days', 4, 1)->default(0);
            $table->decimal('total_work_days', 4, 1)->default(0);
            $table->decimal('meal_days', 4, 1)->default(0);
            $table->decimal('salary_amount', 14, 2)->default(0);
            $table->decimal('meal_allowance_total', 14, 2)->default(0);
            $table->decimal('bonus_amount', 14, 2)->default(0);
            $table->decimal('deduction_amount', 14, 2)->default(0);
            $table->decimal('net_salary', 14, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft, approved, paid
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['month', 'year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'meal_allowance_daily']);
        });
    }
};
