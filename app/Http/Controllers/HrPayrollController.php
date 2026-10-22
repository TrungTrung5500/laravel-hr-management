<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrPayrollController extends Controller
{
    public function __construct(
        protected PayrollCalculationService $payrollService
    ) {}

    public function index(Request $request): View
    {
        $selectedMonth = $request->integer('month', now()->month);
        $selectedYear = $request->integer('year', now()->year);

        $query = Payroll::with(['employee.department', 'employee.position', 'creator'])
            ->where('month', $selectedMonth)
            ->where('year', $selectedYear);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $request->integer('department_id')));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('employee', fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('employee_code', 'like', "%{$search}%"));
        }

        $payrolls = $query->latest()->get();

        $totalNetSalary = $payrolls->sum('net_salary');
        $totalWorkDays = $payrolls->sum('total_work_days');
        $totalMealAllowance = $payrolls->sum('meal_allowance_total');
        $activeEmployeeCount = Employee::where('employment_status', 'active')->count();

        return view('hr.payroll.index', [
            'role' => 'hr',
            'active' => 'payroll',
            'title' => 'Quản lý bảng lương',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => "Bảng lương & Chế độ phúc lợi Tháng {$selectedMonth}/{$selectedYear}",
            'payrolls' => $payrolls,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'totalNetSalary' => $totalNetSalary,
            'totalWorkDays' => $totalWorkDays,
            'totalMealAllowance' => $totalMealAllowance,
            'calculatedCount' => $payrolls->count(),
            'activeEmployeeCount' => $activeEmployeeCount,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        if ($request->filled('employee_id')) {
            $employee = Employee::findOrFail($request->integer('employee_id'));
            $this->payrollService->calculateForEmployee($employee, $month, $year, $request->user()->id);
            return redirect()->route('hr.payroll.index', ['month' => $month, 'year' => $year])
                ->with('success', "Đã tính lương tháng {$month}/{$year} cho nhân viên {$employee->full_name}.");
        }

        $result = $this->payrollService->calculateAll($month, $year, $request->user()->id);

        return redirect()->route('hr.payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "Đã tính lương tự động cho {$result['count']} nhân viên trong tháng {$month}/{$year}.");
    }

    public function show(Payroll $payroll): View
    {
        $payroll->load(['employee.department', 'employee.position', 'creator']);

        return view('hr.payroll.show', [
            'role' => 'hr',
            'active' => 'payroll',
            'title' => "Chi tiết phiếu lương #PL-{$payroll->id}",
            'topTitle' => 'Cổng nhân sự',
            'topSub' => "Chi tiết lương Tháng {$payroll->month}/{$payroll->year}",
            'payroll' => $payroll,
        ]);
    }

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'bonus_amount' => ['nullable', 'numeric', 'min:0'],
            'deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,approved,paid'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $bonus = (float) ($data['bonus_amount'] ?? 0);
        $deduction = (float) ($data['deduction_amount'] ?? 0);
        $netSalary = $payroll->salary_amount + $payroll->meal_allowance_total + $bonus - $deduction;

        $payroll->update([
            'bonus_amount' => $bonus,
            'deduction_amount' => $deduction,
            'net_salary' => $netSalary,
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật phiếu lương thành công.');
    }

    public function destroy(Payroll $payroll): RedirectResponse
    {
        $month = $payroll->month;
        $year = $payroll->year;
        $payroll->delete();

        return redirect()->route('hr.payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', 'Đã xóa bản ghi bảng lương.');
    }
}
