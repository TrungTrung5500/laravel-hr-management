<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollCalculationService
{
    /**
     * Calculate and create/update payroll for an employee for a specific month and year.
     */
    public function calculateForEmployee(Employee $employee, int $month, int $year, ?int $createdByUserId = null): Payroll
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Standard working days in month (weekdays Monday-Friday)
        $standardDays = 22;

        $baseSalary = (float) ($employee->base_salary ?: 10000000);
        $dailySalary = round($baseSalary / $standardDays, 2);
        $mealRate = (float) ($employee->meal_allowance_daily ?: 35000);

        // Fetch attendances in this month
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($item) => $item->work_date->format('Y-m-d'));

        // Fetch approved leave requests in this month
        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $halfWorkDays = 0;
        $fullLeaveDays = 0;
        $leaveDates = [];

        foreach ($leaves as $leave) {
            $cur = Carbon::parse($leave->start_date)->copy();
            $end = Carbon::parse($leave->end_date);

            while ($cur->lte($end)) {
                if ($cur->month === $month && $cur->year === $year) {
                    $dateStr = $cur->format('Y-m-d');
                    $leaveDates[$dateStr] = $leave;

                    if ($leave->isHalfDay()) {
                        $halfWorkDays++;
                    } else {
                        $fullLeaveDays++;
                    }
                }
                $cur->addDay();
            }
        }

        // Count full work days: attendance days that are not half-day leaves or full-day leaves
        $fullWorkDays = 0;
        if ($attendances->isNotEmpty()) {
            foreach ($attendances as $dateStr => $att) {
                if (isset($leaveDates[$dateStr])) {
                    // Handled by leave loop (if half day, already counted in $halfWorkDays)
                    continue;
                }
                if ($att->check_in_at) {
                    $fullWorkDays++;
                }
            }
        } else {
            // If attendances not populated yet for testing/demo, fallback to standard minus leaves
            $fullWorkDays = max(0, $standardDays - $fullLeaveDays - $halfWorkDays);
        }

        // Logic yêu cầu:
        // "Nếu nghỉ nửa buổi thì tính tiền công 1 buổi còn lại, bao gồm tiền ăn"
        // => 1 ngày nghỉ nửa buổi: được 0.5 công (buổi còn lại) và 1 suất tiền ăn cho buổi đó!
        $totalWorkDays = $fullWorkDays + ($halfWorkDays * 0.5);
        $mealDays = $fullWorkDays + $halfWorkDays; // Bao gồm cả ngày nghỉ nửa buổi vì làm buổi còn lại

        $salaryAmount = round($totalWorkDays * $dailySalary, 2);
        $mealAllowanceTotal = round($mealDays * $mealRate, 2);

        $existing = Payroll::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $bonusAmount = $existing ? $existing->bonus_amount : 0;
        $deductionAmount = $existing ? $existing->deduction_amount : 0;
        $netSalary = $salaryAmount + $mealAllowanceTotal + $bonusAmount - $deductionAmount;

        $payroll = Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'standard_days' => $standardDays,
                'base_salary' => $baseSalary,
                'daily_salary' => $dailySalary,
                'meal_allowance_rate' => $mealRate,
                'full_work_days' => $fullWorkDays,
                'half_work_days' => $halfWorkDays,
                'leave_days' => $fullLeaveDays,
                'total_work_days' => $totalWorkDays,
                'meal_days' => $mealDays,
                'salary_amount' => $salaryAmount,
                'meal_allowance_total' => $mealAllowanceTotal,
                'bonus_amount' => $bonusAmount,
                'deduction_amount' => $deductionAmount,
                'net_salary' => $netSalary,
                'status' => $existing ? $existing->status : 'draft',
                'created_by' => $createdByUserId ?: ($existing?->created_by),
            ]
        );

        return $payroll;
    }

    /**
     * Calculate payroll for all active employees.
     *
     * @return array{total: int, count: int}
     */
    public function calculateAll(int $month, int $year, ?int $createdByUserId = null): array
    {
        $employees = Employee::where('employment_status', 'active')->get();
        $count = 0;

        foreach ($employees as $employee) {
            $this->calculateForEmployee($employee, $month, $year, $createdByUserId);
            $count++;
        }

        return [
            'total' => $employees->count(),
            'count' => $count,
        ];
    }
}
