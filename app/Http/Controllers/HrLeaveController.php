<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $query = LeaveRequest::with(['employee.department', 'employee.position', 'approver']);

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

        if ($request->filled('month')) {
            $month = $request->string('month')->toString(); // 'YYYY-MM'
            $query->where(function ($q) use ($month) {
                $q->where('start_date', 'like', "{$month}%")
                    ->orWhere('end_date', 'like', "{$month}%");
            });
        }

        $allLeaves = LeaveRequest::all();
        $leaves = $query->latest()->paginate(15)->withQueryString();

        return view('hr.leaves.index', [
            'role' => 'hr',
            'active' => 'leaves',
            'title' => 'Quản lý nghỉ phép',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Quản lý đơn nghỉ phép & chế độ',
            'leaves' => $leaves,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'totalCount' => $allLeaves->count(),
            'pendingCount' => $allLeaves->where('status', 'pending')->count(),
            'approvedCount' => $allLeaves->where('status', 'approved')->count(),
            'rejectedCount' => $allLeaves->where('status', 'rejected')->count(),
        ]);
    }

    public function show(LeaveRequest $leave): View
    {
        $leave->load(['employee.department', 'employee.position', 'approver']);

        return view('hr.leaves.show', [
            'role' => 'hr',
            'active' => 'leaves',
            'title' => 'Chi tiết đơn nghỉ phép',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Xử lý đơn nghỉ phép',
            'leave' => $leave,
        ]);
    }

    public function update(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'hr_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $leave->update([
            'status' => $data['status'],
            'hr_note' => $data['hr_note'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $statusText = $data['status'] === 'approved' ? 'phê duyệt' : 'từ chối';

        return redirect()->back()->with('success', "Đã {$statusText} đơn nghỉ phép của nhân viên thành công.");
    }
}
