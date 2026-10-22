<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->get();

        $approvedDays = $leaves->where('status', 'approved')->sum('days_count');

        return view('employee.leaves.index', [
            'role' => 'employee',
            'active' => 'leaves',
            'title' => 'Đơn nghỉ phép',
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => 'Quản lý nghỉ phép',
            'employee' => $employee,
            'leaves' => $leaves,
            'totalRequests' => $leaves->count(),
            'pendingRequests' => $leaves->where('status', 'pending')->count(),
            'approvedRequests' => $leaves->where('status', 'approved')->count(),
            'approvedDays' => $approvedDays,
        ]);
    }

    public function create(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        return view('employee.leaves.form', [
            'role' => 'employee',
            'active' => 'leaves',
            'title' => 'Tạo đơn xin nghỉ phép',
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => 'Nộp đơn nghỉ phép',
            'employee' => $employee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        $data = $request->validate([
            'leave_type' => ['required', 'in:full_day,half_day_morning,half_day_afternoon'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $leaveType = $data['leave_type'];
        $startDate = Carbon::parse($data['start_date']);
        $endDate = !empty($data['end_date']) && $leaveType === 'full_day'
            ? Carbon::parse($data['end_date'])
            : $startDate->copy();

        if (in_array($leaveType, ['half_day_morning', 'half_day_afternoon'], true)) {
            $daysCount = 0.5;
            $endDate = $startDate->copy();
        } else {
            $daysCount = (float) ($startDate->diffInDays($endDate) + 1);
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $leaveType,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'days_count' => $daysCount,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('employee.leaves.index')->with('success', 'Đã nộp đơn nghỉ phép thành công. Đang chờ HR phê duyệt.');
    }

    public function destroy(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $leave->employee_id === $employee->id, 403);
        abort_unless($leave->status === 'pending', 400, 'Chỉ có thể hủy đơn nghỉ phép đang chờ duyệt.');

        $leave->delete();

        return redirect()->route('employee.leaves.index')->with('success', 'Đã hủy đơn xin nghỉ phép.');
    }
}
