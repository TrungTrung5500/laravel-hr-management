<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'standard_days',
        'base_salary',
        'daily_salary',
        'meal_allowance_rate',
        'full_work_days',
        'half_work_days',
        'leave_days',
        'total_work_days',
        'meal_days',
        'salary_amount',
        'meal_allowance_total',
        'bonus_amount',
        'deduction_amount',
        'net_salary',
        'status',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'standard_days' => 'integer',
            'base_salary' => 'float',
            'daily_salary' => 'float',
            'meal_allowance_rate' => 'float',
            'full_work_days' => 'float',
            'half_work_days' => 'float',
            'leave_days' => 'float',
            'total_work_days' => 'float',
            'meal_days' => 'float',
            'salary_amount' => 'float',
            'meal_allowance_total' => 'float',
            'bonus_amount' => 'float',
            'deduction_amount' => 'float',
            'net_salary' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Bản nháp',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            default => $this->status,
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            'draft' => 'pending',
            'approved' => 'in_progress',
            'paid' => 'completed',
            default => 'neutral',
        };
    }

    public function formattedBaseSalary(): string
    {
        return number_format($this->base_salary, 0, ',', '.').' đ';
    }

    public function formattedDailySalary(): string
    {
        return number_format($this->daily_salary, 0, ',', '.').' đ';
    }

    public function formattedSalaryAmount(): string
    {
        return number_format($this->salary_amount, 0, ',', '.').' đ';
    }

    public function formattedMealAllowance(): string
    {
        return number_format($this->meal_allowance_total, 0, ',', '.').' đ';
    }

    public function formattedNetSalary(): string
    {
        return number_format($this->net_salary, 0, ',', '.').' đ';
    }
}
