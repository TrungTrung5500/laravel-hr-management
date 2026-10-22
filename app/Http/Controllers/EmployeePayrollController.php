<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePayrollController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $latestPayroll = $payrolls->first();
        $totalEarnedYear = $payrolls->where('year', now()->year)->sum('net_salary');

        return view('employee.payroll.index', [
            'role' => 'employee',
            'active' => 'payroll',
            'title' => 'Bảng lương cá nhân',
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => 'Tra cứu phiếu lương & thu nhập',
            'employee' => $employee,
            'payrolls' => $payrolls,
            'latestPayroll' => $latestPayroll,
            'totalEarnedYear' => $totalEarnedYear,
        ]);
    }

    public function show(Request $request, Payroll $payroll): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $payroll->employee_id === $employee->id, 403);

        $payroll->load(['employee.department', 'employee.position']);

        return view('employee.payroll.show', [
            'role' => 'employee',
            'active' => 'payroll',
            'title' => "Phiếu lương Tháng {$payroll->month}/{$payroll->year}",
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => "Chi tiết phiếu lương Tháng {$payroll->month}/{$payroll->year}",
            'employee' => $employee,
            'payroll' => $payroll,
        ]);
    }
}
